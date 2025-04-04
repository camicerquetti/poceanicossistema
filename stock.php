<?php
ob_start();
// Incluir la conexión a la base de datos
include('config.php');

// Verificar si se ha enviado el formulario para ajustar el stock
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $accion = $_POST['accion']; // 'sumar' o 'restar'

    // Validar los valores
    if (is_numeric($cantidad) && $cantidad > 0) {
        // Obtener el stock actual del producto
        $query = "SELECT Stock_Total FROM producto WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stock_actual = $row['Stock_Total'];

            // Calcular el nuevo stock basado en la acción
            if ($accion == 'sumar') {
                $nuevo_stock = $stock_actual + $cantidad;
            } elseif ($accion == 'restar' && $stock_actual >= $cantidad) {
                $nuevo_stock = $stock_actual - $cantidad;
            } else {
                $error = "No se puede restar más stock del que existe.";
            }

            // Si no hay error, actualizar el stock en la base de datos
            if (!isset($error)) {
                $update_query = "UPDATE producto SET Stock_Total = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('ii', $nuevo_stock, $producto_id);
                $update_stmt->execute();
                
                $mensaje = "El stock se ha actualizado correctamente.";
            }
        } else {
            $error = "Producto no encontrado.";
        }
    } else {
        $error = "Cantidad inválida.";
    }
}

// Obtener todos los productos para mostrar en el formulario
$query = "SELECT id, Nombre FROM producto";
$result = $conn->query($query);
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
?>
<style>
     .container.mt-4 {
        width:1200px;  
        margin-left: 220px;
        padding: 70px;
    }
</style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustar Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Incluir el header de administración -->
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Ajustar Stock de Productos</h2>
        
        <!-- Mostrar mensaje de éxito o error -->
        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php } elseif (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <!-- Formulario para ajustar el stock -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="producto_id" class="form-label">Selecciona el Producto</label>
                <select name="producto_id" id="producto_id" class="form-select" required>
                    <option value="">Selecciona un producto</option>
                    <?php foreach ($productos as $producto) { ?>
                        <option value="<?php echo $producto['id']; ?>"><?php echo $producto['Nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="accion" class="form-label">Acción</label>
                <select name="accion" id="accion" class="form-select" required>
                    <option value="sumar">Sumar</option>
                    <option value="restar">Restar</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Stock</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
