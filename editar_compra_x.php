<?php
ob_start();
include('config.php');
include('clase/Ingreso.php');

$productos = $conn->query("SELECT * FROM producto");
$proveedores = $conn->query("SELECT * FROM proveedores");
$cuentas = $conn->query("SELECT * FROM cuentasx");

$productosArray = $productos->fetch_all(MYSQLI_ASSOC);
$proveedoresArray = $proveedores->fetch_all(MYSQLI_ASSOC);
$cuentasArray = $cuentas->fetch_all(MYSQLI_ASSOC);

$compra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Eliminamos los var_dump de depuración
// var_dump($compra_id);

$compra = $conn->query("SELECT * FROM comprax WHERE id = $compra_id")->fetch_assoc();
// var_dump($compra);

$productosCompra = $conn->query("SELECT * FROM productos_comprax WHERE compra_id = $compra_id")->fetch_all(MYSQLI_ASSOC);
// var_dump($productosCompra);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $vencimiento = $_POST['vencimiento'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado'];
    $metodo_pago = $_POST['metodo_pago'];
    $categoria_pago = $_POST['categoria_pago'];
    $descripcion = $_POST['descripcion'];
    $vendedor = $_POST['vendedor'];
    $cuenta = $_POST['cuenta'];

    $query_actualizar = "UPDATE compras SET 
        emision='$fecha', vencimiento='$vencimiento', proveedor='$proveedor', 
        categoria='$categoria_pago', estado='$estado', metodo_pago='$metodo_pago', 
        descripcion='$descripcion', vendedor='$vendedor', cuenta='$cuenta'
        WHERE id=$compra_id";
    
    if ($conn->query($query_actualizar) === TRUE) {
        $conn->query("DELETE FROM productos_compra WHERE compra_id = $compra_id");

        foreach ($_POST['productos'] as $index => $producto_id) {
            $cantidad = $_POST['cantidad'][$index];
            $precio = $_POST['precio'][$index];
            $peso = $_POST['peso'][$index];

            if (is_numeric($producto_id)) {
                $query_producto = "INSERT INTO productos_comprax (compra_id, producto_id, cantidad, precio_unitario, peso_kg) 
                    VALUES ($compra_id, $producto_id, $cantidad, $precio, $peso)";
                $conn->query($query_producto);
            }
        }
        echo "Compra actualizada correctamente.";
    } else {
        echo "Error al actualizar la compra: " . $conn->error;
    }
}
?>

<style>
    .container.mt-4 {
        width: 1200px;
        margin-left: 220px;
        padding: 80px;
    }

    .producto-item {
        margin-top: 10px;
    }
</style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Editar Compra</h2>

        <form method="POST">
            <!-- Otros campos del formulario -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha de la Compra</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo $compra['emision'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="vencimiento" class="form-label">Fecha de Vencimiento</label>
                <input type="date" name="vencimiento" id="vencimiento" class="form-control" value="<?php echo $compra['vencimiento'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select name="proveedor" id="proveedor" class="form-control" required>
                    <option value="">Seleccione un Proveedor</option>
                    <?php
                    foreach ($proveedoresArray as $prov) {
                        $selected = ($prov['id'] == $compra['proveedor']) ? 'selected' : '';
                        echo "<option value='" . $prov['id'] . "' $selected>" . $prov['proveedor'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="a_cobrar" <?php echo ($compra['estado'] == 'a_cobrar') ? 'selected' : ''; ?>>A Cobrar</option>
                    <option value="cobrado" <?php echo ($compra['estado'] == 'cobrado') ? 'selected' : ''; ?>>Cobrado</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo" <?php echo ($compra['metodo_pago'] == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="tarjeta" <?php echo ($compra['metodo_pago'] == 'tarjeta') ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="transferencia" <?php echo ($compra['metodo_pago'] == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="categoria_pago" class="form-label">Categoría de Pago</label>
                <input type="text" name="categoria_pago" class="form-control" value="<?php echo $compra['categoria'] ?? ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" value="<?php echo $compra['descripcion'] ?? ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="vendedor" class="form-label">Vendedor</label>
                <input type="text" name="vendedor" class="form-control" value="<?php echo $_SESSION['usuario'] ?? ''; ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="cuenta" class="form-label">Cuenta</label>
                <select name="cuenta" class="form-control" required>
                    <option value="">Seleccione una Cuenta</option>
                    <?php
                    foreach ($cuentasArray as $cuenta) {
                        $selected = ($cuenta['Id_cuenta'] == $compra['cuenta']) ? 'selected' : '';
                        echo "<option value='" . $cuenta['Id_cuenta'] . "' $selected>" . $cuenta['Cuenta'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="productos-container">
                <?php
                if (!empty($productosCompra)) {
                    foreach ($productosCompra as $index => $productoCompra) {
                        ?>
                        <div class="producto-item">
                            <select name="productos[]" class="form-control" required>
                                <option value="">Seleccione un Producto</option>
                                <?php
                                foreach ($productosArray as $producto) {
                                    $selected = ($producto['id'] == $productoCompra['producto_id']) ? 'selected' : '';
                                    echo "<option value='" . $producto['id'] . "' $selected>" . $producto['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                            <input type="number" name="cantidad[]" class="form-control mt-2" value="<?php echo $productoCompra['cantidad']; ?>" required>
                            <input type="number" name="precio[]" class="form-control mt-2" value="<?php echo $productoCompra['precio_unitario']; ?>" required>
                            <input type="number" name="peso[]" class="form-control mt-2" value="<?php echo $productoCompra['peso_kg']; ?>" required>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No hay productos en esta compra.</p>";
                }
                ?>
            </div>

            <div class="mb-3">
                <label for="total" class="form-label">Total de la Compra</label>
                <input type="text" name="total" id="total" class="form-control" value="<?php echo $compra['total'] ?? ''; ?>" readonly>
            </div>

            <button type="button" id="agregar-producto" class="btn btn-secondary">Agregar Producto</button>
            <button type="submit" class="btn btn-primary">Guardar Compra</button>
        </form>
    </div>

    <script>
        document.getElementById('agregar-producto').addEventListener('click', function () {
            const productosContainer = document.getElementById('productos-container');
            const index = productosContainer.children.length;
            let options = '<option value="">Seleccione un Producto</option>';
            <?php foreach ($productosArray as $producto) { ?>
                options += `<option value="<?php echo $producto['id']; ?>"><?php echo $producto['Nombre']; ?></option>`;
            <?php } ?>

            const productItem = document.createElement('div');
            productItem.classList.add('producto-item');
            productItem.innerHTML = `
                <select name="productos[]" class="form-control" required>${options}</select>
                <input type="number" name="cantidad[]" class="form-control mt-2" placeholder="Cantidad" required>
                <input type="number" name="precio[]" class="form-control mt-2" placeholder="Precio" required>
                <input type="number" name="peso[]" class="form-control mt-2" placeholder="Peso (kg)" required>
            `;
            productosContainer.appendChild(productItem);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
