<?php
session_start();

// --- 1. CARGA SEGURA DE LA API KEY ---
if (file_exists('config_api.php')) {
    include('config_api.php');
}

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', '');
}

// --- INICIO LOGICA DE IA ---
$sugerencias = ""; 

function obtenerSugerencias($hobbiesArray, $presupuesto, $apiKey) {
    $textoHobbies = !empty($hobbiesArray) ? implode(", ", $hobbiesArray) : "gustos variados";

    $prompt = "Eres un experto en regalos. " .
              "Persona: le gusta " . $textoHobbies . ". " .
              "Presupuesto maximo: " . $presupuesto . " euros. " .
              "Genera 3 ideas de regalos originales. " .
              "REGLAS OBLIGATORIAS DE FORMATO: " .
              "1. Responde UNICAMENTE con 3 elementos <li> de HTML. " .
              "2. Dentro de cada <li>, pon el nombre del regalo en negrita (<b>). " .
              "3. Despues del nombre, añade un enlace HTML <a>. " .
              "4. El enlace debe tener target='_blank' y el href debe ser: https://www.amazon.es/s?k=NOMBRE_DEL_PRODUCTO_CODIFICADO " .
              "5. El texto del enlace debe ser 'Ver precio'. " .
              "6. No uses emojis ni markdown.";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "<li>Error de conexion: " . $error_msg . "</li>";
    }
    
    curl_close($ch);

    $json = json_decode($response, true);
    
    if (isset($json['error'])) {
        return "<li>Error API: " . $json['error']['message'] . "</li>";
    }

    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        return $json['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "<li>No se pudieron generar ideas. Intentalo de nuevo.</li>";
    }
}
// --- FIN LOGICA DE IA ---

if (!isset($_SESSION['participante_id'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

$id = $_SESSION['participante_id'];

$stmt = $conn->prepare("SELECT * FROM participantes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No existe el participante.");
}

$usuario = $result->fetch_assoc();

// Obtener el grupo del usuario
$stmtGrupo = $conn->prepare("
    SELECT g.nombre FROM grupos g
    JOIN participante_grupo pg ON g.id = pg.id_grupo
    WHERE pg.id_participante = ?
");
$stmtGrupo->bind_param("i", $id);
$stmtGrupo->execute();
$resultGrupo = $stmtGrupo->get_result();
$usuarioGrupo = $resultGrupo->fetch_assoc();
$stmtGrupo->close();

$hobbies = [];
if (!empty($usuario['hobbies'])) {
    $hobbies = json_decode($usuario['hobbies'], true);
}

$stmt2 = $conn->prepare("
    SELECT p.id, p.nombre, p.apellido, p.foto, p.hobbies
    FROM regalos r
    JOIN participantes p ON r.id_receptor = p.id
    WHERE r.id_dador = ?
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$asignado = null;
$hobbies_asignado = [];

if ($result2->num_rows > 0) {
    $asignado = $result2->fetch_assoc();
    if (!empty($asignado['hobbies'])) {
        $hobbies_asignado = json_decode($asignado['hobbies'], true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedir_ayuda'])) {
    if ($asignado) {
        $presupuesto = floatval($_POST['presupuesto']);
        $miApiKey = GEMINI_API_KEY; 

        if ($presupuesto > 0 && !empty($miApiKey) && $miApiKey !== 'PEGA_AQUI_TU_NUEVA_API_KEY_SIN_ESPACIOS') {
            $sugerencias = obtenerSugerencias($hobbies_asignado, $presupuesto, $miApiKey);
        } else {
            $sugerencias = "<li>Error de Configuración: Crea el archivo 'config_api.php' y pon la API Key correcta.</li>";
        }
    }
}

$stmt->close();
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <h2>Bienvenido/a, <?php echo htmlspecialchars($usuario['nombre']); ?></h2>

    <p id="countdown" class="countdown-timer"></p>

    <img src="fotos/<?php echo htmlspecialchars($usuario['foto']); ?>" class="foto-perfil">

    <!-- BOTONES DE NAVEGACIÓN ARRIBA -->
    <div class="nav-buttons">
        <a href="editar_perfil.php" class="nav-link">Editar Perfil</a>
        <?php if ($asignado): ?>
            <a href="#amigo-invisible" class="nav-link">Ver Amigo Invisible</a>
        <?php endif; ?>
        <a href="logout_participante.php" class="nav-link">Cerrar Sesión</a>
    </div>

    <div class="contenedor">
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($usuario['apellido']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Sexo:</strong> <?php echo htmlspecialchars($usuario['gender']); ?></p>
        <p><strong>Grupo:</strong> <?php echo !empty($usuarioGrupo['nombre']) ? htmlspecialchars($usuarioGrupo['nombre']) : 'No asignado'; ?></p>

        <?php if (!empty($hobbies)): ?>
            <p><strong>Tus Hobbies/Gustos:</strong></p>
            <ul class="hobbies-list">
                <?php foreach ($hobbies as $hobby): ?>
                    <li><?php echo htmlspecialchars($hobby); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if ($asignado): ?>
        <div class="sorteo-info" id="amigo-invisible">
            <h3>Tu Amigo Invisible es:</h3>
            <img src="fotos/<?php echo htmlspecialchars($asignado['foto']); ?>" class="asignado-foto">
            <p><strong><?php echo htmlspecialchars($asignado['nombre'] . " " . $asignado['apellido']); ?></strong></p>

            <?php if (!empty($hobbies_asignado)): ?>
                <p><strong>Sus Hobbies/Gustos:</strong></p>
                <ul class="hobbies-list">
                    <?php foreach ($hobbies_asignado as $hobby): ?>
                        <li><?php echo htmlspecialchars($hobby); ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="ai-section">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #DAA520; text-align: center;">¿No sabes qué regalar?</p>
                    
                    <form method="POST" class="ai-form">
                        <label for="presupuesto">Max euros:</label>
                        <input type="number" name="presupuesto" id="presupuesto" value="20" min="1" step="1">
                        <button type="submit" name="pedir_ayuda" class="btn-ia">
                            Pedir ideas
                        </button>
                    </form>

                    <?php if (!empty($sugerencias)): ?>
                        <div class="resultados-ia">
                            <h4>💡 Sugerencias de Regalos:</h4>
                            <ul>
                                <?php echo $sugerencias; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p><em>Este usuario no ha indicado hobbies, será más difícil elegir regalo...</em></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sorteo-info">
            <p>Aún no se ha realizado el sorteo.</p>
        </div>
    <?php endif; ?>

    <script>
        // Timer de cuenta atrás
        var countDownDate = new Date("Dec 22, 2025 14:00:00").getTime();

        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = "Quedan: " + days + " dias, " + hours + " horas, " + minutes + " min, " + seconds + "segs";

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "¡El sorteo ha finalizado!";
            }
        }, 1000);
    </script>

</body>
</html>