<?php
ob_start();
include('config.php');
include('clase/Ingresosx.php');

// Obtener lista de productos, clientes, empleados y proveedores
$cuentas = $conn->query("SELECT Id_cuenta, Cuenta FROM cuentasx");
$productos = $conn->query("SELECT id, Nombre FROM producto");
$clientes = $conn->query("SELECT * FROM clientes");
$empleados = $conn->query("SELECT * FROM usuarios WHERE rol IN ('usuario', 'empleado')");
$proveedores = $conn->query("SELECT * FROM proveedores");

// Almacenar productos en un array asociativo
$productos_data = [];
while ($row = $productos->fetch_assoc()) {
    $productos_data[$row['id']] = $row['Nombre'];
}

// Obtener el nombre del proveedor y cliente seleccionado
$proveedor_nombre = '';
$cliente_nombre = '';

if (!empty($proveedor)) {
    $proveedor_result = $conn->query("SELECT proveedor FROM proveedores WHERE id = $proveedor");
    if ($proveedor_result->num_rows > 0) {
        $proveedor_data = $proveedor_result->fetch_assoc();
        $proveedor_nombre = $proveedor_data['proveedor'];
    }
}

if (!empty($cliente)) {
    $cliente_result = $conn->query("SELECT nombre FROM clientes WHERE id = $cliente");
    if ($cliente_result->num_rows > 0) {
        $cliente_data = $cliente_result->fetch_assoc();
        $cliente_nombre = $cliente_data['nombre'];
    }
}

// Inicializar variables de subtotal, IVA y total
$subtotal = 0;
$iva = 0;
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
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
    $cuenta = $_POST['cuenta'];  // Usar 'cuenta' en lugar de 'id_cuenta'
    $vendedor_nombre = $_POST['vendedor_nombre'];  // Usar 'cuenta' en lugar de 'id_cuenta'

    // Obtener el último número de factura generado o establecer el valor inicial si no existen facturas
    $query = "SELECT factura_afip FROM ingresos WHERE factura_afip LIKE '001-PED-%' ORDER BY factura_afip DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Si encontramos una factura, extraemos el número
        $row = $result->fetch_assoc();
        $ultimo_factura = $row['factura_afip'];
        // Extraemos la parte numérica de la factura
        $ultimo_numero = (int) substr($ultimo_factura, strrpos($ultimo_factura, '-') + 1);
        // Incrementar el número para la siguiente factura
        $ultimo_id = $ultimo_numero + 1;
    } else {
        // Si no hay ninguna factura, comenzamos con el primer número
        $ultimo_id = 1;
    }

    // Generar el número de factura en formato 001-PED-XXXX
    $factura_afip = "001-PED-" . str_pad($ultimo_id, 4, '0', STR_PAD_LEFT);

    // Verificar los productos seleccionados
    if (!empty($_POST['productos']) && is_array($_POST['productos'])) {
        foreach ($_POST['productos'] as $index => $producto_id) {
            $precio = isset($_POST['precio'][$index]) ? (float)$_POST['precio'][$index] : 0;
            $cantidad = isset($_POST['cantidad'][$index]) ? (float)$_POST['cantidad'][$index] : 0;

            // Asegurarse de que la cantidad y el precio sean válidos
            if ($cantidad > 0 && $precio > 0) {
                $subtotal += $precio * $cantidad;
            }
        }
    }

    // Calcular IVA (21%) y total
    $iva = $subtotal * 0.21;
    $total = $subtotal + $iva;
    $monto = $total;

    // Insertar ingreso en la tabla ingresosx
    $sql = "INSERT INTO ingresosx (fecha, vencimiento, tipo_ingreso, descripcion, monto, estado, empleado_responsable, metodo_pago, metodo_transporte, proveedor, tipo_factura, subtotal, iva, total, cliente, id_cuenta, vendedor_nombre, factura_afip) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssdddsddd", 
            $fecha, 
            $vencimiento, 
            $tipo_ingreso, 
            $descripcion, 
            $monto, 
            $estado,  // Asegúrate de que 'estado' es correcto
            $empleado_responsable, 
            $metodo_pago, 
            $metodo_transporte, 
            $proveedor, 
            $tipo_factura, 
            $subtotal, 
            $iva, 
            $total, 
            $cliente, 
            $cuenta,
            $vendedor_nombre,
            $factura_afip // Agregar el número de factura
        );

        if ($stmt->execute()) {
            // Obtener el ID del ingreso recién insertado
            $ingreso_id = $conn->insert_id;

            // Insertar productos en la tabla ingreso_productosx
            foreach ($_POST['productos'] as $index => $producto_id) {
                $cantidad = isset($_POST['cantidad'][$index]) ? floatval($_POST['cantidad'][$index]) : 0;
                $precio = isset($_POST['precio'][$index]) ? floatval($_POST['precio'][$index]) : 0;
                $peso_kg = isset($_POST['peso_kg'][$index]) ? floatval($_POST['peso_kg'][$index]) : 0; // Obtener el peso del formulario

                if (isset($productos_data[$producto_id]) && $cantidad > 0 && $precio > 0) {
                    $iva_producto = $precio * $cantidad * 0.21;
            
                    $sql_detalle = "INSERT INTO ingreso_productosx(ingreso_id, producto_id, cantidad, precio, iva, peso_kg) VALUES (?, ?, ?, ?, ?, ?)";
                    if ($stmt_detalle = $conn->prepare($sql_detalle)) {
                        $stmt_detalle->bind_param("iiiddd", $ingreso_id, $producto_id, $cantidad, $precio, $iva_producto, $peso_kg);
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
    let productoCount = 1;
    const productos = <?php echo json_encode($productos_data); ?>;

    // Función para agregar un nuevo producto al formulario
   // Función para agregar un nuevo producto al formulario
function agregarProducto() {
    productoCount++;

    // Crear el div para el nuevo producto
    const productoDiv = document.createElement('div');
    productoDiv.classList.add('producto-item');
    productoDiv.id = `producto-item-${productoCount}`; // Añadimos un ID único para cada nuevo producto
    
    // Crear las opciones de productos dinámicamente
    let optionsHTML = '<option value="">Seleccione un Producto</option>';
    for (const [id, nombre] of Object.entries(productos)) {
        optionsHTML += `<option value="${id}">${nombre}</option>`;
    }

    // Rellenar el HTML de los nuevos campos
    productoDiv.innerHTML = ` 
        <div class="mb-3">
            <label for="producto_${productoCount}" class="form-label">Producto ${productoCount}</label>
            <select name="productos[${productoCount}]" class="form-control" id="producto_${productoCount}" required>
                ${optionsHTML}
            </select>
            <input type="number" name="cantidad[${productoCount}]" class="form-control mt-2" id="cantidad_${productoCount}" placeholder="Cantidad" min="1" required oninput="calcularTotal(${productoCount})">
            <input type="number" name="precio[${productoCount}]" class="form-control mt-2" id="precio_${productoCount}" placeholder="Precio" required oninput="calcularTotal(${productoCount})">
            <input type="number" name="peso_kg[${productoCount}]" class="form-control mt-2" id="peso_kg_${productoCount}" placeholder="Peso en kg" step="0.01" oninput="calcularTotal(${productoCount})">
            <input type="text" name="total[${productoCount}]" class="form-control mt-2" id="total_${productoCount}" placeholder="Total Producto" readonly>
        </div>
    `;

    // Añadir el nuevo campo de producto al formulario
    document.getElementById('productos-container').appendChild(productoDiv);
}

    // Función para calcular el total del producto
    function calcularTotal(index) {
        const productoSelect = document.getElementById('producto_' + index);
        const cantidadInput = document.getElementById('cantidad_' + index);
        const precioInput = document.getElementById('precio_' + index);
        const totalInput = document.getElementById('total_' + index);

        const precio = parseFloat(precioInput.value) || 0;
        const cantidad = parseInt(cantidadInput.value) || 0;
        const total = precio * cantidad;

        // Mostrar el total del producto sin el símbolo $ (solo el número)
        totalInput.value = total.toFixed(2);

        // Llamar a la función que calcula el monto total general
        calcularMontoTotal();
    }

    // Función para calcular el monto total de todos los productos
    function calcularMontoTotal() {
        let total = 0;
        const totalInputs = document.querySelectorAll('input[name^="total"]');
        totalInputs.forEach(function(input) {
            // Sumar los valores de cada total de producto
            total += parseFloat(input.value) || 0;
        });

        // Mostrar el monto total general con el símbolo $
        document.getElementById('monto').value = `$${total.toFixed(2)}`;
    }
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
    <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Ingreso X</h2>

        <form method="POST">
    <div class="row">
        <!-- Columna izquierda con los primeros campos -->
        <div class="col-md-6">
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="vencimiento" class="form-label">Vencimiento</label>
                <input type="date" name="vencimiento" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="tipo_ingreso" class="form-label">Tipo de Ingreso</label>
                <select name="tipo_ingreso" class="form-control" required>
                    <option value="presupuesto">Presupuesto</option>
                    <option value="venta">Venta</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="monto" class="form-label">Monto Total</label>
                <input type="text" name="monto" class="form-control" id="monto" readonly>
            </div>

            <div id="productos-container"></div>

            <button type="button" id="agregar-producto" class="btn btn-secondary mt-2" onclick="agregarProducto()">+ Agregar Producto</button>
        </div>

        <!-- Columna derecha con los siguientes campos -->
        <div class="col-md-6">
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

            <div class="mb-3">
                <label for="cliente" class="form-label">Cliente</label>
                <select name="cliente" class="form-control" required>
                    <option value="">Seleccione un Cliente</option>
                    <?php while ($cliente = $clientes->fetch_assoc()) : ?>
                        <option value="<?php echo $cliente['id']; ?>">
                            <?php echo $cliente['cliente']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="tipo_factura" class="form-label">Tipo de Factura</label>
                <select name="tipo_factura" class="form-control" required>
                    <option value="x">Factura X </option>
                </select>
            </div>

            <div class="mb-3">
                <label for="cuenta" class="form-label">Cuenta</label>
                <select name="cuenta" class="form-control" required>
                    <option value="">Seleccione una Cuenta</option>
                    <?php while ($cuenta = $cuentas->fetch_assoc()) : ?>
                        <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                            <?php echo $cuenta['Cuenta']; ?>
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
                <input type="hidden" name="empleado_responsable" value="<?php echo $_SESSION['usuario']; ?>">
                <input type="text" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
            </div>

            <!-- Estado -->
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" class="form-control" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="vencido">Vencido</option>
                    <option value="facturado">Facturado</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="vendedor_nombre" class="form-label">Vendedor</label>
                <input type="text" name="vendedor_nombre" class="form-control" id="vendedor_nombre" placeholder="Nombre del Vendedor" required>
            </div>

            <!-- Métodos de Pago -->
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>

            <!-- Métodos de Transporte -->
            <div class="mb-3">
                <label for="metodo_transporte" class="form-label">Método de Transporte</label>
                <select name="metodo_transporte" class="form-control" required>
                    <option value="recojo">Recojo</option>
                    <option value="envio">Envío</option>
                </select>
            </div>

            <!-- Botón para guardar -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
            </div>
        </div>
    </div>
</form>

    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</html>
