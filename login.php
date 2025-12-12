<?php

include('conexion.php');

$email = $_POST['email'] ?? '';

$mensaje = "";
$nombre_regalado = "";

if ($email !== '') {

    $sql = "SELECT * FROM participantes WHERE email = '$email'";
    $resultados = $conn->query($sql);

    if ($resultados && $resultados->num_rows > 0) {

        $fila = $resultados->fetch_assoc();
        $id = $fila['id'];

        $sql2 = "SELECT participantes.* FROM regalos 
                 INNER JOIN participantes ON participantes.id = regalos.regalado 
                 WHERE regalos.regalar = $id";
        $resultados2 = $conn->query($sql2);

        if ($resultados2 && $resultados2->num_rows > 0) {

            $fila2 = $resultados2->fetch_assoc();
            $nombre_regalado = $fila2['nombre'];
            $mensaje = "Tu amigo invisible es:";
        } else {
            $mensaje = "No tienes un amigo invisible asignado.";
        }

    } else {
        $mensaje = "El correo no existe en la base de datos.";
    }

} else {
    $mensaje = "Falta el email.";
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado Amigo Invisible</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef2f3;
        }
        .resultado-card {
            max-width: 450px;
            margin: 80px auto;
            padding: 25px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 0 18px rgba(0,0,0,0.12);
        }
        .nombre-amigo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="resultado-card text-center">
    <h3 class="mb-3"><?php echo $mensaje; ?></h3>

    <?php if ($nombre_regalado !== ""): ?>
        <p class="nombre-amigo"><?php echo $nombre_regalado; ?></p>
    <?php endif; ?>

    <a href="login.html" class="btn btn-primary mt-4 w-100">Volver</a>
</div>

</body>
</html>
