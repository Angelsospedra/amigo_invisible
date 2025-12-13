<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

// Obtener todos los participantes
$consulta = $conn->query("SELECT * FROM participantes ORDER BY nombre ASC");
$participantes = $consulta->fetch_all(MYSQLI_ASSOC);

// Ver si ya hay sorteo
$hay_sorteo = $conn->query("SELECT COUNT(*) AS total FROM regalos")->fetch_assoc()['total'] > 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="container admin-panel">

        <h2>Panel del Administrador</h2>

        <a href="logout.php"><button>Cerrar sesión</button></a>
        <hr>

        <h3>Participantes</h3>

        <?php foreach ($participantes as $p): ?>
            <div class="participante">

                <img src="fotos/<?php echo htmlspecialchars($p['foto']); ?>">

                <?php echo $p['nombre'] . " " . $p['apellido']; ?>

                &nbsp;&nbsp;

                <a href="borrar_participante.php?id=<?php echo $p['id']; ?>">
                    <button>Borrar</button>
                </a>

            </div>
        <?php endforeach; ?>

        <hr>

        <h3>Sorteo</h3>

        <?php if ($hay_sorteo): ?>

            <p>Ya existe un sorteo generado.</p>
            <a href="ver_sorteo.php"><button>Ver resultados</button></a>

            <form action="rehacer_sorteo.php" method="POST">
                <button type="submit">Rehacer Sorteo</button>
            </form>

        <?php else: ?>

            <form action="sorteo.php" method="POST">
                <button type="submit">Generar Sorteo</button>
            </form>

        <?php endif; ?>

    </div>
</body>
</html>