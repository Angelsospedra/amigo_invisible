<?php
session_start();
if (!isset($_SESSION['admin'])) {
    die("Acceso denegado.");
}

include("conexion.php");

$grupo_id = isset($_GET['grupo_id']) ? intval($_GET['grupo_id']) : 0;

if ($grupo_id === 0) {
    die("Error: Grupo no especificado.");
}

// 1. Obtener el nombre del grupo para mostrarlo
$sql_grupo = "SELECT nombre FROM grupos WHERE id = ?";
$stmt_grupo = $conn->prepare($sql_grupo);
$stmt_grupo->bind_param("i", $grupo_id);
$stmt_grupo->execute();
$res_grupo = $stmt_grupo->get_result();

if ($fila_grupo = $res_grupo->fetch_assoc()) {
    $nombre_grupo = $fila_grupo['nombre'];
} else {
    $nombre_grupo = "Grupo Desconocido";
}
$stmt_grupo->close();


// 2. Consulta filtrada por grupo (He corregido los nombres de las columnas para que coincidan con tu DB anterior: participante_id y grupo_id)
$stmt = $conn->prepare("
    SELECT 
        p1.nombre AS dador_nombre,
        p1.apellido AS dador_apellido,
        p1.foto AS dador_foto,
        p2.nombre AS rec_nombre,
        p2.apellido AS rec_apellido,
        p2.foto AS rec_foto
    FROM regalos r
    JOIN participantes p1 ON r.id_dador = p1.id
    JOIN participantes p2 ON r.id_receptor = p2.id
    JOIN participante_grupo pg ON p1.id = pg.id_participante
    WHERE pg.id_grupo = ?
");

$stmt->bind_param("i", $grupo_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Sorteo - <?php echo htmlspecialchars($nombre_grupo); ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="container admin-panel">
        <h2>Resultados del Sorteo: <span style="color: #DAA520;"><?php echo htmlspecialchars($nombre_grupo); ?></span></h2>

        <a href="panel_admin.php?grupo_id=<?php echo $grupo_id; ?>"><button>Volver al Panel</button></a>
        <hr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="sorteo-row">
                    <div class="sorteo-persona">
                        <img src="fotos/<?php echo htmlspecialchars($row['dador_foto']); ?>">
                        <span><?php echo htmlspecialchars($row['dador_nombre'] . " " . $row['dador_apellido']); ?></span>
                    </div>

                    <span class="sorteo-flecha">→</span>

                    <div class="sorteo-persona">
                        <img src="fotos/<?php echo htmlspecialchars($row['rec_foto']); ?>">
                        <span><?php echo htmlspecialchars($row['rec_nombre'] . " " . $row['rec_apellido']); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay resultados para mostrar en este grupo.</p>
        <?php endif; ?>
    </div>

</body>
</html>