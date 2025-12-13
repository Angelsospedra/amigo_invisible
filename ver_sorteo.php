<?php
session_start();
if (!isset($_SESSION['admin'])) {
    die("Acceso denegado.");
}

include("conexion.php");

$result = $conn->query("
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
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ver Sorteo</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="container admin-panel">
        <h2>Sorteo Actual</h2>
        
        <a href="panel_admin.php"><button>Volver al Panel</button></a>
        
        <hr>

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
    </div>

</body>
</html>