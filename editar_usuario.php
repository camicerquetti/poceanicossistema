<?php
ob_start();  // Comienza el almacenamiento en búfer
include('config.php');
include('clase/usuarios.php');

// Crear una instancia de la clase Usuario
$usuario = new Usuario($conn);

// Verificar si se ha pasado un 'id' en la URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los datos del usuario específico por ID
    $datosUsuario = $usuario->obtenerUsuarioPorId($id);

    if (!$datosUsuario) {
        die("<div class='alert alert-danger'>Usuario no encontrado.</div>");
    }
} else {
    die("<div class='alert alert-danger'>No se ha especificado el ID del usuario.</div>");
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioActualizado = new Usuario($conn);
    $usuarioActualizado->id = $id;
    $usuarioActualizado->usuario = $_POST['usuario'];
    $usuarioActualizado->nombre = $_POST['nombre'];
    $usuarioActualizado->apellido = $_POST['apellido'];
    $usuarioActualizado->email = $_POST['email'];
    $usuarioActualizado->cargo = $_POST['cargo'];
    $usuarioActualizado->rol = $_POST['rol'];

    // Si el campo contraseña está vacío, mantener la actual
    if (!empty($_POST['contraseña'])) {
        $usuarioActualizado->contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    } else {
        $usuarioActualizado->contraseña = $datosUsuario['contraseña']; // Mantener la contraseña actual
    }

    // Intentar actualizar los datos del usuario
    if ($usuarioActualizado->actualizarDatos()) {
        header("Location: usuarios.php?status=updated");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error al actualizar el usuario.</div>";
    }
}
?>
<style>
    /* Ajuste para que el contenido quede fuera del encabezado */
    body {
        padding-top: 190px; /* Agrega espacio debajo del header (ajusta según el tamaño del header) */
    }

    .container {
        margin-left: 140px; /* Ajusta el margen a la izquierda según lo necesites */
    }

    /* Si deseas un margen solo en el formulario, puedes hacerlo así */
    form {
        height:40%;
        margin-left: 220px; /* Asegúrate de ajustar esto según sea necesario */
    }

    /* Opcional: Si deseas un mayor margen a nivel de los campos de formulario */
    .form-control {
        margin-left:5px; /* Ajusta según lo necesites */
    }
    .container.mt-4 {
        width: 1200px;
        margin-left: 120px;
        padding: 80px;

        }

    </style>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<header>
    <?php include('headeradmin.php'); ?>
</header>
<body>
<div class="container">
    <h2 class="text-center">Editar Usuario</h2>
    <form action="editar_usuario.php?id=<?php echo $id; ?>" method="POST">
        <div class="row">
            <!-- Primera columna -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" value="<?php echo $datosUsuario['usuario']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo $datosUsuario['nombre']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?php echo $datosUsuario['apellido']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $datosUsuario['email']; ?>" required>
                </div>
            </div>

            <!-- Segunda columna -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control" value="<?php echo $datosUsuario['cargo']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-control" required>
                        <option value="admin" <?php echo ($datosUsuario['rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="empleado" <?php echo ($datosUsuario['rol'] == 'empleado') ? 'selected' : ''; ?>>Empleado</option>
                        <option value="usuario" <?php echo ($datosUsuario['rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña (opcional)</label>
                    <input type="password" name="contraseña" class="form-control">
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
