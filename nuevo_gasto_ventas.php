<?php
ob_start();
include('config.php');
include('clase/Gasto.php');

// Obtener lista de productos y proveedores
$productosQuery = $conn->query("SELECT * FROM producto");
$proveedores = $conn->query("SELECT * FROM proveedores");

// Guardar los productos en un array para reutilizarlos
$productosArray = [];
while ($row = $productosQuery->fetch_assoc()) {
    $productosArray[] = $row;
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que todos los campos necesarios estén presentes
    if (empty($_POST['fecha']) || empty($_POST['proveedor']) || empty($_POST['estado']) || empty($_POST['metodo_pago']) || empty($_POST['categoria_pago']) || empty($_POST['descripcion']) || empty($_POST['productos'])) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado']; // Estado (Por Pagar o Pagado)
    $metodo_pago = $_POST['metodo_pago'];
    $categoria_pago = $_POST['categoria_pago'];
    $descripcion = $_POST['descripcion'];

    // Calcular el subtotal, IVA y total
    $subtotal = 0;
    $iva = 0;
    $productos_seleccionados = [];

    // Recorremos el array de productos seleccionados
    foreach ($_POST['productos'] as $producto_id => $cantidad) {
        // Usamos el array de productos cargado previamente
        foreach ($productosArray as $producto) {
            if ($producto['id'] == $producto_id) {
                $precio_unitario = $producto['precio'];
                $subtotal += $precio_unitario * $cantidad;
                $productos_seleccionados[] = [
                    'producto' => $producto['nombre'],
                    'cantidad' => $cantidad,
                    'precio' => $precio_unitario,
                    'total' => $precio_unitario * $cantidad
                ];
            }
        }
    }

    // Calcular IVA (21%)
    $iva = $subtotal * 0.21;

    // Total con IVA
    $total = $subtotal + $iva;

    // Insertar el gasto en la base de datos
    $gasto = new Gasto($conn);

    // Asegúrate de que el método insertarGasto esté preparado correctamente
    if ($gasto->insertarGasto($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $total, $productos_seleccionados)) {
        // Redirigir con éxito
        header('Location: gastosventas.php?status=success');
        exit();
    } else {
        // Redirigir con error
        header('Location: gastosventas.php?status=error');
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

    .producto-item {
        margin-top: 10px;
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
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Gasto</h2>

        <form method="POST">
            <!-- Fecha -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha de Emisión</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>

            <!-- Proveedor -->
            <div class="mb-3">
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

            <!-- Estado -->
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" class="form-control" required>
                    <option value="por_pagar">Por Pagar</option>
                    <option value="pagado">Pagado</option>
                </select>
            </div>

            <!-- Método de Pago -->
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <!-- Categoría de Pago -->
            <div class="mb-3">
                <label for="categoria_pago" class="form-label">Categoría de Pago</label>
                <select name="categoria_pago" class="form-control" required>
                    <option value="contado">Contado</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <!-- Productos -->
            <div id="productos-container"></div>

            <button type="button" id="agregar-producto" class="btn btn-secondary mt-2">+ Agregar Producto</button>

            <!-- Mostrar el monto total -->
            <div class="mt-3">
                <h4>Total: <span id="total">$0.00</span></h4>
            </div>
    <button type="submit" class="btn btn-primary mt-3">Guardar Gasto</button>
    <script>
    function actualizarPrecio(index) {
        const productoSelect = document.getElementById('producto_' + index);
        const cantidadInput = document.getElementById('cantidad_' + index);
        const precioInput = document.getElementById('precio_' + index);

        const selectedOption = productoSelect.options[productoSelect.selectedIndex];
        const precioUnitario = parseFloat(selectedOption.getAttribute('data-precio')) || 0;
        const cantidad = parseInt(cantidadInput.value) || 0;

        const precioTotal = precioUnitario * cantidad;

        if (precioTotal > 0) {
            precioInput.value = '$' + precioTotal.toFixed(2);
        } else {
            precioInput.value = '';
        }
        actualizarTotal();
    }

    function actualizarTotal() {
        let total = 0;

        // Sumamos el total de todos los productos
        document.querySelectorAll('.producto-item').forEach(function(item) {
            const precioInput = item.querySelector('input[name^="precio"]');
            const precio = parseFloat(precioInput.value.replace('$', '')) || 0;
            total += precio;
        });

        // Mostrar el total en la interfaz
        document.getElementById('total').innerText = '$' + total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('input[name^="cantidad"]');
        cantidadInputs.forEach(function(input) {
            const index = input.name.match(/\d+/)[0];
            actualizarPrecio(index);  // Actualizamos el precio de cada producto al cargar la página
        });
    });

    let productCount = 1;

    document.getElementById('agregar-producto').addEventListener('click', function() {
        productCount++;
        const productItem = document.createElement('div');
        productItem.classList.add('producto-item');
        productItem.innerHTML = `
            <select name="productos[${productCount}]" class="form-control" id="producto_${productCount}" required onchange="actualizarPrecio(${productCount})">
                <option value="">Seleccione un Producto</option>
                <?php foreach($productosArray as $producto): ?>
                    <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['Costo']; ?>">
                        <?php echo $producto['Nombre']; ?> - $<?php echo number_format($producto['Costo'], 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="cantidad[${productCount}]" class="form-control mt-2" id="cantidad_${productCount}" placeholder="Cantidad" min="1" required oninput="actualizarPrecio(${productCount})">
            <input type="text" name="precio[${productCount}]" class="form-control mt-2" id="precio_${productCount}" placeholder="Precio" readonly>
        `;
        document.getElementById('productos-container').appendChild(productItem);
        actualizarTotal();
    });
</script>
</form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
