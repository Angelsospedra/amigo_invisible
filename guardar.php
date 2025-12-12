<?php
include "conexion.php";

$nombre    = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';

if ($nombre !== "" && $apellidos !== "") {

    $sql = "SELECT * FROM participantes WHERE nombre = '$nombre' AND apellidos = '$apellidos'";
    $resultados = $conn->query($sql);

    if ($resultados && $resultados->num_rows > 0) {
        echo "El usuario ya existe";
    } else {

        $stmt = $conn->prepare("INSERT INTO participantes (nombre, apellidos) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $apellidos);

        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "ERROR";
        }

        $stmt->close();
    }
}

$conn->close();
?>
