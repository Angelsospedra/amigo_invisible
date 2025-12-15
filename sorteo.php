<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

$grupo_id = isset($_POST['grupo_id']) ? intval($_POST['grupo_id']) : 0;

if ($grupo_id === 0) {
    die("Error: No se ha especificado un grupo.");
}

// 1. Verificar si YA existe sorteo para ESTE grupo
// (Importante: la tabla 'regalos' no tiene grupo_id, así que lo inferimos por los participantes)
$sql_check = "
    SELECT COUNT(*) AS total 
    FROM regalos r
    INNER JOIN participante_grupo pg ON r.id_dador = pg.id_participante
    WHERE pg.id_grupo = ?
";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $grupo_id);
$stmt->execute();
$check = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($check["total"] > 0) {
    die("<h3>Ya existe un sorteo para este grupo.</h3><a href='panel_admin.php?grupo_id=$grupo_id'>Volver</a>");
}

// Obtener participantes
$sql = "SELECT id FROM participantes";
$result = $conn->query($sql);

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = $row['id'];
}

// 3. FUNCIÓN: generar derangement (Tu lógica original estaba bien)
function generarSorteo($ids) {
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
