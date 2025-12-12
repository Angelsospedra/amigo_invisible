<?php
include('conexion.php');

$sql = "SELECT id FROM participantes";
$resultados = $conn->query($sql);

if ($resultados->num_rows > 0) {

    $participantes = [];
    while ($row = $resultados->fetch_assoc()) {
        $participantes[] = $row["id"];
    }

    $participantes_total = count($participantes);

    $ya_regalados = [];

    function sacaNumeroValido($i, $ya_regalados, $total) {

        while (true) {

            $num = rand(0, $total - 1);

            if ($num == $i) continue;

            if (in_array($num, $ya_regalados)) continue;

            return $num;
        }
    }

    for ($i = 0; $i < $participantes_total; $i++) {

        $numero_aleatorio = sacaNumeroValido(
            $i,
            $ya_regalados,
            $participantes_total
        );

        $ya_regalados[] = $numero_aleatorio;

        $regalar  = $participantes[$i];
        $regalado = $participantes[$numero_aleatorio];

        $sql2 = "INSERT INTO regalos (regalar, regalado) VALUES ($regalar, $regalado)";
        $conn->query($sql2);
    }

    echo "Sorteo realizado correctamente.";
}
?>
