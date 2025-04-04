<?php
ob_start();
// Incluir la clase Gasto y la conexión
include('config.php');
include('clase/Gasto.php');

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

        // Crear una instancia de Gasto
        $gasto = new Gasto($conn);

        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Asignar cada valor de la línea a una variable
            $fecha = $data[0];
            $descripcion = $data[1];
            $categoria = $data[2];
            $metodo_pago = $data[3];
            $monto = $data[4];
            $estado = $data[5];

            // Insertar el gasto en la base de datos
            $gasto->insertarGasto(
                $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado
            );
        }

        // Cerrar el archivo CSV
        fclose($handle);

        // Redirigir a la página de lista de gastos después de la importación
        header('Location: gastosventas.php');
        exit();
    } else {
        die('Error al abrir el archivo CSV.');
    }
} else {
    // Si no se sube archivo, mostrar un mensaje de error
    $error_message = "No se ha seleccionado un archivo CSV o ocurrió un error en la carga.";
}

// Crear una instancia de Gasto
$gasto = new Gasto($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de gastos por página
$gastos_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $gastos_por_pagina;

// Obtener los gastos filtrados con paginación
$gastos = $gasto->obtenerGastos($filtro, $gastos_por_pagina, $offset);

// Obtener el total de gastos para la paginación
$total_gastos = $gasto->contarGastos($filtro);
$total_paginas = ceil($total_gastos / $gastos_por_pagina);
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
    <title>Lista de Gastos</title>
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
            padding: 60px;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Gastos</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por descripción, categoría o estado" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>

        <!-- Botón verde para mostrar el formulario de importación -->
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Gasto</button>
        <button class="btn btn-success" onclick="window.location.href='nuevo_gasto_ventas.php';">Nuevo Gasto</button>

        <!-- Formulario de importación de gastos -->
        <div id="importarForm" class="mt-3">
            <h3 class="import">Importar Gastos desde CSV</h3>
            <form action="importar_gastos.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Selecciona un archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir CSV</button>
            </form>
        </div>

        <!-- Tabla de gastos -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $gastos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['fecha']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo $row['categoria']; ?></td>
                        <td><?php echo $row['monto']; ?></td>
                        <td><?php echo $row['estado']; ?></td>
                        <td>
                            <a href="editar_gasto.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_gasto.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este gasto?');">Eliminar</a>
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
