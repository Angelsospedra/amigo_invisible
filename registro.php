<?php
session_start();
include('conexion.php');

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST['nombre'] ?? "";
    $apellido = $_POST['apellido'] ?? "";
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $hobbies = $_POST['hobby'] ?? [];

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($gender)) {
        $mensaje = "Por favor completa todos los campos requeridos.";
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

            // Insertar participante
            $stmt2 = $conn->prepare("
                INSERT INTO participantes (nombre, apellido, email, password, gender, foto, hobbies)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt2->bind_param("sssssss", $nombre, $apellido, $email, $password_hash, $gender, $foto_nombre, $hobbies_json);

            if ($stmt2->execute()) {
                $mensaje = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                // Redirigir después de 2 segundos
                header("Refresh: 2; url=login.php");
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
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .container {
            width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color: #218838;
        }

        .hobbies-container {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .hobby-input {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .hobby-input input {
            flex: 1;
            margin-bottom: 0;
        }

        .btn-remove {
            background-color: #dc3545;
            width: auto;
            padding: 8px 15px;
            margin-bottom: 0;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        .btn-add-hobby {
            background-color: #17a2b8;
            margin-top: 10px;
        }

        .btn-add-hobby:hover {
            background-color: #138496;
        }

        .mensaje {
            color: green;
            margin-top: 10px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
        }

        .mensaje.error {
            color: red;
            background-color: #f8d7da;
        }

        a {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Registro de Participante</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <label>Sexo:</label>
            <select name="gender" required>
                <option value="">Selecciona...</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
            </select>

            <label>Foto (opcional):</label>
            <input type="file" name="foto" accept="image/*">

            <!-- SECCIÓN DE HOBBIES -->
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
            <p class="mensaje <?php echo strpos($mensaje, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>

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