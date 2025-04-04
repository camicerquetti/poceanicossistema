<?php
ob_start();
include('config.php');
include('clase/Ingreso.php');

// Obtener lista de productos, proveedores y cuentas
$productos = $conn->query("SELECT * FROM producto");
$proveedores = $conn->query("SELECT * FROM proveedores");
$cuentas = $conn->query("SELECT * FROM cuentas");

// Convertir los productos a un array PHP
$productosArray = [];
while ($producto = $productos->fetch_assoc()) {
    $productosArray[] = $producto;
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado']; // Estado (Por pagar o Pagado)
    $metodo_pago = $_POST['metodo_pago'];
    $categoria_pago = $_POST['categoria_pago'];
    $descripcion = $_POST['descripcion'];
    $cuenta_id = $_POST['cuenta']; // Obtener la cuenta seleccionada
    $vendedor = $_SESSION['usuario']; // Asumiendo que el nombre del usuario está en la sesión

    // Calcular el subtotal, IVA y total
    $subtotal = 0;
    $iva = 0;
    $productos_seleccionados = [];

    foreach ($_POST['productos'] as $producto_id => $cantidad) {
        // Ejecuta la consulta para obtener el producto
        $producto = $conn->query("SELECT * FROM producto WHERE id = $producto_id")->fetch_assoc();
        
        // Verificar si el producto existe
        if ($producto) {
            $precio_unitario = $producto['Costo']; // Asegúrate de usar la columna 'Costo' que es donde está el precio
            $subtotal += $precio_unitario * $cantidad;
            $productos_seleccionados[] = [
                'producto' => $producto['Nombre'],
                'cantidad' => $cantidad,
                'precio' => $precio_unitario,
                'total' => $precio_unitario * $cantidad
            ];
        } else {
            // Si no existe el producto, mostrar un mensaje de error
            echo "Producto con ID $producto_id no encontrado.";
        }
    }
    
    // Calcular IVA (21%)
    $iva = $subtotal * 0.21;

    // Total con IVA
    $total = $subtotal + $iva;

    // Convertir los productos seleccionados a JSON
    $productos_json = json_encode($productos_seleccionados);

    // Insertar la compra en la base de datos
    $query = "INSERT INTO compras (emision, proveedor, categoria, subtotal, descuento, cantidad, total, vencimientoPago, tipoCompra, producto, precio, iva, notaInterna, contador, estado, vendedor, productos)
              VALUES ('$fecha', '$proveedor', '$categoria_pago', '$subtotal', 0, 0, '$total', NULL, '$metodo_pago', '$descripcion', '$precio_unitario', '$iva', '$descripcion', '$vendedor', '$estado', '$vendedor', '$productos_json')";

    if ($conn->query($query) === TRUE) {
        echo "Compra insertada correctamente.";
    } else {
        echo "Error al insertar la compra: " . $conn->error;
    }

    // Actualizar el saldo de la cuenta seleccionada
    $cuenta = $conn->query("SELECT * FROM cuentas WHERE Id_cuenta = $cuenta_id")->fetch_assoc();
    $nuevo_saldo = $cuenta['Saldo'] - $total;
    $conn->query("UPDATE cuentas SET Saldo = $nuevo_saldo WHERE Id_cuenta = $cuenta_id");

    // Redirigir a la lista de compras
    header('Location: comprasventas.php');
    exit();
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
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nueva Compra</h2>

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

            <!-- Selección de Cuenta -->
            <div class="mb-3">
                <label for="cuenta" class="form-label">Seleccionar Cuenta</label>
                <select name="cuenta" class="form-control" required>
                    <option value="">Seleccione una Cuenta</option>
                    <?php while ($cuenta = $cuentas->fetch_assoc()) : ?>
                        <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                            <?php echo $cuenta['Cuenta']; ?> (Saldo: $<?php echo number_format($cuenta['Saldo'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Vendedor (Nombre del usuario en la sesión) -->
            <div class="mb-3">
                <label for="vendedor" class="form-label">Vendedor</label>
                <input type="text" name="vendedor" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
            </div>

            <!-- Productos -->
            <div id="productos-container"></div>

            <!-- Monto Total -->
            <div class="mb-3">
                <label for="total" class="form-label">Total de la Compra</label>
                <input type="text" name="total" id="total" class="form-control" readonly>
            </div>

            <!-- Botón para agregar productos -->
            <button type="button" id="agregar-producto" class="btn btn-secondary mt-2">+ Agregar Producto</button>

            <button type="submit" class="btn btn-primary mt-3">Guardar Compra</button>
        </form>
    </div>

    <script>
        // Convertir el array de productos a JSON y pasarlo a JavaScript
        const productos = <?php echo json_encode($productosArray); ?>;
        let totalCompra = 0;  // Variable para mantener el total de la compra

        // Función para agregar un producto al formulario
        function agregarProducto(index) {
            const productItem = document.createElement('div');
            productItem.classList.add('producto-item');

            // Crear las opciones para el select de productos
            let options = '<option value="">Seleccione un Producto</option>';
            productos.forEach(producto => {
                options += `<option value="${producto.id}" data-precio="${producto.Costo}" data-nombre="${producto.Nombre}">${producto.Nombre} - $${parseFloat(producto.Costo).toFixed(2)}</option>`;
            });

            productItem.innerHTML = ` 
                <select name="productos[${index}]" class="form-control" id="producto_${index}" required onchange="actualizarPrecio(${index})">
                    ${options}
                </select>
                <input type="number" name="cantidad[${index}]" class="form-control mt-2" id="cantidad_${index}" placeholder="Cantidad" min="1" required oninput="actualizarPrecio(${index})">
                <input type="text" name="precio[${index}]" class="form-control mt-2" id="precio_${index}" placeholder="Precio" readonly>
            `;
            document.getElementById('productos-container').appendChild(productItem);
        }

        // Función para actualizar el precio total del producto
        function actualizarPrecio(index) {
            const productoSelect = document.getElementById('producto_' + index);
            const cantidadInput = document.getElementById('cantidad_' + index);
            const precioInput = document.getElementById('precio_' + index);

            const selectedOption = productoSelect.options[productoSelect.selectedIndex];
            const costoUnitario = parseFloat(selectedOption.getAttribute('data-precio')); // Usamos el costo
            const cantidad = parseInt(cantidadInput.value) || 0;

            const precioTotal = costoUnitario * cantidad;

            if (precioTotal > 0) {
                precioInput.value = '$' + precioTotal.toFixed(2);
                actualizarTotalCompra();
            } else {
                precioInput.value = '';
            }
        }

        // Actualizar el monto total de la compra
        function actualizarTotalCompra() {
            totalCompra = 0;

            // Sumar los valores de todos los productos
            const precios = document.querySelectorAll('[name^="precio"]');
            precios.forEach(precio => {
                const precioTotal = parseFloat(precio.value.replace('$', '')) || 0;
                totalCompra += precioTotal;
            });

            // Mostrar el total en el campo correspondiente
            document.getElementById('total').value = '$' + totalCompra.toFixed(2);
        }

        // Agregar el primer producto al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            agregarProducto(1);
        });

        let productCount = 1;

        // Agregar más productos al hacer clic en el botón
        document.getElementById('agregar-producto').addEventListener('click', function () {
            productCount++;
            agregarProducto(productCount);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
