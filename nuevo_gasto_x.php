<?php
ob_start();
include('config.php');
include('clase/Gastosx.php');

// Obtener lista de proveedores
$proveedores = $conn->query("SELECT * FROM proveedores");

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que todos los campos necesarios estén presentes
    if (empty($_POST['fecha']) || empty($_POST['proveedor']) || empty($_POST['estado']) || empty($_POST['metodo_pago']) || empty($_POST['categoria_pago']) || empty($_POST['descripcion']) || empty($_POST['monto_total'])) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado']; // Estado (Por Pagar o Pagado)
    $metodo_pago = $_POST['metodo_pago']; // Ahora es un campo de texto
    $categoria_pago = $_POST['categoria_pago']; // Ahora es un campo de texto
    $descripcion = $_POST['descripcion'];
    $monto_total = $_POST['monto_total']; // El monto total

    // Calcular el subtotal y el IVA
    $subtotal = $monto_total / 1.21; // Restando el IVA del total
    $iva = $monto_total - $subtotal;

    // Insertar el gasto en la base de datos
    $gasto = new Gasto($conn);

    // Asegúrate de que el método insertarGasto reciba todos los parámetros correctamente.
    if ($gasto->insertarGasto($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $monto_total, '')) {
        // Redirigir con éxito
        header('Location: gastos.php?status=success');
        exit();
    } else {
        // Redirigir con error
        header('Location: gastos.php?status=error');
        exit();
    }
}
?>

<style>
    .container.mt-4 {
        width: 1200px;
        margin-left: 220px;
        padding: 80px;
    }
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Gasto X</h2>

        <form method="POST">
    <div class="row">
        <!-- Fecha -->
        <div class="col-md-6 mb-3">
            <label for="fecha" class="form-label">Fecha de Emisión</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>

        <!-- Proveedor -->
        <div class="col-md-6 mb-3">
            <label for="proveedor" class="form-label">Proveedor</label>
            <select name="proveedor" class="form-control" required>
                <option value="">Seleccione un Proveedor</option>
                <?php while ($proveedor = $proveedores->fetch_assoc()) : ?>
                    <option value="<?php echo $proveedor['id']; ?>">
                        <?php echo $proveedor['proveedor']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Estado -->
        <div class="col-md-6 mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" class="form-control" required>
                <option value="a_pagar">A Pagar</option>
                <option value="pagado">Pagado</option>
            </select>
        </div>

        <!-- Método de Pago -->
        <div class="col-md-6 mb-3">
            <label for="metodo_pago" class="form-label">Método de Pago</label>
            <input type="text" name="metodo_pago" class="form-control" required>
        </div>
    </div>

    <div class="row">
        <!-- Categoría de Pago -->
        <div class="col-md-6 mb-3">
            <label for="categoria_pago" class="form-label">Categoría de Pago</label>
            <input type="text" name="categoria_pago" class="form-control" required>
        </div>

        <!-- Descripción -->
        <div class="col-md-6 mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <input type="text" name="descripcion" class="form-control" required>
        </div>
    </div>

    <div class="row">
        <!-- Monto Total del Gasto -->
        <div class="col-md-6 mb-3">
            <label for="monto_total" class="form-label">Monto Total del Gasto</label>
            <input type="number" step="0.01" name="monto_total" class="form-control" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Guardar Gasto</button>
</form>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
