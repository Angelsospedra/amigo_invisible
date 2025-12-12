<?php
session_start();
if (!isset($_SESSION['admin'])) {
    die("Acceso denegado.");
}

include('conexion.php');

$id = $_GET['id'];

// Borrar foto del servidor
$foto = $conn->query("SELECT foto FROM participantes WHERE id=$id")->fetch_assoc()['foto'];
unlink("fotos/" . $foto);

// Borrar de BD
$conn->query("DELETE FROM participantes WHERE id=$id");

header("Location: panel_admin.php");
exit;
