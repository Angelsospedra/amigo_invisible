<?php
include 'conexion.php';

// Primero, verificar si la tabla regalos tiene columna fecha_registro
$check_column = $conn->query("SHOW COLUMNS FROM regalos LIKE 'fecha_registro'");
if ($check_column->num_rows == 0) {
    // Si no existe, crear la columna con fecha actual por defecto
    $conn->query("ALTER TABLE regalos ADD COLUMN fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

// Obtener años disponibles
$query_años = "SELECT DISTINCT YEAR(fecha_registro) as año FROM regalos ORDER BY año DESC";
$result_años = $conn->query($query_años);

// Obtener año seleccionado (por defecto el actual)
$año_seleccionado = isset($_GET['año']) ? (int) $_GET['año'] : date('Y');

// Obtener regalos del año seleccionado
$query_regalos = "SELECT r.id, p1.nombre as dador, p1.apellido as apellido_dador, p1.foto as foto_dador,
                         p2.nombre as receptor, p2.apellido as apellido_receptor, p2.foto as foto_receptor
                  FROM regalos r
                  JOIN participantes p1 ON r.id_dador = p1.id
                  JOIN participantes p2 ON r.id_receptor = p2.id
                  WHERE YEAR(r.fecha_registro) = $año_seleccionado
                  ORDER BY r.fecha_registro DESC";
$result_regalos = $conn->query($query_regalos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Amigo Invisible</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <div class="container">
        <h1>Historial Amigo Invisible</h1>

        <div class="filter-section">
            <form method="GET" action="historial.php">
                <label for="año">Seleccionar año:</label>
                <select name="año" id="año" onchange="this.form.submit()">
                    <?php
                    if ($result_años && $result_años->num_rows > 0) {
                        $result_años->data_seek(0);
                        while ($row = $result_años->fetch_assoc()) {
                            $selected = ($row['año'] == $año_seleccionado) ? 'selected' : '';
                            echo "<option value='" . $row['año'] . "' $selected>" . $row['año'] . "</option>";
                        }
                    } else {
                        echo "<option value='" . date('Y') . "' selected>" . date('Y') . "</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <div class="historial-section">
            <h2>Año: <?php echo $año_seleccionado; ?></h2>

            <?php if ($result_regalos && $result_regalos->num_rows > 0) { ?>
                <table class="historial-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Quien Regala</th>
                            <th>➜</th>
                            <th></th>
                            <th>Quien Recibe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_regalos->fetch_assoc()) { ?>
                            <tr>
                                <td class="foto-cell">
                                    <img src="fotos/<?php echo htmlspecialchars($row['foto_dador']); ?>"
                                        alt="<?php echo htmlspecialchars($row['dador']); ?>" class="foto-historial">
                                </td>
                                <td><?php echo htmlspecialchars($row['dador'] . ' ' . $row['apellido_dador']); ?></td>
                                <td class="arrow">→</td>
                                <td class="foto-cell">
                                    <img src="fotos/<?php echo htmlspecialchars($row['foto_receptor']); ?>"
                                        alt="<?php echo htmlspecialchars($row['receptor']); ?>" class="foto-historial">
                                </td>
                                <td><?php echo htmlspecialchars($row['receptor'] . ' ' . $row['apellido_receptor']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="no-data">No hay asignaciones registradas para el año <?php echo $año_seleccionado; ?></p>
            <?php } ?>
        </div>

        <a href="ver_sorteo.php" class="btn-back">← Volver al sorteo actual</a>
    </div>
</body>

</html>

<?php $conn->close(); ?>