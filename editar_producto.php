<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Producto y la conexión
include('config.php');
include('clase/Producto.php');

// Crear una instancia de Producto
$producto = new Producto($conn);

// Verificar si se pasó un ID en la URL
if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];

    // Obtener los detalles del producto con el ID proporcionado
    $resultado = $producto->obtenerProductoPorID($id_producto);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
    } else {
        die('Producto no encontrado');
    }
} else {
    die('ID de producto no proporcionado');
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos del formulario
    $nombre = $_POST['Nombre'];
    $tipo = $_POST['Tipo'];
    $tipo_producto_servicio = $_POST['Tipo_Producto_Servicio'];
    $proveedor = $_POST['Proveedor'];
    $codigo = $_POST['Codigo'];
    $deposito_1 = $_POST['Deposito_1'];
    $general = $_POST['General'];
    $stock_total = $_POST['Stock_Total'];
    $costo = $_POST['Costo'];
    $iva_compras = $_POST['IVA_Compras'];
    $precio_venta = $_POST['Precio_de_Venta'];
    $iva_ventas = $_POST['IVA_Ventas'];
    $descripcion = $_POST['Descripcion'];
    $activo = $_POST['Activo'];
    $mostrar_en_ventas = $_POST['Mostrar_en_Ventas'];
    $mostrar_en_compras = $_POST['Mostrar_en_Compras'];

    // Inicializar la variable de imagen
    $imagen = '';

    // Si se ha subido una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Directorio donde se guardarán las imágenes
        $directorioDestino = 'imagenes_productos/';
        
        // Obtener el nombre del archivo y su extensión
        $nombreImagen = basename($_FILES['imagen']['name']);
        $rutaImagen = $directorioDestino . $nombreImagen;

        // Mover el archivo de la imagen al directorio de destino
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagen)) {
            // Si la imagen se subió correctamente, guardamos el nombre del archivo
            $imagen = $nombreImagen;
        } else {
            // Si hubo un error al mover el archivo
            echo "Error al subir la imagen.";
        }
    } else {
        // Si no se subió una nueva imagen, mantener la imagen existente (de la base de datos)
        $imagen = $row['imagen'];
    }

    // Actualizar los datos del producto con la nueva imagen (si se subió una nueva)
    $producto->actualizarProducto($id_producto, $nombre, $tipo, $tipo_producto_servicio, $proveedor, $codigo, $deposito_1, $general, $stock_total, $costo, $iva_compras, $precio_venta, $iva_ventas, $descripcion, $activo, $mostrar_en_ventas, $mostrar_en_compras, $imagen);

    // Redirigir después de actualizar
    header('Location: lista_productos.php');
    exit();
}
?>
<style>
    /* Ajuste para que el contenido quede fuera del encabezado */
    body {
        padding-top: 80px; /* Agrega espacio debajo del header (ajusta según el tamaño del header) */
    }

    .container {
        margin-left: 140px; /* Ajusta el margen a la izquierda según lo necesites */
    }

    /* Si deseas un margen solo en el formulario, puedes hacerlo así */
    form {
        height:40%;
        margin-left: 10px; /* Asegúrate de ajustar esto según sea necesario */
    }

    /* Opcional: Si deseas un mayor margen a nivel de los campos de formulario */
    .form-control {
        margin-left:5px; /* Ajusta según lo necesites */
    }
    .container.mt-4 {
        width: 1200px;
        margin-left: 400px;
        padding: 80px;
        margin-top:70px;

        }

    </style>


  
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header>
        <?php include('headeradmin.php'); ?>
    </header>
    <main>
    <div class="container mt-4">
        <h2>Editar Producto</h2>

        <!-- Formulario para editar producto -->
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="Nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="Nombre" name="Nombre" value="<?php echo $row['Nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="Tipo" name="Tipo" value="<?php echo $row['Tipo']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Tipo_Producto_Servicio" class="form-label">Tipo Producto/Servicio</label>
                <input type="text" class="form-control" id="Tipo_Producto_Servicio" name="Tipo_Producto_Servicio" value="<?php echo $row['Tipo_Producto_Servicio']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Proveedor" class="form-label">Proveedor</label>
                <input type="text" class="form-control" id="Proveedor" name="Proveedor" value="<?php echo $row['Proveedor']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Codigo" class="form-label">Código</label>
                <input type="text" class="form-control" id="Codigo" name="Codigo" value="<?php echo $row['Codigo']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Deposito_1" class="form-label">Depósito 1</label>
                <input type="number" class="form-control" id="Deposito_1" name="Deposito_1" value="<?php echo $row['Deposito_1']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="General" class="form-label">General</label>
                <input type="number" class="form-control" id="General" name="General" value="<?php echo $row['General']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Stock_Total" class="form-label">Stock Total</label>
                <input type="number" class="form-control" id="Stock_Total" name="Stock_Total" value="<?php echo $row['Stock_Total']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Costo" class="form-label">Costo</label>
                <input type="number" step="0.01" class="form-control" id="Costo" name="Costo" value="<?php echo $row['Costo']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="IVA_Compras" class="form-label">IVA Compras</label>
                <input type="number" step="0.01" class="form-control" id="IVA_Compras" name="IVA_Compras" value="<?php echo $row['IVA_Compras']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Precio_de_Venta" class="form-label">Precio de Venta</label>
                <input type="number" step="0.01" class="form-control" id="Precio_de_Venta" name="Precio_de_Venta" value="<?php echo $row['Precio_de_Venta']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="IVA_Ventas" class="form-label">IVA Ventas</label>
                <input type="number" step="0.01" class="form-control" id="IVA_Ventas" name="IVA_Ventas" value="<?php echo $row['IVA_Ventas']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="Descripcion" name="Descripcion" required><?php echo $row['Descripcion']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="Activo" class="form-label">Activo</label>
                <select class="form-control" id="Activo" name="Activo">
                    <option value="1" <?php echo $row['Activo'] == 1 ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo $row['Activo'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="Mostrar_en_Ventas" class="form-label">Mostrar en Ventas</label>
                <select class="form-control" id="Mostrar_en_Ventas" name="Mostrar_en_Ventas">
                    <option value="1" <?php echo $row['Mostrar_en_Ventas'] == 1 ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo $row['Mostrar_en_Ventas'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="Mostrar_en_Compras" class="form-label">Mostrar en Compras</label>
                <select class="form-control" id="Mostrar_en_Compras" name="Mostrar_en_Compras">
                    <option value="1" <?php echo $row['Mostrar_en_Compras'] == 1 ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo $row['Mostrar_en_Compras'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen</label>
                <input type="file" class="form-control" id="imagen" name="imagen">
                <?php if (!empty($row['imagen'])): ?>
                    <p><strong>Imagen Actual:</strong> <img src="imagenes_productos/<?php echo $row['imagen']; ?>" alt="Imagen del producto" width="100"></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
