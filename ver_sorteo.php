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

<h2>Sorteo Actual</h2>
<a href="panel_admin.php">Volver</a>
<hr>

<?php while ($row = $result->fetch_assoc()): ?>

    <img src="fotos/<?php echo $row['dador_foto']; ?>" width="50">
    <?php echo $row['dador_nombre'] . " " . $row['dador_apellido']; ?>

    →

    <img src="fotos/<?php echo $row['rec_foto']; ?>" width="50">
    <?php echo $row['rec_nombre'] . " " . $row['rec_apellido']; ?>

    <br><br>

<?php endwhile; ?>