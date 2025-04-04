<?php
ob_start();
// Incluir el header admin (si tienes un header común)
include('headeradmin.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Agregar Font Awesome -->
    <style>
        body {
            margin-left: 0px; /* Evitar que el contenido se desplace hacia la derecha si hay barra lateral */
        }

        .container {
            margin-top: -490px;
            margin-left: 270px; /* Para dar espacio si tienes una barra lateral */
            width: 1200px; /* Resta el tamaño de la barra lateral */
           

        }

        .menu-icons {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 columnas */
            gap: 20px;
            margin-top: 30px;
        }

        .menu-icons .card {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .menu-icons .card:hover {
            transform: scale(1.05);
        }

        .menu-icons .card i {
            font-size: 40px;
            color: #007b8c; /* Celeste oscuro */
        }

        .menu-icons .card h5 {
            margin-top: 10px;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <main>
        <div class="container">
            <h2>Informes</h2>

            <!-- Sección de íconos estilo menú -->
            <div class="menu-icons">
                <!-- Ranking -->
                <div class="card" onclick="window.location.href='ranking.php'">
                    <i class="fas fa-trophy"></i>
                    <h5>Ranking</h5>
                </div>

                <!-- Reporte Final -->
                <div class="card" onclick="window.location.href='reporte_final.php'">
                    <i class="fas fa-file-alt"></i>
                    <h5>Reporte Final</h5>
                </div>

                <!-- Stock -->
                <div class="card" onclick="window.location.href='stock.php'">
                    <i class="fas fa-cogs"></i>
                    <h5>Stock</h5>
                </div>

                <!-- Cuenta Corriente Clientes -->
                <div class="card" onclick="window.location.href='cuenta_corriente_clientes.php'">
                    <i class="fas fa-users"></i>
                    <h5>Cuenta Corriente Clientes</h5>
                </div>

                <!-- Cuenta Corriente Proveedores -->
                <div class="card" onclick="window.location.href='cuenta_corriente_proveedores.php'">
                    <i class="fas fa-truck"></i>
                    <h5>Cuenta Corriente Proveedores</h5>
                </div>

                <!-- Información de Contabilidad -->
               
            </div>
        </div>
    </main>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
