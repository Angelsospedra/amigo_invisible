<?php
session_start();
include("conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = $_POST['tipo'];

    // =========================
    // LOGIN DE ADMINISTRADOR
    // =========================
    if ($tipo === "admin") {

        $admin_pass = "1234"; // Cambiar si quieres

        if ($_POST['password_admin'] === $admin_pass) {

            $_SESSION['admin'] = true;
            header("Location: panel_admin.php");
            exit;
        } else {
            $mensaje = "Contraseña de administrador incorrecta.";
        }
    }

    // =========================
    // LOGIN DE PARTICIPANTE
    // =========================
    if ($tipo === "participante") {

        $email = $_POST['email'] ?? "";
        $password = $_POST['password_participante'] ?? "";

        // Buscar usuario por email
        $stmt = $conn->prepare("SELECT * FROM participantes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $usuario = $result->fetch_assoc();

            if (password_verify($password, $usuario['password'])) {

                $_SESSION['participante_id'] = $usuario['id'];
                header("Location: panel.php");
                exit;
            } else {
                $mensaje = "Contraseña incorrecta.";
            }
        } else {
            $mensaje = "No existe un usuario con ese correo.";
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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="container">

        <h2>Amigo Invisible</h2>

        <form method="POST">

            <label>¿Quién eres?</label>
            <select name="tipo" required onchange="cambiarTipo()">
                <option value="participante">Participante</option>
                <option value="admin">Administrador</option>
            </select>

            <!-- Campos para participantes -->
            <div id="participante-fields">
                <label>Email:</label>
                <input type="email" name="email" placeholder="tu@email.com">

                <label>Contraseña:</label>
                <input type="password" name="password_participante" placeholder="Tu contraseña">
            </div>

            <!-- Campo para administrador -->
            <div id="admin-fields">
                <label>Contraseña Admin:</label>
                <input type="password" name="password_admin" placeholder="Contraseña de administrador">
            </div>

            <button type="submit">Entrar</button>

        </form>

        <?php if ($mensaje) echo "<p class='mensaje error'>$mensaje</p>"; ?>

        <div class="registro-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>

    </div>

    <script>
        function cambiarTipo() {
            const tipo = document.querySelector("[name='tipo']").value;

            document.getElementById("participante-fields").style.display =
                (tipo === "participante") ? "block" : "none";

            document.getElementById("admin-fields").style.display =
                (tipo === "admin") ? "block" : "none";
        }

        // Inicializar al cargar
        cambiarTipo();
    </script>

</body>

</html>