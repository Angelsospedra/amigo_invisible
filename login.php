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
?>

<!DOCTYPE html>
<html lang="es">

<body>

    <div class="container">

        <h2>Iniciar Sesión</h2>

        <form method="POST">

            <label>¿Quién eres?</label><br>
            <select name="tipo" required>
                <option value="participante">Participante</option>
                <option value="admin">Administrador</option>
            </select><br><br>

            <!-- Campos para participantes -->
            <div id="participante-fields">
                <label>Email:</label>
                <input type="email" name="email"><br><br>

                <label>Contraseña:</label>
                <input type="password" name="password_participante"><br><br>
            </div>

            <!-- Campo para administrador -->
            <div id="admin-fields" style="display:none;">
                <label>Contraseña Admin:</label>
                <input type="password" name="password_admin"><br><br>
            </div>

            <button type="submit">Entrar</button>

        </form>

        <?php if ($mensaje) echo "<p style='color:red;'>$mensaje</p>"; ?>

    </div>

    <script>
        // Cambiar campos según elección
        document.querySelector("[name='tipo']").addEventListener("change", function() {
            let tipo = this.value;

            document.getElementById("participante-fields").style.display =
                (tipo === "participante") ? "block" : "none";

            document.getElementById("admin-fields").style.display =
                (tipo === "admin") ? "block" : "none";
        });
    </script>

</body>

</html>