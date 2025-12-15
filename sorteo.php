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

// Obtener todos los grupos
$gruposQuery = $conn->query("SELECT id, nombre FROM grupos");
$grupos = [];
while ($row = $gruposQuery->fetch_assoc()) {
    $grupos[] = $row;
}

// Realizar sorteo por cada grupo
$totalGrupos = count($grupos);
$gruposExitosos = 0;

foreach ($grupos as $grupo) {
    // Obtener participantes del grupo
    $sql = "SELECT p.id FROM participantes p
            JOIN participante_grupo pg ON p.id = pg.id_participante
            WHERE pg.id_grupo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $grupo['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $stmt->close();
    
    // Si hay al menos 2 participantes en el grupo, hacer sorteo
    if (count($ids) >= 2) {
        $receptores = generarSorteo($ids);
        
        // Guardar sorteo para este grupo
        foreach ($ids as $i => $dador) {
            $receptor = $receptores[$i];
            
            $stmtInsert = $conn->prepare("INSERT INTO regalos (id_dador, id_receptor) VALUES (?, ?)");
            $stmtInsert->bind_param("ii", $dador, $receptor);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
        $gruposExitosos++;
    }
}

echo "<h3>Sorteo generado y guardado correctamente.</h3>";
echo "<p>Grupos procesados: <strong>" . $gruposExitosos . "</strong> de <strong>" . $totalGrupos . "</strong></p>";
echo "<a href='panel_admin.php'>Volver al Panel</a>";
