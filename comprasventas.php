<?php
ob_start();  // Comienza el almacenamiento en búfers
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

            // Insertar la compra en la base de datos
            $compra->insertarCompra(
                $emision, $vencimiento, $proveedor, $categoria, $subtotal, $descuento,
                $cantidad, $total, $vencimientoPago, $tipoCompra, $producto, $precio, $iva,
                $notaInterna, $contador
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
        /* Estilo para que la paginación tenga el mismo ancho que la tabla */
        .pagination {
            width: 100%;
            justify-content: center;  /* Centra la paginación */
        }

        .container {
            max-width: 1000px;  /* El contenedor de la tabla y la paginación */
            margin-left: 300px;
            margin-right: auto;
          padding:60px;
        }
        
    </style>
</head>
<body>
    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Compras</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por proveedor, producto o categoría" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>

        <!-- Botón verde para mostrar el formulario de importación -->
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Compra</button>
        <button class="btn btn-success" onclick="window.location.href='nueva_compra_ventas.php';">Nueva Compra</button>

      

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
            <th>Proveedor</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Tipo de Compra</th>
            <th>Vendedor</th> <!-- Nueva columna para el vendedor -->
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $compras->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['proveedor']; ?></td>
                <td><?php echo $row['producto']; ?></td>
                <td><?php echo $row['cantidad']; ?></td>
                <td><?php echo '$' . number_format($row['total'], 2); ?></td>
                <td><?php echo $row['tipoCompra']; ?></td>
                <td><?php echo $row['vendedor']; ?></td> <!-- Mostrar vendedor -->
                <td>
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
                // Número de páginas a mostrar alrededor de la página actual
                $rango = 2;

                // Determinar el rango de páginas a mostrar
                $inicio = max(1, $paginacion - $rango);
                $fin = min($total_paginas, $paginacion + $rango);

                // Mostrar los números de página
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
    <script>
    // Función para mostrar/ocultar el formulario
    function toggleImportarForm() {
        var form = document.getElementById("importarForm");
        
        // Verifica si el formulario está visible, y alterna su estado
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";  // Muestra el formulario
        } else {
            form.style.display = "none";   // Oculta el formulario
        }
    }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
