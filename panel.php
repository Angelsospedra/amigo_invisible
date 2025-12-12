<?php
session_start();

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

// Decodificar hobbies
$hobbies = [];
if (!empty($usuario['hobbies'])) {
    $hobbies = json_decode($usuario['hobbies'], true);
}

// Obtener a quién le toca regalar
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
if ($result2->num_rows > 0) {
    $asignado = $result2->fetch_assoc();
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
            font-family: Arial;
            text-align: center;
            margin-top: 30px;
        }

        .foto-perfil {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .contenedor {
            width: 350px;
            margin: auto;
            text-align: left;
        }

        .sorteo-info {
            margin-top: 30px;
            padding: 20px;
            background: #f0f0f0;
            border-radius: 8px;
            width: 350px;
            margin-left: auto;
            margin-right: auto;
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
        }

        a {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <h2>Bienvenido/a, <?php echo $usuario['nombre']; ?>!</h2>

    <img src="fotos/<?php echo $usuario['foto']; ?>" class="foto-perfil">

    <div class="contenedor">
        <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
        <p><strong>Apellido:</strong> <?php echo $usuario['apellido']; ?></p>
        <p><strong>Email:</strong> <?php echo $usuario['email']; ?></p>
        <p><strong>Sexo:</strong> <?php echo $usuario['gender']; ?></p>

        <?php if (!empty($hobbies)): ?>
            <p><strong>Tus Hobbies/Gustos:</strong></p>
            <ul class="hobbies-list">
                <?php foreach ($hobbies as $hobby): ?>
                    <li>🎯 <?php echo $hobby; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if ($asignado): ?>
        <div class="sorteo-info">
            <h3>🎁 Tu Amigo Invisible es:</h3>
            <img src="fotos/<?php echo $asignado['foto']; ?>" class="asignado-foto">
            <p><strong><?php echo $asignado['nombre'] . " " . $asignado['apellido']; ?></strong></p>

            <?php 
                $hobbies_asignado = [];
                if (!empty($asignado['hobbies'])) {
                    $hobbies_asignado = json_decode($asignado['hobbies'], true);
                }
                if (!empty($hobbies_asignado)): 
            ?>
                <p><strong>Sus Hobbies/Gustos:</strong></p>
                <ul class="hobbies-list">
                    <?php foreach ($hobbies_asignado as $hobby): ?>
                        <li>🎯 <?php echo $hobby; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sorteo-info">
            <p>Aún no se ha realizado el sorteo.</p>
        </div>
    <?php endif; ?>

    <div class="links">
        <a href="editar_perfil.php">Editar Perfil</a>
        <a href="logout_participante.php">Cerrar sesión</a>
    </div>

</body>

</html>