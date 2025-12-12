<?php
include('conexion.php');

$sql = "SELECT id, nombre, apellido FROM participantes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Foto de Perfil</title>
</head>
<body>

<h2>Subir Foto de Perfil</h2>

<form action="subir_foto_proceso.php" method="POST" enctype="multipart/form-data">
    
    <label>Selecciona tu nombre:</label><br>
    <select name="id_participante" required>
        <option value="">-- Selecciona --</option>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <option value="<?php echo $row['id']; ?>">
                <?php echo $row['nombre'] . " " . $row['apellido']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Sube tu foto (JPG, PNG, JPEG):</label><br>
    <input type="file" name="foto" accept="image/*" required>

    <br><br>

    <button type="submit">Subir Foto</button>

</form>

</body>
</html>
