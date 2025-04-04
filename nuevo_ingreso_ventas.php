<?php
ob_start();
include('config.php');
include('clase/Ingreso.php');

// Obtener lista de productos, clientes, empleados y proveedores
$cuentas = $conn->query("SELECT Id_cuenta, Cuenta FROM cuentas");
$productos = $conn->query("SELECT * FROM producto");
$clientes = $conn->query("SELECT * FROM clientes");
$empleados = $conn->query("SELECT * FROM usuarios WHERE rol IN ('usuario', 'empleado')");
$proveedores = $conn->query("SELECT * FROM proveedores");

// Obtener el nombre del proveedor seleccionado
$proveedor = '';
if (!empty($proveedor)) {
    $proveedor_result = $conn->query("SELECT proveedor FROM proveedores WHERE id = $proveedor");
    if ($proveedor_result->num_rows > 0) {
        $proveedor_data = $proveedor_result->fetch_assoc();
        $proveedor_nombre = $proveedor_data['proveedor'];
    }
}

// Obtener el nombre del cliente seleccionado
$cliente = '';
if (!empty($cliente)) {
    $cliente_result = $conn->query("SELECT nombre FROM clientes WHERE id = $cliente");
    if ($cliente_result->num_rows > 0) {
        $cliente_data = $cliente_result->fetch_assoc();
        $cliente_nombre = $cliente_data['nombre'];
    }
}

// Verificar si se ha enviado el formulario
$productos = $conn->query("SELECT * FROM producto");

// Inicia las variables de subtotal, IVA y total
$subtotal = 0;
$iva = 0;
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $vencimiento = $_POST['vencimiento'];
    $tipo_ingreso = $_POST['tipo_ingreso'];
    $descripcion = $_POST['descripcion'];
    $cliente = $_POST['cliente'];
    $estado = $_POST['estado'];
    $empleado_responsable = $_POST['empleado_responsable'];
    $metodo_pago = $_POST['metodo_pago'];
    $metodo_transporte = $_POST['metodo_transporte'];
    $proveedor = $_POST['proveedor'];
    $tipo_factura = $_POST['tipo_factura'];
    $cuenta = $_POST['cuenta']; // Cambiado de 'id_cuenta' a 'cuenta'

    // Verificar si hay productos seleccionados
    if (!empty($_POST['productos']) && is_array($_POST['productos'])) {
        foreach ($_POST['productos'] as $index => $producto_id) {
            // Obtener el precio de venta del producto seleccionado
            $producto_result = $conn->query("SELECT Precio_de_Venta FROM producto WHERE id = $producto_id");
            $producto_data = $producto_result->fetch_assoc();
            $precio = $producto_data['Precio_de_Venta'];
            
            $cantidad = isset($_POST['cantidad'][$index]) ? floatval($_POST['cantidad'][$index]) : 0;

            // Asegurarse de que la cantidad y el precio sean valores válidos
            if ($cantidad > 0 && $precio > 0) {
                // Sumar al subtotal
                $subtotal += $precio * $cantidad;
            }
        }
    }

    // Calcular IVA (21%) y total
    $iva = $subtotal * 0.21;
    $total = $subtotal + $iva;

    // Calcular el monto total (igual al total calculado)
    $monto = $total;

    // Insertar el ingreso en la base de datos
    $sql = "INSERT INTO ingresos (fecha, vencimiento, tipo_ingreso, descripcion, monto, estado, empleado_responsable, metodo_pago, metodo_transporte, proveedor, tipo_factura, subtotal, iva, total, cliente, id_cuenta) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssdddsd",  // 16 parámetros ahora
            $fecha, 
            $vencimiento, 
            $tipo_ingreso, 
            $descripcion, 
            $monto, // Usamos el monto calculado
            $estado, 
            $empleado_responsable, 
            $metodo_pago, 
            $metodo_transporte, 
            $proveedor, 
            $tipo_factura, 
            $subtotal, 
            $iva, 
            $total, 
            $cliente, 
            $cuenta // Usamos 'cuenta' en lugar de 'id_cuenta'
        );
        

        if ($stmt->execute()) {
            // Obtener el ID del ingreso recién insertado
            $ingreso_id = $conn->insert_id;

            // Insertar los productos en la tabla ingreso_productos
            foreach ($_POST['productos'] as $index => $producto_id) {
                $cantidad = isset($_POST['cantidad'][$index]) ? floatval($_POST['cantidad'][$index]) : 0;
                $precio = isset($_POST['precio'][$index]) ? floatval($_POST['precio'][$index]) : 0;
                $iva_producto = $precio * $cantidad * 0.21; // IVA individual

                if ($cantidad > 0 && $precio > 0) {
                    $sql_detalle = "INSERT INTO ingreso_productos (ingreso_id, producto_id, cantidad, precio, iva) VALUES (?, ?, ?, ?, ?)";
                    if ($stmt_detalle = $conn->prepare($sql_detalle)) {
                        $stmt_detalle->bind_param("iiidd", $ingreso_id, $producto_id, $cantidad, $precio, $iva_producto);
                        $stmt_detalle->execute();
                        $stmt_detalle->close();
                    }
                }
            }

            echo "✅ Ingreso y productos guardados correctamente.";
        } else {
            echo "❌ Error al guardar el ingreso: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "❌ Error en la preparación de la consulta: " . $conn->error;
    }
}
?>



<script>
    function actualizarPrecio(index) {
        // Obtener los valores de los elementos
        const productoSelect = document.getElementById('producto_' + index);
        const cantidadInput = document.getElementById('cantidad_' + index);
        const precioInput = document.getElementById('precio_' + index);

        // Obtener el precio del producto seleccionado desde el atributo 'data-precio'
        const selectedOption = productoSelect.options[productoSelect.selectedIndex];
        const precioUnitario = parseFloat(selectedOption.getAttribute('data-precio'));

        // Obtener la cantidad ingresada
        const cantidad = parseInt(cantidadInput.value) || 0; // Si la cantidad es vacía, ponerla en 0

        // Calcular el precio total (precio unitario * cantidad)
        const precioTotal = precioUnitario * cantidad;

        // Actualizar el campo de precio con el valor calculado
        if (precioTotal > 0) {
            precioInput.value = '$' + precioTotal.toFixed(2);
        } else {
            precioInput.value = ''; // Si la cantidad es 0 o no se ha seleccionado el producto, dejar el precio vacío
        }

        // Calcular el monto total y actualizar el campo Monto
        actualizarMontoTotal();
    }

    function actualizarMontoTotal() {
        let total = 0;
        const cantidadInputs = document.querySelectorAll('input[name^="cantidad"]');
        cantidadInputs.forEach(function(input) {
            const index = input.name.match(/\d+/)[0];
            const precioInput = document.getElementById('precio_' + index);
            const precioTotal = parseFloat(precioInput.value.replace('$', '') || 0);
            total += precioTotal;
        });

        // Mostrar el monto total calculado en el campo Monto
        document.getElementById('monto').value = '$' + total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('input[name^="cantidad"]');
        cantidadInputs.forEach(function(input) {
            const index = input.name.match(/\d+/)[0];
            actualizarPrecio(index);
        });
    });
</script>

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
    <title>Nuevo Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Ingreso</h2>

        <form method="POST">
            <!-- Fecha y Vencimiento -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="vencimiento" class="form-label">Vencimiento</label>
                <input type="date" name="vencimiento" class="form-control" required>
            </div>

            <!-- Tipo de Ingreso -->
            <div class="mb-3">
                <label for="tipo_ingreso" class="form-label">Tipo de Ingreso</label>
                <select name="tipo_ingreso" class="form-control" required>
                    <option value="presupuesto">Presupuesto</option>
                    <option value="venta">Venta</option>
                </select>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <!-- Monto Total de la Compra -->
            <div class="mb-3">
                <label for="monto" class="form-label">Monto Total</label>
                <input type="text" name="monto" class="form-control" id="monto" readonly>
            </div>

            <!-- Productos -->
            <div class="producto-item">
                <select name="productos[1]" class="form-control" id="producto_1" required onchange="actualizarPrecio(1)">
                    <option value="">Seleccione un Producto</option>
                    <?php while ($producto = $productos->fetch_assoc()) : ?>
                        <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['Precio_de_Venta']; ?>">
                            <?php echo $producto['Nombre']; ?> - $<?php echo number_format($producto['Precio_de_Venta'], 2); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="cantidad[1]" class="form-control mt-2" id="cantidad_1" placeholder="Cantidad" min="1" required oninput="actualizarPrecio(1)">
                <input type="text" name="precio[1]" class="form-control mt-2" id="precio_1" placeholder="Precio" readonly>
            </div>

            <button type="button" id="agregar-producto" class="btn btn-secondary mt-2">+ Agregar Producto</button>

            <!-- Selección de Proveedor -->
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

            <!-- Cliente -->
            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <select name="cliente" class="form-control" required>
                    <option value="">Seleccione un Cliente</option>
                    <?php while ($cliente = $clientes->fetch_assoc()) : ?>
                        <option value="<?php echo $cliente['id']; ?>">
                            <?php echo $cliente['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Responsable Inscripto -->
            <div class="mb-3">
                <label for="responsable_inscripto" class="form-label">¿Es Responsable Inscripto?</label>
                <select name="responsable_inscripto" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>

            <!-- Empleado Responsable -->
            <div class="mb-3">
    <label for="empleado_responsable" class="form-label">Empleado Responsable</label>
    <!-- Campo oculto para enviar el valor del usuario -->
    <input type="hidden" name="empleado_responsable" value="<?php echo $_SESSION['usuario']; ?>">
    
    <!-- Campo solo de lectura para mostrar el nombre del usuario -->
    <input type="text" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
</div>


            <!-- Tipo de Factura -->
            <div class="mb-3">
                <label for="tipo_factura" class="form-label">Tipo de Factura</label>
                <select name="tipo_factura" class="form-control" required>
                    <option value="A">Factura A</option>
                    <option value="B">Factura B</option>
                    <option value="C">Factura C</option>
                </select>
            </div>
            <div class="mb-3">
    <label for="cuenta" class="form-label">Cuenta</label>
    <select name="cuenta" class="form-control" required>
        <?php
        // Consulta para obtener las cuentas desde la tabla "cuentas"
        $query_cuentas = "SELECT Id_cuenta, Cuenta FROM cuentas";
        $resultado_cuentas = $conn->query($query_cuentas);

        // Mostrar las cuentas en el select
        while ($cuenta = $resultado_cuentas->fetch_assoc()) :
        ?>
            <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                <?php echo $cuenta['Cuenta']; ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>



            <!-- Estado -->
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" class="form-control" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="vencida">Vencida</option>
                    <option value="facturado">Facturado</option>
                </select>
            </div>

            <!-- Métodos de Pago y Transporte -->
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="metodo_transporte" class="form-label">Método de Transporte</label>
                <select name="metodo_transporte" class="form-control" required>
                    <option value="recojo">Recojo</option>
                    <option value="envio">Envío</option>
                </select>
            </div>

            <!-- Botón para guardar -->
            <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
