<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "amigo";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
