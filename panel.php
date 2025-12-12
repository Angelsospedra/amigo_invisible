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
$stmt->close();
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
    </div>

    <br>

    <a href="editar_perfil.php">Editar Perfil</a>
    <br><br>
    <a href="logout.php">Cerrar sesión</a>

</body>

</html>