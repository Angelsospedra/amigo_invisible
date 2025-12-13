<?php
session_start();

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
        $miApiKey = "AIzaSyDYPIG4NC-ZCTgAU3AaBIqbDH7bevjx5ZI"; 

        if ($presupuesto > 0 && !empty($miApiKey)) {
            $sugerencias = obtenerSugerencias($hobbies_asignado, $presupuesto, $miApiKey);
        } else {
            $sugerencias = "<li>Introduce un presupuesto valido y verifica la API Key.</li>";
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

    <img src="fotos/<?php echo htmlspecialchars($usuario['foto']); ?>" class="foto-perfil">

    <div class="contenedor">
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($usuario['apellido']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Sexo:</strong> <?php echo htmlspecialchars($usuario['gender']); ?></p>

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
        <div class="sorteo-info">
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
                    <p style="font-weight: bold; margin-bottom: 5px; color: #DAA520;">¿No sabes que regalar?</p>
                    
                    <form method="POST" class="ai-form">
                        <label for="presupuesto">Max euros:</label>
                        <input type="number" name="presupuesto" id="presupuesto" value="20" min="1" step="1">
                        <button type="submit" name="pedir_ayuda" class="btn-ia">
                            Pedir ideas
                        </button>
                    </form>

                    <?php if (!empty($sugerencias)): ?>
                        <div class="resultados-ia">
                            <h4>Sugerencias:</h4>
                            <ul>
                                <?php echo $sugerencias; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p><em>Este usuario no ha indicado hobbies, sera mas dificil elegir regalo...</em></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sorteo-info">
            <p>Aun no se ha realizado el sorteo.</p>
        </div>
    <?php endif; ?>

    <div class="links">
        <a href="editar_perfil.php" class="nav-link">Editar Perfil</a>
        <a href="logout_participante.php" class="nav-link">Cerrar sesion</a>
    </div>

</body>
</html>