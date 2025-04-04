<?php
ob_start();
include('config.php');
include('headeradmin.php'); // Incluye el archivo de configuración que contiene la conexión a la base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $usuario = $_POST['usuario'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $cargo = $_POST['cargo'];
    $rol = $_POST['rol'];
    $direccion = $_POST['direccion'];
    $contraseña = $_POST['contraseña'];

    // Crear una instancia de la clase Usuario
    $nuevo_usuario = new Usuario($conn);
    
    // Asignar los valores de los campos del formulario a las propiedades de la clase
    $nuevo_usuario->usuario = $usuario;
    $nuevo_usuario->nombre = $nombre;
    $nuevo_usuario->apellido = $apellido;
    $nuevo_usuario->email = $email;
    $nuevo_usuario->cargo = $cargo;
    $nuevo_usuario->rol = $rol;
    $nuevo_usuario->direccion = $direccion;
    $nuevo_usuario->contraseña = $contraseña;

    // Intentar agregar el usuario a la base de datos
    if ($nuevo_usuario->agregarUsuario()) {
        // Redirigir a la página de usuarios después de agregar
        header("Location: usuarios.php");
        exit();
    } else {
        // Si hubo un error al agregar el usuario
        $error = "Error al agregar el usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Ajuste del contenido */
        .content {
            width:850px;
            margin-left: 380px; /* Ajusta la distancia desde la barra lateral */
            margin-top: -616px; /* Ajusta para que no se sobreponga con el top-bar */
            padding: 5px;
         height:100%;
         
       
        }
        /* Formulario */

        .form-container {
            width: 800px;
        
     font-size:10px;
            padding: 2px; /* Reduce el padding para que el formulario ocupe menos espacio */
        }

        /* Reducir el espacio entre los elementos del formulario */
        .form-container .mb-3 {
            margin-bottom: 0px; /* Ajuste para reducir la separación entre los campos */
        }

        /* Ajuste para los inputs y selects */
        .form-container .form-control {
            height: 32px; /* Reducir la altura de los campos de entrada */
        }

        .form-container button {
            width: 20%; /* Hace que el botón ocupe todo el ancho del formulario */
            margin-top: 10px; /* Separación superior del botón */
        }

    </style>
</head>
<body>
   
<!-- Contenido principal -->
<div class="content">
    <h2>Agregar Nuevo Usuario</h2>

    <!-- Mostrar el mensaje de error si existe -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="agregar_usuario.php" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="cargo" class="form-label">Cargo</label>
                <input type="text" class="form-control" id="cargo" name="cargo" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select class="form-control" id="rol" name="rol" required>
                    <option value="admin">Admin</option>
                    <option value="empleado">Empleado</option>
                    <option value="usuario">Usuario</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Usuario</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
