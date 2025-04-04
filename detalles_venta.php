<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Ingreso y la conexión
include('config.php');
include('clase/Ingreso.php');

// Verificar si se ha pasado un ID de venta por URL
if (isset($_GET['id'])) {
    $venta_id = (int)$_GET['id']; // ID de la venta a consultar

    // Crear una instancia de la clase Ingreso
    $ingreso = new Ingreso($conn);

    // Obtener los detalles de la venta específica
    $venta = $ingreso->obtenerIngresoPorId($venta_id);

    // Verificar si la venta existe
    if (!$venta) {
        die("Venta no encontrada.");
    }
} else {
    die("ID de venta no especificado.");
}
?>
<style>
        .pagination {
            width: 100%;
            justify-content: center;  /* Centra la paginación */
        }
        .container.mt-4 {
            width: 1200px;  
            margin-left: 220px;
            padding: 70px;
        }
     
        
    </style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Detalles de Venta</h2>

        <!-- Botón de regresar -->
        <a href="ventas.php" class="btn btn-secondary mb-3">Volver a la lista de ventas</a>

        <!-- Mostrar los detalles de la venta -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Venta ID: <?php echo $venta['id']; ?></h5>
                <p><strong>Fecha:</strong> <?php echo $venta['fecha']; ?></p>
                <p><strong>Cliente:</strong> <?php echo $venta['cliente']; ?></p>
                <p><strong>Descripción:</strong> <?php echo $venta['descripcion']; ?></p>
                <p><strong>Factura AFIP:</strong> <?php echo $venta['factura_afip']; ?></p>
                <p><strong>Estado:</strong> <span class="estado-<?php echo $venta['estado']; ?>"><?php echo ucfirst($venta['estado']); ?></span></p>
                <p><strong>Subtotal:</strong> $<?php echo number_format($venta['subtotal'], 2); ?></p>
                <p><strong>IVA:</strong> $<?php echo number_format($venta['iva'], 2); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($venta['total'], 2); ?></p>
                
                <h5>Productos:</h5>
                <ul>
               
                </ul>

                <h5>Detalles de Pago:</h5>
                <p><strong>Metodo de Pago:</strong> <?php echo $venta['metodo_pago']; ?></p>
                <p><strong>Metodo de Transporte:</strong> <?php echo $venta['metodo_transporte']; ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
