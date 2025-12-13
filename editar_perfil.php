<?php
session_start();

if (!isset($_SESSION['participante_id'])) {
    die("Error: No hay sesión activa.");
}

$id = $_SESSION['participante_id'];

include('conexion.php');

// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM participantes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No existe un participante con ese ID.");
}

$usuario = $result->fetch_assoc();

// Decodificar hobbies si existen
$hobbies = [];
if (!empty($usuario['hobbies'])) {
    $hobbies = json_decode($usuario['hobbies'], true);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <div class="form-container">

        <h2>Editar Perfil</h2>

        <?php if ($usuario['foto']) { ?>
            <img src="fotos/<?php echo $usuario['foto']; ?>" class="perfil-foto">
        <?php } ?>

        <form action="editar_perfil_proceso.php" method="POST" enctype="multipart/form-data">

            <!-- Enviar ID oculto -->
            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $usuario['email']; ?>" required>

            <label>Sexo:</label>
            <select name="gender" required>
                <option value="masculino" <?php echo $usuario['gender'] == "masculino" ? "selected" : ""; ?>>Masculino
                </option>
                <option value="femenino" <?php echo $usuario['gender'] == "femenino" ? "selected" : ""; ?>>Femenino
                </option>
            </select>

            <label>Cambiar foto (opcional):</label>
            <input type="file" name="foto" accept="image/*">

            <!-- SECCIÓN DE HOBBIES -->
            <div class="hobbies-container">
                <label><strong>Tus Hobbies/Gustos (máximo 3):</strong></label>

                <div id="hobbies-list">
                    <?php foreach ($hobbies as $hobby): ?>
                    <div class="hobby-input">
                        <input type="text" name="hobby[]" value="<?php echo $hobby; ?>" placeholder="Ej: Videojuegos">
                        <button type="button" class="btn-remove"
                            onclick="this.parentElement.remove(); updateButtonState();">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="btn-add-hobby" id="btn-add-hobby">+ Añadir otro gusto</button>
            </div>
            <br>

            <button type="submit">Guardar Cambios</button>

        </form>

        <a href="panel.php">Volver al perfil</a>

    </div>

    <script>
        const MAX_HOBBIES = 3;

        document.getElementById("btn-add-hobby").addEventListener("click", function () {
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