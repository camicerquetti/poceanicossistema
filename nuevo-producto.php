<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir el header admin y la conexión a la base de datos

include('config.php');

// Si se envía el formulario, insertar el producto en la base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $tipo_producto_servicio = $_POST['tipo_producto_servicio'];
    $proveedor = $_POST['proveedor'];
    $codigo = $_POST['codigo'];
    $deposito_1 = $_POST['deposito_1'];
    $general = $_POST['general'];
    $stock_total = $_POST['stock_total'];
    $costo = $_POST['costo'];
    $iva_compras = $_POST['iva_compras'];
    $precio_de_venta = $_POST['precio_de_venta'];
    $iva_ventas = $_POST['iva_ventas'];
    $descripcion = $_POST['descripcion'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $mostrar_en_ventas = isset($_POST['mostrar_en_ventas']) ? 1 : 0;
    $mostrar_en_compras = isset($_POST['mostrar_en_compras']) ? 1 : 0;
    $imagen = $_POST['imagen'];

    // Consulta para insertar el nuevo producto
    $query = "INSERT INTO producto (Nombre, Tipo, Tipo_Producto_Servicio, Proveedor, Codigo, Deposito_1, General, Stock_Total, Costo, IVA_Compras, Precio_de_Venta, IVA_Ventas, Descripcion, Activo, Mostrar_en_Ventas, Mostrar_en_Compras, imagen)
              VALUES ('$nombre', '$tipo', '$tipo_producto_servicio', '$proveedor', '$codigo', '$deposito_1', '$general', '$stock_total', '$costo', '$iva_compras', '$precio_de_venta', '$iva_ventas', '$descripcion', '$activo', '$mostrar_en_ventas', '$mostrar_en_compras', '$imagen')";

    if ($conn->query($query) === TRUE) {
        $mensaje = "Producto agregado exitosamente.";
    } else {
        $mensaje = "Error al agregar el producto: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin-left: 300px;
            margin-right: auto;
            padding: 40px;
        }

        body {
            padding-top: 60px;
        }

        .form-container {
            margin-top: 40px;
        }

        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-green:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container">
        <h2>Agregar Nuevo Producto</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar un nuevo producto -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <input type="text" id="tipo" name="tipo" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="tipo_producto_servicio" class="form-label">Tipo Producto/Servicio</label>
                    <input type="text" id="tipo_producto_servicio" name="tipo_producto_servicio" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <input type="text" id="proveedor" name="proveedor" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="codigo" class="form-label">Código</label>
                    <input type="text" id="codigo" name="codigo" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="deposito_1" class="form-label">Depósito 1</label>
                    <input type="number" id="deposito_1" name="deposito_1" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="general" class="form-label">General</label>
                    <input type="number" id="general" name="general" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="stock_total" class="form-label">Stock Total</label>
                    <input type="number" id="stock_total" name="stock_total" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="costo" class="form-label">Costo</label>
                    <input type="number" id="costo" name="costo" class="form-control" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="iva_compras" class="form-label">IVA Compras</label>
                    <input type="number" id="iva_compras" name="iva_compras" class="form-control" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="precio_de_venta" class="form-label">Precio de Venta</label>
                    <input type="number" id="precio_de_venta" name="precio_de_venta" class="form-control" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="iva_ventas" class="form-label">IVA Ventas</label>
                    <input type="number" id="iva_ventas" name="iva_ventas" class="form-control" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label for="activo" class="form-label">Activo</label>
                    <input type="checkbox" id="activo" name="activo">
                </div>

                <div class="mb-3">
                    <label for="mostrar_en_ventas" class="form-label">Mostrar en Ventas</label>
                    <input type="checkbox" id="mostrar_en_ventas" name="mostrar_en_ventas">
                </div>

                <div class="mb-3">
                    <label for="mostrar_en_compras" class="form-label">Mostrar en Compras</label>
                    <input type="checkbox" id="mostrar_en_compras" name="mostrar_en_compras">
                </div>

                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen</label>
                    <input type="text" id="imagen" name="imagen" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Guardar Producto</button>
                <a href="lista_productos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
