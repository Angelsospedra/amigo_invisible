<?php
session_start();

if (!isset($_SESSION['participante_id'])) {
    die("Error: No hay sesión activa.");
}

$id = $_SESSION['participante_id'];


include('conexion.php');

// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM participantes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No existe un participante con ese ID.");
}

$usuario = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>

    <style>
        .form-container {
            width: 350px;
            margin: 20px auto;
            font-family: Arial;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }

        .perfil-foto {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 10px;
        }
    </style>

</head>

<body>

    <div class="form-container">

        <h2>Editar Perfil</h2>

        <?php if ($usuario['foto']) { ?>
            <img src="fotos/<?php echo $usuario['foto']; ?>" class="perfil-foto">
        <?php } ?>

        <form action="editar_perfil_proceso.php" method="POST" enctype="multipart/form-data">

            <!-- Enviar ID oculto -->
            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $usuario['email']; ?>" required>

            <label>Sexo:</label>
            <select name="gender" required>
                <option value="masculino" <?php echo $usuario['gender'] == "masculino" ? "selected" : ""; ?>>Masculino</option>
                <option value="femenino" <?php echo $usuario['gender'] == "femenino" ? "selected" : ""; ?>>Femenino</option>
            </select>

            <label>Cambiar foto (opcional):</label>
            <input type="file" name="foto" accept="image/*">

            <button type="submit">Guardar Cambios</button>

        </form>

        <br>
        <a href="panel.php">Volver al perfil</a>

    </div>
</body>
</html>