<?php
session_start();

// --- 1. CARGA SEGURA DE LA API KEY ---
// Buscamos si existe el archivo de configuración externo
if (file_exists('config_api.php')) {
    include('config_api.php');
}

// Si por alguna razón el archivo no existe o no se definió la constante,
// definimos una por defecto vacía para evitar errores fatales en PHP.
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

    // Usamos el modelo estable
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

// --- PROCESAMIENTO DEL FORMULARIO CON SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedir_ayuda'])) {
    if ($asignado) {
        $presupuesto = floatval($_POST['presupuesto']);
        
        // AQUI ESTA LA MAGIA: Leemos la constante del archivo externo
        $miApiKey = GEMINI_API_KEY; 

        // Verificamos que la key no esté vacía ni sea el texto de ejemplo
        if ($presupuesto > 0 && !empty($miApiKey) && $miApiKey !== 'PEGA_AQUI_TU_NUEVA_API_KEY_SIN_ESPACIOS') {
            $sugerencias = obtenerSugerencias($hobbies_asignado, $presupuesto, $miApiKey);
        } else {
            // Mensaje de error amigable para el equipo de desarrollo
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
    <style>
        /* Estilos inline para asegurar que la sección nueva se vea bien si no has actualizado estilos.css */
        .ai-section { margin-top: 20px; border-top: 2px dashed #ccc; padding-top: 15px; }
        .ai-form { display: flex; justify-content: center; align-items: center; gap: 5px; margin-bottom: 15px; }
        .ai-form input { padding: 5px; border: 1px solid #ccc; border-radius: 4px; width: 70px; }
        .btn-ia { background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-ia:hover { background-color: #218838; }
        .resultados-ia { text-align: left; background-color: #f9fff9; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; }
        .resultados-ia h4 { margin-top: 0; color: #155724; font-size: 0.9em; }
        .resultados-ia ul { padding-left: 20px; margin-bottom: 0; }
        .resultados-ia li { margin-bottom: 10px; font-size: 0.95em; line-height: 1.5; }
        .resultados-ia a { display: inline-block; margin-left: 8px; font-size: 0.85em; color: #d35400; text-decoration: underline; font-weight: bold; }
        .resultados-ia a:hover { color: #e67e22; }
        .btn-anchor { display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #196c2fff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        
    </style>
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

        <?php if ($asignado): ?>
            <a href="#amigo-invisible" class="btn-anchor">Ver mi amigo invisible</a>
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