<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include('conexion.php');

// 1. Obtener todos los grupos para el desplegable
$grupos = $conn->query("SELECT * FROM grupos ORDER BY nombre ASC");

// 2. Verificar si se ha seleccionado un grupo
$grupo_id = isset($_GET['grupo_id']) ? intval($_GET['grupo_id']) : null;
$participantes = [];
$hay_sorteo = false;

if ($grupo_id) {
    // 3. Obtener participantes SOLO del grupo seleccionado usando JOIN
    $stmt = $conn->prepare("
        SELECT p.* FROM participantes p
        INNER JOIN participante_grupo pg ON p.id = pg.id_participante
        WHERE pg.id_grupo = ?
        ORDER BY p.nombre ASC
    ");
    $stmt->bind_param("i", $grupo_id);
    $stmt->execute();
    $participantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 4. Verificar si YA existe un sorteo para ESTE grupo
    // Miramos si alguno de los participantes de este grupo ya es "dador" en la tabla regalos
    $stmt_check = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM regalos r
        INNER JOIN participante_grupo pg ON r.id_dador = pg.id_participante
        WHERE pg.id_grupo = ?
    ");
    $stmt_check->bind_param("i", $grupo_id);
    $stmt_check->execute();
    $hay_sorteo = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
    $stmt_check->close();
}
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

        <div class="actions-top">
            <a href="logout.php"><button>Cerrar sesión</button></a>
            </div>
        <hr>

        <form action="panel_admin.php" method="GET" class="grupo-selector">
            <label><strong>Gestionar Grupo:</strong></label>
            <select name="grupo_id" onchange="this.form.submit()">
                <option value="">-- Selecciona un grupo --</option>
                <?php while ($g = $grupos->fetch_assoc()): ?>
                    <option value="<?php echo $g['id']; ?>" <?php echo ($grupo_id == $g['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($grupo_id): ?>
            
            <h3>Participantes del Grupo</h3>
            
            <?php if (count($participantes) > 0): ?>
                <?php foreach ($participantes as $p): ?>
                    <div class="participante">
                        <img src="fotos/<?php echo htmlspecialchars($p['foto']); ?>">
                        <?php echo $p['nombre'] . " " . $p['apellido']; ?>
                        &nbsp;&nbsp;
                        <a href="borrar_participante.php?id=<?php echo $p['id']; ?>&grupo_id=<?php echo $grupo_id; ?>">
                            <button>Borrar</button>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay participantes en este grupo aún.</p>
            <?php endif; ?>

            <hr>

            <h3>Sorteo del Grupo</h3>

            <?php if ($hay_sorteo): ?>
                <p>✅ Ya existe un sorteo generado para este grupo.</p>
                
                <a href="ver_sorteo.php?grupo_id=<?php echo $grupo_id; ?>">
                    <button>Ver resultados</button>
                </a>

                <form action="rehacer_sorteo.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="grupo_id" value="<?php echo $grupo_id; ?>">
                    <button type="submit" onclick="return confirm('¿Seguro que quieres borrar el sorteo actual y hacer uno nuevo?');">Rehacer Sorteo</button>
                </form>

            <?php else: ?>
                <?php if (count($participantes) < 3): ?>
                     <p>⚠️ Necesitas al menos 3 participantes para hacer el sorteo.</p>
                <?php else: ?>
                    <form action="sorteo.php" method="POST">
                        <input type="hidden" name="grupo_id" value="<?php echo $grupo_id; ?>">
                        <button type="submit">Generar Sorteo</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <p>👈 Por favor, selecciona un grupo arriba para empezar.</p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>