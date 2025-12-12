<?php
include('conexion.php');

// Verificar que llega un archivo y un ID
if (!isset($_POST['id_participante']) || !isset($_FILES['foto'])) {
    die("Error: datos incompletos.");
}

$id = $_POST['id_participante'];
$foto = $_FILES['foto'];

// Validar que no haya errores
if ($foto['error'] !== 0) {
    die("Error al subir la imagen.");
}

// Extensiones permitidas
$ext_permitidas = ['jpg', 'jpeg', 'png', 'svg'];
$nombre_original = $foto['name'];
$extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

if (!in_array($extension, $ext_permitidas)) {
    die("Error: solo se permiten fotos JPG o PNG.");
}

// Crear un nombre único basado en el ID del usuario
$nombre_archivo = "foto_" . $id . "." . $extension;

// Ruta final donde se guardará dentro de la carpeta fotos
$ruta_destino = "fotos/" . $nombre_archivo;

// Mover la foto al servidor
if (!move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
    die("Error al guardar la imagen en el servidor.");
}

// Guardar la ruta en la base de datos
$stmt = $conn->prepare("UPDATE participantes SET foto = ? WHERE id = ?");
$stmt->bind_param("si", $nombre_archivo, $id);

if ($stmt->execute()) {
    echo "<h3>Foto subida correctamente.</h3>";
} else {
    echo "Error al actualizar la base de datos.";
}

$stmt->close();
$conn->close();

echo "<br><br><a href='subir_foto.php'>Volver</a>";
echo "<br><a href='sorteo.php'>Ir al sorteo</a>";
?>
