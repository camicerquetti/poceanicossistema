<?php
ob_start();
include('config.php');
include('clase/Ingreso.php');

// Obtener lista de productos y proveedores
$productos = $conn->query("SELECT * FROM producto");
$proveedores = $conn->query("SELECT * FROM proveedores");
$cuentas = $conn->query("SELECT * FROM cuentas");

// Verificar si la consulta se ejecutó correctamente
if ($cuentas->num_rows > 0) {
    $cuentasArray = [];
    while ($cuenta = $cuentas->fetch_assoc()) {
        $cuentasArray[] = $cuenta;
    }
} else {
    $cuentasArray = [];  // Si no hay cuentas, devolvemos un arreglo vacío
}

// Verificar si hay resultados
if ($proveedores->num_rows > 0) {
    $proveedoresArray = [];
    while ($proveedor = $proveedores->fetch_assoc()) {
        $proveedoresArray[] = $proveedor;
    }
} else {
    $proveedoresArray = [];  // Si no hay proveedores, devolvemos un arreglo vacío
}

// Convertir los productos a un array PHP
$productosArray = [];
while ($producto = $productos->fetch_assoc()) {
    $productosArray[] = $producto;
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $fecha = $_POST['fecha'];
    $vencimiento = $_POST['vencimiento']; // Captura la fecha de vencimiento

    $proveedor = $_POST['proveedor'];
    $estado = $_POST['estado']; // Estado (Por pagar o Pagado)
    $metodo_pago = $_POST['metodo_pago'];
    $categoria_pago = $_POST['categoria_pago'];
    $descripcion = $_POST['descripcion'];
    $vendedor = $_POST['vendedor']; // Asumiendo que el nombre del usuario está en la sesión
    $total_calculado = $_POST['total']; // Recibimos el total calculado
    $cuenta = $_POST['cuenta']; // Capturar la cuenta seleccionada


    // Calcular el subtotal, IVA y total
    $subtotal = 0;
    $iva = 0;
    $productos_seleccionados = [];
    $query_compra = "INSERT INTO compras (emision, proveedor, categoria, subtotal, iva, total, estado, metodo_pago, descripcion, vendedor, cuenta, vencimiento)
    VALUES ('$fecha', '$proveedor', '$categoria_pago', 0, 0, 0, '$estado', '$metodo_pago', '$descripcion', '$vendedor', '$cuenta', '$vencimiento')";
    
    // Primero, insertar la compra en la base de datos (tabla compras)
    

    if ($conn->query($query_compra) === TRUE) {
        // Obtener el ID de la compra insertada
        $compra_id = $conn->insert_id;  // Este es el ID que necesitamos para insertar en productos_compra

        // Insertar productos en la tabla productos_compras
        foreach ($_POST['productos'] as $index => $producto_id) {
            $cantidad = $_POST['cantidad'][$index];
            $precio = $_POST['precio'][$index];
            $peso = $_POST['peso'][$index];

            // Validar que el producto_id sea un número válido
            if (is_numeric($producto_id)) {
                // Ejecuta la consulta para obtener el producto
                $producto_resultado = $conn->query("SELECT * FROM producto WHERE id = $producto_id");

                // Verificar si el producto existe
                if ($producto_resultado && $producto_resultado->num_rows > 0) {
                    $producto = $producto_resultado->fetch_assoc();
                    $precio_unitario = $producto['Costo']; // Asegúrate de usar la columna 'Costo' que es donde está el precio
                    $subtotal += $precio_unitario * $cantidad;
                    $productos_seleccionados[] = [
                        'producto' => $producto['Nombre'],
                        'cantidad' => $cantidad,
                        'precio' => $precio_unitario,
                        'total' => $precio_unitario * $cantidad
                    ];

                    // Insertar en la tabla productos_compras usando el ID de la compra
                    $query_producto_compras = "INSERT INTO productos_compra (compra_id, producto_id, cantidad, precio_unitario, peso_kg)
                                               VALUES ($compra_id, $producto_id, $cantidad, $precio_unitario, $peso)";
                    $conn->query($query_producto_compras);
                }
            }
        }

        // Calcular IVA (21%)
        $iva = $subtotal * 0.21;

        // Total con IVA
        $total = $subtotal + $iva;

        // Actualizar la tabla compras con el subtotal, iva y total calculados
        $query_actualizar_compra = "UPDATE compras SET subtotal = $subtotal, iva = $iva, total = $total WHERE id = $compra_id";
        $conn->query($query_actualizar_compra);

        echo "Compra insertada correctamente y productos asociados.";
    } else {
        echo "Error al insertar la compra: " . $conn->error;
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
        <h2>Nueva Compra</h2>

        <form method="POST">
            <!-- Otros campos del formulario -->
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha de la Compra</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
            </div>
            <div class="mb-3">
    <label for="vencimiento" class="form-label">Fecha de Vencimiento</label>
    <input type="date" name="vencimiento" id="vencimiento" class="form-control" required>
</div>

            <div class="mb-3">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select name="proveedor" id="proveedor" class="form-control" required>
                    <option value="">Seleccione un Proveedor</option>
                    <?php
                    // Recorrer los proveedores y generar las opciones
                    foreach ($proveedoresArray as $proveedor) {
                        echo "<option value='" . $proveedor['id'] . "'>" . $proveedor['proveedor'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="a_cobrar"> a cobrar</option>
                    <option value="cobrado">cobrado</option>
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
    <input type="text" name="categoria_pago" class="form-control" required placeholder="Escriba la categoría de pago">
</div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>

            <!-- Vendedor (Nombre del usuario en la sesión) -->
            <div class="mb-3">
                <label for="vendedor" class="form-label">Vendedor</label>
                <input type="text" name="vendedor" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
            </div>
            
            <div class="mb-3">
    <label for="cuenta" class="form-label">Cuenta</label>
    <select name="cuenta" class="form-control" required>
        <option value="">Seleccione una Cuenta</option>
        <?php 
        // Usamos el array $cuentasArray que ya contiene los datos de las cuentas
        foreach ($cuentasArray as $cuenta) : ?>
            <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                <?php echo $cuenta['Cuenta']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>



            <!-- Productos -->
            <div id="productos-container"></div>

            <!-- Monto Total -->
            <div class="mb-3">
                <label for="total" class="form-label">Total de la Compra</label>
                <input type="text" name="total" id="total" class="form-control" readonly>
            </div>

           <!-- Fila para los botones -->
<div class="row">
    <!-- Columna para el primer botón (Agregar Producto) -->
    <div class="col-auto mb-3">
        <button type="button" id="agregar-producto" class="btn btn-secondary">Agregar Producto</button>
    </div>

  <!-- Columna para el segundo botón (Guardar Compra) -->
  <div class="col-auto mb-3 mt-0"> <!-- Ajuste el mt-0 para quitar el margen superior -->
        <button type="submit" class="btn btn-primary">Guardar Compra</button>
    </div>
</div>

        </form>
    </div>

    <script>
        const productos = <?php echo json_encode($productosArray); ?>;
        let totalCompra = 0;

        function agregarProducto(index) {
            const productItem = document.createElement('div');
            productItem.classList.add('producto-item');
            let options = '<option value="">Seleccione un Producto</option>';
            productos.forEach(producto => {
                options += `<option value="${producto.id}" data-nombre="${producto.Nombre}" data-peso="${producto.Peso}">${producto.Nombre}</option>`;
            });

            productItem.innerHTML = ` 
                <select name="productos[${index}]" class="form-control" id="producto_${index}" required onchange="actualizarPrecioYPeso(${index})">
                    ${options}
                </select>
                <input type="number" name="cantidad[${index}]" class="form-control mt-2" id="cantidad_${index}" placeholder="Cantidad" min="1" required oninput="actualizarPrecioYPeso(${index})">
                <input type="number" name="precio[${index}]" class="form-control mt-2" id="precio_${index}" placeholder="Precio" step="0.01" min="0" required oninput="actualizarPrecioYPeso(${index})">
                <input type="number" name="peso[${index}]" class="form-control mt-2" id="peso_${index}" placeholder="Peso (kg)" required oninput="actualizarPrecioYPeso(${index})">
            `;
            document.getElementById('productos-container').appendChild(productItem);
        }

        function actualizarPrecioYPeso(index) {
            const cantidadInput = document.getElementById('cantidad_' + index);
            const precioInput = document.getElementById('precio_' + index);
            const pesoInput = document.getElementById('peso_' + index);

            const cantidad = parseInt(cantidadInput.value) || 0;
            const precioUnitario = parseFloat(precioInput.value) || 0;
            const peso = parseFloat(pesoInput.value) || 0;

            if (cantidad > 0 && precioUnitario > 0) {
                actualizarTotalCompra();
            }
        }

        function actualizarTotalCompra() {
            totalCompra = 0;
            const productosItems = document.querySelectorAll('.producto-item');
            productosItems.forEach((productoItem) => {
                const cantidad = parseInt(productoItem.querySelector('input[name^="cantidad"]').value) || 0;
                const precio = parseFloat(productoItem.querySelector('input[name^="precio"]').value) || 0;
                const precioTotal = cantidad * precio;
                totalCompra += precioTotal;
            });

            document.getElementById('total').value = '$' + totalCompra.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            agregarProducto(1);
        });

        let productCount = 1;

        document.getElementById('agregar-producto').addEventListener('click', function () {
            productCount++;
            agregarProducto(productCount);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
