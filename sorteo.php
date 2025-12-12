<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

// SOLO se ejecuta si se recibe un POST desde el botón
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h3>No puedes acceder directamente a esta página.</h3>
        <a href='panel_admin.php'>Volver</a>");
}

// Verificar que no exista un sorteo previo
$check = $conn->query("SELECT COUNT(*) AS total FROM regalos");
$datos = $check->fetch_assoc();

if ($datos["total"] > 0) {
    die("<h3>Ya existe un sorteo guardado. No se puede generar otro.</h3>
        <a href='panel_admin.php'>Volver</a>");
}

// Obtener participantes
$sql = "SELECT id FROM participantes";
$result = $conn->query($sql);

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = $row['id'];
}

// FUNCIÓN: generar derangement
function generarSorteo($ids)
{
    $receptores = $ids;
    do {
        shuffle($receptores);
        $valido = true;
        for ($i = 0; $i < count($ids); $i++) {
            if ($ids[$i] == $receptores[$i]) {
                $valido = false;
                break;
            }
        }
    } while (!$valido);

    return $receptores;
}

$receptores = generarSorteo($ids);

// Guardar sorteo
foreach ($ids as $i => $dador) {
    $receptor = $receptores[$i];

    $stmt = $conn->prepare("INSERT INTO regalos (id_dador, id_receptor) VALUES (?, ?)");
    $stmt->bind_param("ii", $dador, $receptor);
    $stmt->execute();
    $stmt->close();
}

echo "<h3>Sorteo generado y guardado correctamente.</h3>
    <a href='panel_admin.php'>Volver al Panel</a>";
