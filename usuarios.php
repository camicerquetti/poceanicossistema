<?php
ob_start();  // Comienza el almacenamiento en búfer
include('headeradmin.php'); // Incluye la conexión a la base de datos
include('config.php');

// Crear instancia de la clase Usuario
$usuario = new Usuario($conn);

// Obtener todos los usuarios
$usuarios = $usuario->obtenerTodosUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin-top: 0;
            font-family: Arial, sans-serif;
        }
        
        .top-bar .btn {
        
            color: white;
        }
        /* Contenido principal */
        .content {
            margin-top: -220px; /* Reduce el espacio después de la barra de navegación fija */
            padding: 6px;
           
        }
        .table-container {
            margin-top: 20px; /* Reduce el espacio entre el título y la tabla */
            margin-left:290px;
            width: 1000px;
            
        }
        h2 {
            margin-bottom: 10px; /* Reduce el espacio entre el título y la tabla */
        }
        .btn-success {
            margin-bottom: 4px; /* Espacio entre el botón y la tabla */
        }
        .container{
            margin-left:160px;
            width: 480px;
        }
        </style>
       
</head>
<body>
  
<div class="content">
    <div class="container">
        <!-- Usamos 'd-flex' para hacer que los elementos estén en una fila -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Título -->
            <h2 class="mb-0">Gestión de Usuarios</h2>
            <!-- Botón para agregar nuevos usuarios -->
            <a href="agregar_usuario.php" class="btn btn-success">Nuevo Usuario</a>
        </div>
    </div>
</div>

            <!-- Tabla de usuarios -->
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>usuario</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Cargo</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($usuarios) > 0) {
                            // Mostrar cada usuario
                            foreach ($usuarios as $row) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                          <td>{$row['usuario']}</td>
                                        <td>{$row['nombre']}</td>
                                        <td>{$row['apellido']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['cargo']}</td>
                                        <td>{$row['rol']}</td>
                                        <td>{$row['fecha_registro']}</td>
                                        <td>
                                            <a href='editar_usuario.php?id={$row['id']}' class='btn btn-warning btn-sm'>Editar</a>
                                            <a href='usuarios.php?eliminar={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>Eliminar</a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No hay usuarios registrados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
