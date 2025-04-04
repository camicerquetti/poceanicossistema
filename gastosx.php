<?php
ob_start();
// Incluir la clase Gasto y la conexión
include('config.php');
include('clase/Gastosx.php');

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
            $estado = $data[5];  // El estado es 'A Pagar' o 'Pagado' según el CSV

            // Insertar el gasto en la base de datos
            $gasto->insertarGasto(
                $fecha, $descripcion, $categoria, $metodo_pago, $monto, $estado
            );
        }

        // Cerrar el archivo CSV
        fclose($handle);

        // Redirigir a la página de lista de gastos después de la importación
        header('Location: gastosx.php');
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
    <title>Lista de Gastos X</title>
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

.estado-amarillo {
    background-color: yellow !important;
    color: black !important;
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
        <h2>Lista de Gastos</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
    <div class="input-group">
        <select name="campo" class="form-select">
            <option value="todos" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="fecha" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'fecha') ? 'selected' : ''; ?>>Fecha</option>
            <option value="proveedor" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
            <option value="descripcion" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'descripcion') ? 'selected' : ''; ?>>Descripción</option>
            <option value="categoria" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'categoria') ? 'selected' : ''; ?>>Categoría</option>
            <option value="monto" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'monto') ? 'selected' : ''; ?>>Monto</option>
            <option value="estado" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'estado') ? 'selected' : ''; ?>>Estado</option>
            <option value="metodo_pago" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'metodo_pago') ? 'selected' : ''; ?>>Método de Pago</option>
        </select>
        <input type="text" name="filtro" class="form-control" placeholder="Buscar..." value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>

        <!-- Botón verde para mostrar el formulario de importación -->
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Gasto</button>
        <button class="btn btn-success" onclick="window.location.href='nuevo_gasto_x.php';">Nuevo Gasto</button>

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
                                         
                        <td class="<?php 
    $estado = trim($row['estado']); // Elimina espacios en blanco alrededor del texto
    echo ($estado == 'a_pagar') ? 'estado-rojo' : 
         (($estado == 'pagado') ? 'estado-verde' : 
         (($estado == 'A Cobrar') ? 'estado-amarillo' : ''));
?>">
    <?php echo $estado; ?>
</td>

                        <td>
                            <a href="editar_gastos_x.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
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
    <script>
        // Función para mostrar/ocultar el formulario
        function toggleImportarForm() {
            var form = document.getElementById("importarForm");
            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
