<?php
session_start();
include('config.php'); // Conexión a la base de datos
include('clase/usuarios.php'); // Incluir la clase Usuario


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Crear una instancia de la clase Usuario
    $usuarioObj = new Usuario($conn);

    // Verificar las credenciales
    $usuarioData = $usuarioObj->verificarCredenciales($usuario, $contraseña);

    if ($usuarioData) {
        // Si las credenciales son correctas, almacenar los datos en la sesión
        $_SESSION['usuario_id'] = $usuarioData['id'];
        $_SESSION['usuario'] = $usuarioData['usuario'];
        $_SESSION['rol'] = $usuarioData['rol'];

        // Redirigir según el rol del usuario
        switch ($_SESSION['rol']) {
            case 'admin':
                header('Location: usuarios.php');
                break;
            case 'empleado':
                header('Location: menuVentas.php');
                break;
            case 'usuario':
                header('Location: menuVentas.php');
                break;
            default:
                header('Location: login.php');
                break;
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #a0d8ff; /* Fondo celeste */
        }
        .login-container {
            width: 100%;
            max-width: 600px; /* Ancho máximo del formulario */
            padding: 30px;
            background-color: #000; /* Fondo negro del formulario */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            text-align: center;
            border: 2px solid #6a0dad; /* Borde violeta */
        }
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        h2 {
            color: #fff; /* Texto en blanco */
            font-size: 24px;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #6a0dad; /* Violeta */
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #580a80; /* Violeta más oscuro */
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #6a0dad; /* Violeta en el focus */
        }

        /* Estilo responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 20px;
            }
            .avatar {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="img/LOGO.png" alt="Avatar" class="avatar">
        <h2>Producto oceanico</h2>
        <form action="login.php" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <button type="submit">Iniciar sesión</button>
        </form>
        <?php
        // Muestra el mensaje de error si existe
        if (isset($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        ?>
    </div>

</body>
</html>
