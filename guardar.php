<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
$nombre = $_POST['fname'];
$apellido = $_POST['lname'];
$email    = $_POST['email'];
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_DEFAULT);
$gender   = $_POST['gender'];
$foto     = $_FILES['foto'];

$conn = new mysqli("localhost", "root", "", "amigo");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// SUBIR FOTO
if ($foto['error'] !== 0) {
    die("Error al subir la imagen.");
}

$ext_permitidas = ['jpg', 'jpeg', 'png'];
$nombre_original = $foto['name'];
$extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

if (!in_array($extension, $ext_permitidas)) {
    die("Error: solo se permiten fotos JPG o PNG.");
}

$nombre_archivo = "foto_" . time() . "_" . rand(1000, 9999) . "." . $extension;
$ruta_destino = "fotos/" . $nombre_archivo;

if (!move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
    die("Error al guardar la imagen en el servidor.");
}

// INSERTAR PARTICIPANTE EN BD
$stmt = $conn->prepare("
    INSERT INTO participantes (nombre, apellido, email, password, gender, foto)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssss",
    $nombre,
    $apellido,
    $email,
    $hash,
    $gender,
    $nombre_archivo
);


if ($stmt->execute()) {
    // Guardamos el ID recién insertado
    $id_nuevo = $conn->insert_id;

    // Iniciar sesión automáticamente
    session_start();
    $_SESSION['participante_id'] = $id_nuevo;

    // Redirigir al panel del participante
    header("Location: panel.php");
    exit;

} else {
    echo "Error: " . $stmt->error;    
}

$stmt->close();
$conn->close();

?>

<br>

<button onclick="window.location.href='index.html'">Volver</button>