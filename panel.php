<?php
session_start();

// --- INICIO LOGICA DE IA ---
$sugerencias = ""; 

function obtenerSugerencias($hobbiesArray, $presupuesto, $apiKey) {
    // Convertir array a texto
    $textoHobbies = !empty($hobbiesArray) ? implode(", ", $hobbiesArray) : "gustos variados";

    // Prompt estricto
    $prompt = "Eres un experto en regalos para un amigo invisible. " .
              "A la persona le gusta: " . $textoHobbies . ". " .
              "El presupuesto maximo es de " . $presupuesto . " euros. " .
              "Dame 3 sugerencias de regalos originales. " .
              "Reglas estrictas: " .
              "1. Responde UNICAMENTE con 3 elementos li de HTML. " .
              "2. No uses emojis ni iconos visuales. " .
              "3. No incluyas etiquetas ul ni markdown.";

    // CAMBIO DEFINITIVO: Usamos el alias 'gemini-flash-latest' que salia en tu lista
    // Este apunta siempre a la version estable mas rapida disponible para tu cuenta
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
    
    // Desactivar verificacion SSL (necesario en local)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "<li>Error de conexion: " . $error_msg . "</li>";
    }
    
    curl_close($ch);

    $json = json_decode($response, true);
    
    // Verificacion de errores devueltos por Google
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

// Obtener datos del usuario logueado
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

// Obtener a quien le toca regalar
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

// --- PROCESAR FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedir_ayuda'])) {
    if ($asignado) {
        $presupuesto = floatval($_POST['presupuesto']);
        
        // ----------------------------------------------------
        // PEGA AQUI TU API KEY (asegurate de no dejar espacios extra)
        $miApiKey = "AIzaSyDYPIG4NC-ZCTgAU3AaBIqbDH7bevjx5ZI"; 
        // ----------------------------------------------------

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

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 30px;
            background-color: #f4f4f4;
        }

        .foto-perfil {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .contenedor {
            width: 350px;
            margin: auto;
            text-align: left;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sorteo-info {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            width: 350px;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .asignado-foto {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px auto;
            display: block;
        }

        .hobbies-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }

        .hobbies-list li {
            background-color: #e7f3ff;
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .links {
            margin-top: 20px;
            margin-bottom: 40px;
        }

        a {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        a:hover {
            background-color: #0056b3;
        }

        /* Estilos seccion IA */
        .ai-section {
            margin-top: 20px;
            border-top: 2px dashed #ccc;
            padding-top: 15px;
        }

        .ai-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .ai-form input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 70px;
        }

        .btn-ia {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-ia:hover {
            background-color: #218838;
        }

        .resultados-ia {
            text-align: left;
            background-color: #f9fff9;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
        }

        .resultados-ia h4 {
            margin-top: 0;
            color: #155724;
            font-size: 0.9em;
        }

        .resultados-ia ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
        
        .resultados-ia li {
            margin-bottom: 5px;
            font-size: 0.9em;
        }
    </style>
</head>

<body>

    <h2>Bienvenido/a, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h2>

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
                    <p style="font-weight: bold; margin-bottom: 5px;">No sabes que regalar?</p>
                    
                    <form method="POST" class="ai-form">
                        <label for="presupuesto">Max eur:</label>
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
        <a href="editar_perfil.php">Editar Perfil</a>
        <a href="logout_participante.php">Cerrar sesion</a>
    </div>

</body>
</html>