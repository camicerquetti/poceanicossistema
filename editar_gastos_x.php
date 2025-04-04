<?php
ob_start();
include('config.php');
include('clase/Gastosx.php');

// Obtener lista de proveedores
$proveedores = $conn->query("SELECT * FROM proveedores");

// Obtener el id del gasto a editar desde la URL
$gasto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cargar el gasto existente
$gasto_data = $conn->query("SELECT * FROM gastosx WHERE id = $gasto_id")->fetch_assoc();
if (!$gasto_data) {
    echo "Gasto no encontrado.";
    exit();
}

// Procesar el formulario al enviarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que todos los campos necesarios estén presentes
    if (empty($_POST['fecha']) || empty($_POST['proveedor']) || empty($_POST['descripcion']) || empty($_POST['categoria']) || empty($_POST['metodo_pago']) || empty($_POST['monto']) || empty($_POST['estado'])) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $metodo_pago = $_POST['metodo_pago'];
    $monto = $_POST['monto'];
    $estado = $_POST['estado'];

    $gasto = new Gasto($conn);

    // Actualizar el gasto usando el método actualizarGasto
    if ($gasto->actualizarGasto($gasto_id, $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado)) {
        header('Location: gastosx.php?status=success');
        exit();
    } else {
        header('Location: gastos.php?status=error');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container.mt-4 {
            width: 1200px;
            margin-left: 220px;
            padding: 80px;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Editar Gasto X</h2>

        <form method="POST">
            <div class="row">
                <!-- Fecha de Emisión -->
                <div class="col-md-6 mb-3">
                    <label for="fecha" class="form-label">Fecha de Emisión</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo $gasto_data['fecha'] ?? ''; ?>" required>
                </div>
                
                <!-- Estado -->
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" class="form-control" required>
                        <option value="a_pagar" <?php echo (($gasto_data['estado'] ?? '') === 'a_pagar') ? 'selected' : ''; ?>>A Pagar</option>
                        <option value="pagado" <?php echo (($gasto_data['estado'] ?? '') === 'pagado') ? 'selected' : ''; ?>>Pagado</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Proveedor -->
                <div class="col-md-6 mb-3">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <select name="proveedor" class="form-control" required>
                        <option value="">Seleccione un Proveedor</option>
                        <?php 
                        // Reiniciamos el cursor del query para recorrerlo nuevamente
                        $proveedores->data_seek(0);
                        while ($prov = $proveedores->fetch_assoc()) : 
                            $selected = ($prov['id'] == ($gasto_data['proveedor'] ?? '')) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $prov['id']; ?>" <?php echo $selected; ?>>
                                <?php echo $prov['proveedor']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Descripción -->
                <div class="col-md-6 mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" value="<?php 
                        // Si se usa 'descripcion' o 'descripción'
                        echo $gasto_data['descripcion'] ?? $gasto_data['descripción'] ?? ''; 
                    ?>" required>
                </div>
            </div>

            <div class="row">
                <!-- Categoría de Pago -->
                <div class="col-md-6 mb-3">
                    <label for="categoria" class="form-label">Categoría de Pago</label>
                    <input type="text" name="categoria" class="form-control" value="<?php echo $gasto_data['categoria'] ?? ''; ?>" required>
                </div>

                <!-- Método de Pago -->
                <div class="col-md-6 mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <input type="text" name="metodo_pago" class="form-control" value="<?php echo $gasto_data['metodo_pago'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="row">
                <!-- Monto Total del Gasto -->
                <div class="col-md-6 mb-3">
                    <label for="monto" class="form-label">Monto Total del Gasto</label>
                    <input type="number" step="0.01" name="monto" class="form-control" value="<?php echo $gasto_data['monto'] ?? ''; ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Guardar Gasto</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
