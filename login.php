<?php
session_start();
include("conexion.php");

$mensaje = "";

// Variable para persistir el email si hay error (UX)
$email_ingresado = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = $_POST['tipo'];

    // =========================
    // LOGIN DE ADMINISTRADOR
    // =========================
    if ($tipo === "admin") {

        $admin_pass = "1234"; // Cambiar por tu contraseña real

        if (isset($_POST['password_admin']) && $_POST['password_admin'] === $admin_pass) {
            $_SESSION['admin'] = true;
            header("Location: panel_admin.php");
            exit;
        } else {
            // Mensaje genérico para seguridad
            $mensaje = "Usuario o contraseña incorrectas.";
        }
    }

    // =========================
    // LOGIN DE PARTICIPANTE
    // =========================
    if ($tipo === "participante") {

        $email = $_POST['email'] ?? "";
        $password = $_POST['password_participante'] ?? "";
        
        // Guardamos el email para volver a mostrarlo en el input
        $email_ingresado = $email;

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
                // Contraseña mal
                $mensaje = "Usuario o contraseña incorrectas.";
            }
        } else {
            // Usuario no existe (Mismo mensaje por seguridad)
            $mensaje = "Usuario o contraseña incorrectas.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Amigo Invisible</title>
    <link rel="stylesheet" href="estilos.css">

</head>

<body>

    <!-- Modal de Error -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">🎅 ¡Ho Ho Ho! 🎅</div>
            <div class="modal-body">
                🎄 Parece que Papá Noel no encuentra tu nombre en la lista... 🎁
                <br><br>
                <strong>Usuario o contraseña incorrectas</strong>
                <br><br>
                ❄️ ¡Inténtalo de nuevo! ⛄
            </div>
            <button class="close-modal" onclick="cerrarModal()">🎄 Entendido 🎄</button>
        </div>
    </div>

    <div class="container">

        <h2>Amigo Invisible 🎄</h2>

        <form method="POST">

            <label>¿Quién eres?</label>
            <select name="tipo" required onchange="cambiarTipo()">
                <option value="participante" <?php if (isset($_POST['tipo']) && $_POST['tipo'] == 'participante') echo 'selected'; ?>>Participante</option>
                <option value="admin" <?php if (isset($_POST['tipo']) && $_POST['tipo'] == 'admin') echo 'selected'; ?>>Administrador</option>
            </select>

            <div id="participante-fields">
                <label>Email:</label>
                <input type="email" name="email" placeholder="tu@email.com" value="<?php echo htmlspecialchars($email_ingresado); ?>">

                <label>Contraseña:</label>
                <input type="password" name="password_participante" placeholder="Tu contraseña">
            </div>

            <div id="admin-fields" style="display:none;">
                <label>Contraseña Admin:</label>
                <input type="password" name="password_admin" placeholder="Contraseña de administrador">
            </div>

            <button type="submit">Entrar</button>

        </form>

        <div class="registro-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>

    </div>

    <script>
        function cambiarTipo() {
            const select = document.querySelector("[name='tipo']");
            const tipo = select.value;
            document.getElementById("participante-fields").style.display = (tipo === "participante") ? "block" : "none";
            document.getElementById("admin-fields").style.display = (tipo === "admin") ? "block" : "none";
        }

        function cerrarModal() {
            document.getElementById("errorModal").style.display = "none";
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById("errorModal");
            if (event.target == modal) {
                cerrarModal();
            }
        }

        // Mostrar modal si hay mensaje de error
       // Mostrar modal si hay mensaje de error
        const mensajeError = "<?php echo $mensaje; ?>";
        if (mensajeError) {
            // CAMBIO IMPORTANTE: Usamos 'flex' en lugar de 'block'
            // Esto respeta el align-items y justify-content del CSS
            document.getElementById("errorModal").style.display = "flex";
        }

        window.onload = cambiarTipo;
    </script>

</body>

</html>