<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

// SOLO se ejecuta si se recibe un POST desde el botón
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: panel_admin.php");
    exit;
}

// Eliminar sorteo anterior
$conn->query("DELETE FROM regalos");

// Obtener participantes
$sql = "SELECT id FROM participantes";
$result = $conn->query($sql);

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = $row['id'];
}

// FUNCIÓN: generar derangement (asignación válida)
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

// Guardar nuevo sorteo
foreach ($ids as $i => $dador) {
    $receptor = $receptores[$i];

    $stmt = $conn->prepare("INSERT INTO regalos (id_dador, id_receptor) VALUES (?, ?)");
    $stmt->bind_param("ii", $dador, $receptor);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo Regenerado</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="container">
        <h2>Sorteo Regenerado</h2>
        
        <div class="mensaje success">
            ✓ El sorteo se ha regenerado correctamente. Todos los participantes tienen nuevas asignaciones.
        </div>

        <div style="margin-top: 30px; text-align: center; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="panel_admin.php"><button>Volver al Panel</button></a>
            <a href="ver_sorteo.php"><button>Ver Nuevo Sorteo</button></a>
        </div>
    </div>

</body>

</html>