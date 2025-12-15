<?php
session_start();
include('conexion.php');

$mensaje = "";

// 1. OBTENER GRUPOS PARA EL DESPLEGABLE
$sql_grupos = "SELECT * FROM grupos";
$result_grupos = $conn->query($sql_grupos);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST['nombre'] ?? "";
    $apellido = $_POST['apellido'] ?? "";
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $grupo_id = $_POST['grupo_id'] ?? ""; 
    $hobbies = $_POST['hobby'] ?? [];

    // Validamos datos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($gender) || empty($grupo_id)) {
        $mensaje = "Por favor completa todos los campos requeridos, incluyendo el grupo.";
    } else {

        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM participantes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mensaje = "El email ya está registrado.";
        } else {

            // Procesar foto
            $foto_nombre = "default.jpg";
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext_permitidas = ['jpg', 'jpeg', 'png'];
                $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

                if (in_array($extension, $ext_permitidas)) {
                    $foto_nombre = "foto_" . time() . "_" . rand(1000, 9999) . "." . $extension;
                    move_uploaded_file($_FILES['foto']['tmp_name'], "fotos/" . $foto_nombre);
                }
            }

            // Convertir hobbies a JSON
            $hobbies_filtrados = array_filter($hobbies, function($h) {
                return !empty(trim($h));
            });
            $hobbies_json = !empty($hobbies_filtrados) ? json_encode(array_values($hobbies_filtrados)) : NULL;

            // Hash de contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 2. INSERTAR PARTICIPANTE
            $stmt2 = $conn->prepare("
                INSERT INTO participantes (nombre, apellido, email, password, gender, foto, hobbies)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt2->bind_param("sssssss", $nombre, $apellido, $email, $password_hash, $gender, $foto_nombre, $hobbies_json);

            if ($stmt2->execute()) {
                
                // 3. RECUPERAR EL ID DEL USUARIO RECIÉN CREADO
                // Esta función devuelve el ID autoincremental de la última consulta INSERT
                $nuevo_participante_id = $conn->insert_id;

                // 4. INSERTAR EN LA TABLA INTERMEDIA (participante_grupo)
                $stmt_grupo = $conn->prepare("INSERT INTO participante_grupo (id_participante, id_grupo) VALUES (?, ?)");
                $stmt_grupo->bind_param("ii", $nuevo_participante_id, $grupo_id);
                
                if($stmt_grupo->execute()){
                    $mensaje = "¡Registro exitoso y grupo asignado! Redirigiendo al login...";
                    header("Refresh: 2; url=login.php");
                } else {
                    $mensaje = "Registro parcial: Usuario creado pero error al asignar grupo.";
                }
                $stmt_grupo->close();

            } else {
                $mensaje = "Error al registrar. Intenta de nuevo.";
            }

            $stmt2->close();
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="container">

        <h2>Registro de Participante</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Nombre:</label>
            <input type="text" name="nombre" placeholder="Tu nombre..." required>

            <label>Apellido:</label>
            <input type="text" name="apellido" placeholder="Tu apellido..." required>

            <label>Email:</label>
            <input type="email" name="email" placeholder="tu@email.com" required>

            <label>Contraseña:</label>
            <input type="password" name="password" placeholder="Elige una contraseña..." required>

            <label>Grupo:</label>
            <select name="grupo_id" required>
                <option value="">Selecciona un grupo...</option>
                <?php 
                // Recorremos los resultados de la consulta hecha al inicio
                if ($result_grupos->num_rows > 0) {
                    while($grupo = $result_grupos->fetch_assoc()) {
                        echo '<option value="' . $grupo['id'] . '">' . htmlspecialchars($grupo['nombre']) . '</option>';
                    }
                }
                ?>
            </select>

            <label>Sexo:</label>
            <select name="gender" required>
                <option value="">Selecciona...</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
            </select>

            <label>Foto (opcional):</label>
            <input type="file" name="foto" accept="image/*">

            <div class="hobbies-container">
                <label><strong>Tus Hobbies/Gustos (máximo 3):</strong></label>

                <div id="hobbies-list">
                    <div class="hobby-input">
                        <input type="text" name="hobby[]" placeholder="Ej: Videojuegos">
                    </div>
                </div>

                <button type="button" class="btn-add-hobby" id="btn-add-hobby">+ Añadir otro gusto</button>
            </div>
            <br>

            <button type="submit">Registrarse</button>

        </form>

        <?php if ($mensaje): ?>
            <p class="mensaje <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'registrado') !== false) ? 'error' : 'success'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>

    </div>

    <script>
        const MAX_HOBBIES = 3;

        document.getElementById("btn-add-hobby").addEventListener("click", function() {
            const hobbyList = document.getElementById("hobbies-list");
            const currentHobbies = hobbyList.querySelectorAll(".hobby-input").length;

            if (currentHobbies < MAX_HOBBIES) {
                const newHobby = document.createElement("div");
                newHobby.className = "hobby-input";
                newHobby.innerHTML = `
                    <input type="text" name="hobby[]" placeholder="Ej: Lectura">
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove(); updateButtonState();">✕</button>
                `;
                hobbyList.appendChild(newHobby);
                updateButtonState();
            }
        });

        function updateButtonState() {
            const hobbyList = document.getElementById("hobbies-list");
            const currentHobbies = hobbyList.querySelectorAll(".hobby-input").length;
            const button = document.getElementById("btn-add-hobby");

            button.disabled = currentHobbies >= MAX_HOBBIES;
            button.style.opacity = currentHobbies >= MAX_HOBBIES ? "0.5" : "1";
        }

        updateButtonState();
    </script>

</body>

</html>