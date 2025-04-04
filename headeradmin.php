<?php
ob_start(); // Asegúrate de que esto esté al principio del archivo

// Verifica si la sesión ya está activa antes de llamar a session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Solo inicia sesión si no hay una sesión activa
}
 // Asegúrate de que esto esté al principio de todo el archivo, antes de cualquier HTML

include('config.php'); // Incluye el archivo de configuración que contiene la conexión a la base de datos
include_once('clase/usuarios.php'); // Incluye la clase Usuario

// Verifica si el usuario está autenticado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    // Redirige al login si no está autenticado o no es admin
    header('Location: login.php');
    exit();
}

// Crea un objeto Usuario para obtener los datos del usuario
$usuario = new Usuario($conn);
$usuario->id = $_SESSION['usuario_id']; // Asigna el ID del usuario de la sesión
$usuario->obtenerDatos(); // Llama a un método para obtener los datos del usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Admin</title>
    <!-- Agrega el enlace al CSS de Bootstrap desde un CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJY4-OKJ2K2pPjcF5G7vNZZQxMJnLUFSkfK4O9F9thTbt1N00xwMxlVWjTe1" crossorigin="anonymous">
    <link rel="stylesheet" href="estilos.css"> <!-- Archivo CSS para estilos adicionales -->
    <style>
        /* Estilo del sidebar */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: black;
            padding-top: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar .navbar-nav {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .sidebar .nav-item {
            width: 100%;
        }

        .sidebar .nav-link {
            color: white;
            font-size: 16px;
            padding: 12px 20px;
        }

        .sidebar .nav-link:hover {
            background-color: #0066cc;
            color: white;
        }

        .sidebar .navbar-brand {
            text-align: center;
            padding: 10px 0;
        }

        .sidebar .navbar-brand img {
            max-width: 100%;
            height: auto;
        }

        .sidebar .user-info {
            padding: 20px;
            text-align: center;
            color: white;
        }

        .sidebar .btn-logout {
            background-color: #0066cc;
            color: white;
        }

        .sidebar .btn-logout:hover {
            background-color: #004a99;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Barra superior */
        .top-bar {
            height: 50px;
            background-color: black; /* Barra horizontal negra */
            color: white;
            padding: 10px;
            position: fixed;
            top: 0;
            left: 250px; /* Ajustar para que no se superponga al sidebar */
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .top-bar .user-info {
            display: flex;
            align-items: center;
        }

        .top-bar .user-info span {
            margin-right: 15px;
        }

        /* Barra de navegación responsive */
        @media (max-width: 768px) {
            .top-bar {
                left: 0;
                right: 0;
                padding-left: 10px;
                padding-right: 10px;
            }

            .sidebar {
                transform: translateX(-250px);
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .navbar-toggler {
                display: block;
                border: none;
                background: transparent;
                color: white;
                font-size: 30px;
            }

            .sidebar .navbar-nav {
                display: none;
            }

            .top-bar .user-info {
                display: flex;
                justify-content: space-between;
                width: 100%;
            }

            .top-bar .user-info span,
            .top-bar .user-info a {
                flex: 1;
                text-align: right;
            }
        }

        /* Estilo del botón de logout */
        .btn-logout {
            background-color: #0066cc;
            color: white;
        }

        .btn-logout:hover {
            background-color: #004a99;
        }
    </style>
</head>
<body>
  <!-- Barra superior con nombre de usuario y botón de cerrar sesión -->
<div class="top-bar">
    <!-- Contenido de la izquierda, por ejemplo, el título o logo -->
    <div class="left-content">
        <!-- Aquí puedes agregar el logo o el título, si es necesario -->
        <span>Producto Oceanico</span>
    </div>

    <!-- Información del usuario y cerrar sesión alineados a la derecha -->
    <div class="user-info">
        <span><?php echo $usuario->nombre . ' ' . $usuario->apellido; ?></span>
        <a href="login.php" class="btn btn-outline-light">Cerrar sesión</a>
    </div>
</div>



    <!-- Sidebar (Menú vertical estático) -->
    <div class="sidebar">
        <!-- Logo Producto Oceanico -->
        <a class="navbar-brand" href="#">
            <img src="img/LOGO.PNG" alt="Producto Oceanico Logo">
        </a>

        <!-- Menú de navegación -->
        <ul class="navbar-nav">
    
            <li class="nav-item dropdown">
            <a class="nav-link " href="Menu.php" id="" role="button" data-bs-toggle="" aria-expanded="false">
        Menu
    </a>
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        MenuX
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="ingresosx.php">IngresosX</a></li>
        <li><a class="dropdown-item" href="comprasx.php">ComprasX</a></li>
        <li><a class="dropdown-item" href="gastosx.php">GastosX</a></li>
        <li class="nav-item dropdown">
</li>
    </ul>
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        TesoreríaX
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="saldosX.php">SaldosX</a></li>
        <li><a class="dropdown-item" href="movimientosX.php">MovimientosX</a></li>
    </ul>
</li>
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownIngresos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Ingresos
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdownIngresos">
        <li><a class="dropdown-item" href="otros_ingresos.php">Otros Ingresos</a></li>
        <li><a class="dropdown-item" href="ventas.php">Ventas</a></li>
        <li><a class="dropdown-item" href="ingresos.php">Ingresos</a></li>
    </ul>
</li>

            <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownEgresos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Egresos
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdownEgresos">
        <li><a class="dropdown-item" href="compras.php">Compras</a></li>
        <li><a class="dropdown-item" href="gastos.php">Gastos</a></li>
    </ul>
</li>

            <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Base de Datos
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="productos.php">Productos</a></li>
        <li><a class="dropdown-item" href="proveedores.php">Proveedores</a></li>
        <li><a class="dropdown-item" href="clientes.php">Clientes</a></li>
    </ul>
</li>

            <li class="nav-item">
                <a class="nav-link" href="informes.php">Informes</a>
            </li>
            <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Tesorería
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
        <li><a class="dropdown-item" href="saldos.php">Saldos</a></li>
        <li><a class="dropdown-item" href="movimientos.php">Movimientos</a></li>
    </ul>
</li>

            <li class="nav-item">
                <a class="nav-link" href="usuarios.php">Usuarios</a>
            </li>
        </ul>
       


    <!-- Contenido principal de la página -->
    <div class="content">
        <!-- Aquí va el contenido principal de la página -->
    </div>

    <!-- Scripts de Bootstrap (para la funcionalidad del navbar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-KyZXEJY4-OKJ2K2pPjcF5G7vNZZQxMJnLUFSkfK4O9F9thTbt1N00xwMxlVWjTe1" crossorigin="anonymous"></script>

    <!-- Script para el funcionamiento del menú hamburguesa en pantallas pequeñas -->
    <script>
        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
