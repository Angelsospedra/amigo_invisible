<?php
include('conexion.php');

$id       = $_POST['id'];
$nombre   = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email    = $_POST['email'];
$gender   = $_POST['gender'];
$hobbies  = $_POST['hobby'] ?? [];

// Filtrar hobbies vacíos y convertir a JSON
$hobbies_filtrados = array_filter($hobbies, function($h) {
    return !empty(trim($h));
});
$hobbies_json = !empty($hobbies_filtrados) ? json_encode(array_values($hobbies_filtrados)) : NULL;

// Actualizar datos básicos
$stmt = $conn->prepare("UPDATE participantes SET nombre=?, apellido=?, email=?, gender=?, hobbies=? WHERE id=?");
$stmt->bind_param("sssssi", $nombre, $apellido, $email, $gender, $hobbies_json, $id);
$stmt->execute();
$stmt->close();

// Si se subió una nueva foto, la procesamos
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {

    $ext_permitidas = ['jpg', 'jpeg', 'png'];
    $nombre_original = $_FILES['foto']['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    if (in_array($extension, $ext_permitidas)) {

        $nombre_archivo = "foto_" . time() . "_" . rand(1000, 9999) . "." . $extension;
        $ruta_destino = "fotos/" . $nombre_archivo;

        // Mover a carpeta fotos
        move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino);

        // Actualizar la BD con el nuevo nombre
        $stmt2 = $conn->prepare("UPDATE participantes SET foto=? WHERE id=?");
        $stmt2->bind_param("si", $nombre_archivo, $id);
        $stmt2->execute();
        $stmt2->close();
    }
}

$conn->close();

// Redirigir de vuelta al perfil
header("Location: panel.php");
exit;
