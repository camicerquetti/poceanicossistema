<?php 
ob_start();  // Comienza el almacenamiento en búfer
// Incluir las dependencias necesarias
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Verificar que se haya pasado un ID válido
if (isset($_GET['id'])) {
    $ingreso_id = (int)$_GET['id'];
    $detalle_ingreso = $ingreso->obtenerIngresoPorId($ingreso_id);
    if (!$detalle_ingreso) {
        die("Ingreso no encontrado.");
    }
} else {
    die("No se ha proporcionado un ID de ingreso.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Producto Oceanico SRL</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #000;
        }

        /* Contenedor para las dos columnas */
        .columns-container {
            display: flex; /* Usamos flexbox para distribuir las columnas */
            justify-content: space-between; /* Espacio entre las columnas */
            align-items: flex-start;
            padding: 10px;
            gap: 10px; /* Espacio entre las columnas */
            width: 100%; /* Aseguramos que ocupe todo el ancho */
            border: 2px solid #000;
        }

        /* Estilo para la columna izquierda (Encabezado de la empresa) */
        .header {
            width: 48%; /* Ajuste el tamaño para que sea más pequeño que el 50% */
            font-size: 14px;
            margin-left: 65px;
        }

        .header img {
            width: 100px; /* El logo tiene un ancho fijo */
        }

        .empresa-info {
            font-size: 14px;
            margin-top: 10px;
        }

        /* Estilo para la columna central (Tipo de factura centrado) */
        .factura-titulo {
            width: 10%; /* Ancho completo */
            border: 2px solid #000;
            text-align: center; /* Centrado del tipo de factura */
            margin-right: 60px;
        }

        /* Estilo para la columna derecha (Detalles de la factura) */
        .detalle-factura {
            width: 48%; /* Ajuste para que tenga el mismo ancho que el header */
            font-size: 14px;
            margin-right: 25px;
        }

        /* Otras definiciones */
        .factura-detalle {
            width: 100%;
            border: 2px solid #000;
            padding: 10px;
            margin-top: 20px;
        }

        h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 15px;
        }

        .factura-detalle p {
            margin: 5px 0;
            font-size: 16px;
        }

        .total-container {
            margin-left:500px;
            display: flex;
            justify-content: center;
            margin-top: 20px;
            width: 30%;
            border: 2px solid #000;
          
        }

        .total {
            font-weight: bold;
            font-size: 13px;  /* Hacemos el tamaño de la fuente más pequeño */
            margin-top: 10px;
            margin-left: 10px;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        .btn-back {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .button-container button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button-container button:hover {
            background-color: #218838;
        }
         /* Estilo para la impresión */
    @media print {
        /* Ocultar los botones durante la impresión */
        .button-container, .btn-back {
            display: none;  /* Oculta los botones */
        }

    </style>
</head>
<body>

<div class="container">
    
    <!-- Contenedor para dos columnas -->
    <div class="columns-container">
        <!-- Columna izquierda: Encabezado de la empresa -->
        <div class="header">
            <img src="img/LOGO.png" alt="Logo"><br>
            <div class="empresa-info">
                <p>Producto Oceanico SRL</p>
                <p>Santa Magdalena 309, CABA 1277 - Buenos Aires</p>
                <p>Tel: 7546-2063 | productooceanicopatricio@gmail.com</p>
                <p>www.productooceanico.com.ar</p>
            </div>
        </div>

        <!-- Columna central: Tipo de factura centrado -->
        <div class="factura-titulo">
            <h2><span style="font-size: 30px; font-weight: bold;"> 
                <?php echo isset($detalle_ingreso['tipo_factura']) ? $detalle_ingreso['tipo_factura'] : 'No disponible'; ?>
            </span></h2>
        </div>
        
        <!-- Columna derecha: Detalles de la factura -->
        <div class="detalle-factura">
            <p><strong>Fecha de Emisión:</strong> <?php echo isset($detalle_ingreso['fecha']) ? $detalle_ingreso['fecha'] : 'No disponible'; ?></p>
            <p><strong>Factura AFIP:</strong> <?php echo isset($detalle_ingreso['factura_afip']) ? $detalle_ingreso['factura_afip'] : 'No disponible'; ?></p>
            <p><strong>Estado:</strong> <?php echo isset($detalle_ingreso['estado']) ? $detalle_ingreso['estado'] : 'No disponible'; ?></p>
            <p><strong>Razón Social:</strong> <?php echo isset($detalle_ingreso['razon_social']) ? $detalle_ingreso['razon_social'] : 'No disponible'; ?></p>
            <p><strong>CUIT:</strong> <?php echo isset($detalle_ingreso['cuit']) ? $detalle_ingreso['cuit'] : 'No disponible'; ?></p>
            <p><strong>Condición IVA:</strong> <?php echo isset($detalle_ingreso['condicion_iva']) ? $detalle_ingreso['condicion_iva'] : 'No disponible'; ?></p>
            <p><strong>Vendedor:</strong> <?php echo isset($detalle_ingreso['empleado_responsable']) ? $detalle_ingreso['empleado_responsable'] : 'No disponible'; ?></p>
        </div>
    </div>

    <!-- Tabla de productos -->
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>IVA</th>
                <th>Subtotal</th>
                <th>Peso (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle_ingreso['productos'] as $producto): ?>
            <tr>
                <td><?php echo isset($producto['Codigo']) ? $producto['Codigo'] : 'No disponible'; ?></td>
                <td><?php echo isset($producto['Nombre']) ? $producto['Nombre'] : 'No disponible'; ?></td>
                <td><?php echo isset($producto['cantidad']) ? $producto['cantidad'] : 'No disponible'; ?></td>
                <td>$<?php echo isset($producto['precio']) ? number_format($producto['precio'], 2, ',', '.') : 'No disponible'; ?></td>
                <td><?php echo isset($producto['iva']) ? $producto['iva'] : '0'; ?>%</td>
                <td>$<?php echo isset($producto['subtotal']) ? number_format($producto['subtotal'], 2, ',', '.') : 'No disponible'; ?></td>
                <td><?php echo isset($producto['peso_kg']) ? number_format($producto['peso_kg'], 2, ',', '.') : 'No disponible'; ?> kg</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Contenedor para los totales en una columna derecha -->
    <div class="total-container">
        <div class="total">
            <p><strong>Total Venta:</strong> $<?php echo isset($detalle_ingreso['total_venta']) ? number_format($detalle_ingreso['total_venta'], 2, ',', '.') : '0,00'; ?></p>
            <p><strong>Impuesto (IVA):</strong> <?php echo isset($detalle_ingreso['iva_total']) ? $detalle_ingreso['iva_total'] : '0'; ?>%</p>
            <p><strong>Total a Cobrar:</strong> $<?php echo isset($detalle_ingreso['total_cobrar']) ? number_format($detalle_ingreso['total_cobrar'], 2, ',', '.') : '0,00'; ?></p>
            <p><strong>Total Cobrado:</strong> $<?php echo isset($detalle_ingreso['total_cobrado']) ? number_format($detalle_ingreso['total_cobrado'], 2, ',', '.') : '0,00'; ?></p>
        </div>
    </div>
 <!-- Botones -->
 <div class="button-container d-flex">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>
    <br><br>
    <a href="ingresos.php" class="btn-back">Volver a la aplicación</a>
</div>
</body>

</html>

