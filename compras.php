<?php
ob_start();
// Incluir la clase Compra y la conexión
include('config.php');
include('clase/Compra.php');

// Verificar si se ha enviado el archivo CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    // Obtener la ruta temporal del archivo subido
    $csv_file = $_FILES['csv_file']['tmp_name'];

    // Verificar si el archivo es un archivo CSV
    $file_extension = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);
    if ($file_extension !== 'csv') {
        die('Solo se permiten archivos CSV.');
    }

    // Abrir el archivo CSV para leerlo
    if (($handle = fopen($csv_file, 'r')) !== false) {
        // Saltar la primera línea si tiene encabezados
        fgetcsv($handle);

        // Crear una instancia de Compra
        $compra = new Compra($conn);

        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Asignar cada valor de la línea a una variable
            $emision = $data[0];
            $vencimiento = $data[1];
            $proveedor = $data[2];
            $categoria = $data[3];
            $subtotal = $data[4];
            $descuento = $data[5];
            $cantidad = $data[6];
            $total = $data[7];
            $vencimientoPago = $data[8];
            $tipoCompra = $data[9];
            $producto = $data[10];
            $precio = $data[11];
            $iva = $data[12];
            $notaInterna = $data[13];
            $contador = $data[14];

            // Lógica para determinar el estado
            $estado = (strtotime($vencimientoPago) < time()) ? 'Pagado' : 'A Pagar';

            // Insertar la compra en la base de datos
            $compra->insertarCompra(
                $emision, $vencimiento, $proveedor, $categoria, $subtotal, $descuento,
                $cantidad, $total, $vencimientoPago, $tipoCompra, $producto, $precio, $iva,
                $notaInterna, $contador, $estado
            );
        }

        // Cerrar el archivo CSV
        fclose($handle);

        // Redirigir a la página de lista de compras después de la importación
        header('Location: lista_compras.php');
        exit();
    } else {
        die('Error al abrir el archivo CSV.');
    }
} else {
    // Si no se sube archivo, mostrar un mensaje de error
    $error_message = "No se ha seleccionado un archivo CSV o ocurrió un error en la carga.";
}

// Crear una instancia de Compra
$compra = new Compra($conn);

// Manejar filtro
$campo = isset($_POST['campo']) ? $_POST['campo'] : 'todos';
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';


// Obtener el número de compras por página
$compras_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $compras_por_pagina;

// Obtener las compras filtradas con paginación
$compras = $compra->obtenerCompras($filtro, $compras_por_pagina, $offset);

// Obtener el total de compras para la paginación
$total_compras = $compra->contarCompras($filtro);
$total_paginas = ceil($total_compras / $compras_por_pagina);


?>

<script>
    // Función para mostrar u ocultar el formulario de importación
    function toggleImportarForm() {
        var form = document.getElementById("importarForm");
        // Cambiar el estilo de display entre 'none' y 'block'
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";  // Mostrar el formulario
        } else {
            form.style.display = "none";  // Ocultar el formulario
        }
    }
</script>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pagination {
            width: 100%;
            justify-content: center;
        }

        .container {
            max-width: 1000px;
            margin-left: 300px;
            margin-right: auto;
            padding: 60px;
        }
        .estado-rojo {
    background-color: red !important;
    color: white !important;
    font-weight: bold;
    text-align: center;
}

.estado-verde {
    background-color: green !important;
    color: white !important;
    font-weight: bold;
    text-align: center;
}

    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Compras</h2>
<!-- Formulario de filtro -->
<form method="POST">
    <div class="input-group">
        <select name="campo" class="form-select">
            <option value="todos" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="emision" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'emision') ? 'selected' : ''; ?>>Emisión</option>
            <option value="vencimiento" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'vencimiento') ? 'selected' : ''; ?>>Vencimiento</option>
            <option value="proveedor" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
            <option value="categoria" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'categoria') ? 'selected' : ''; ?>>Categoría</option>
            <option value="total" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'total') ? 'selected' : ''; ?>>Total</option>
            <option value="descripcion" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'descripcion') ? 'selected' : ''; ?>>Descripción</option>
            <option value="estado" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'estado') ? 'selected' : ''; ?>>Estado</option>
            <option value="metodo_pago" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'metodo_pago') ? 'selected' : ''; ?>>Método de Pago</option>
        </select>
        <input type="text" name="filtro" class="form-control" placeholder="Buscar..." value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>


        <!-- Botón para mostrar el formulario de importación -->
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Compra</button>
        <button class="btn btn-success" onclick="window.location.href='nueva-compra.php';">Nueva Compra</button>

        <!-- Formulario de importación de compras -->
        <div id="importarForm" class="mt-3">
            <h3 class="import">Importar Compras desde CSV</h3>
            <form action="importar_compras.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Selecciona un archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir CSV</button>
            </form>
        </div>

        <!-- Tabla de compras -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>emision</th>
                    <th>vencimiento</th>
                    <th>proveedor</th>
                    <th>Total</th>
                    <th>categoria</th>
                    <th>descripcion</th>
                    <th>Estado</th> <!-- Nueva columna para el estado -->
                    <th>metodo_cobro</th> <!-- Nueva columna para el estado -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $compras->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['emision']; ?></td>
                        <td><?php echo $row['vencimiento']; ?></td>
                        <td><?php echo $row['proveedor']; ?></td>
                        <td><?php echo '$' . number_format($row['total'], 2); ?></td>
                        <td><?php echo $row['categoria']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                      
                        <td class="
    <?php 
        $estado = trim($row['estado']); // Elimina espacios en blanco
        if ($estado == 'a_cobrar') echo 'estado-rojo';
        elseif ($estado == 'cobrado') echo 'estado-verde';
    ?>
">
    <?php echo $estado; ?>
</td>

                        <td><?php echo $row['metodo_pago']; ?></td>
                        <td style="display: flex; gap: 5px;">
    <a href="editar_compra.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
    <a href="eliminar_compra.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta compra?');">Eliminar</a>
</td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav aria-label="Paginación">
            <ul class="pagination mt-3">
                <li class="page-item <?php echo $paginacion <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion - 1; ?>">Anterior</a>
                </li>

                <?php
                $rango = 2;
                $inicio = max(1, $paginacion - $rango);
                $fin = min($total_paginas, $paginacion + $rango);

                for ($i = $inicio; $i <= $fin; $i++) :
                ?>
                    <li class="page-item <?php echo $i == $paginacion ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $paginacion >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $paginacion + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
