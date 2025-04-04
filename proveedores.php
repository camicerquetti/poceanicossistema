<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Proveedor y la conexión
include('config.php');
include('clase/Proveedor.php');

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

        // Crear una instancia de Proveedor
        $proveedor = new Proveedor($conn);

        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Asignar cada valor de la línea a una variable
            $proveedor_nombre = $data[0];
            $proveedor_razon_social = $data[1];
            $proveedor_email = $data[2];
            $proveedor_telefono = $data[3];
            $proveedor_direccion = $data[4];
            $proveedor_localidad = $data[5];
            $proveedor_provincia = $data[6];
            $proveedor_cuit = $data[7];
            $proveedor_condicion_iva = $data[8];
            $proveedor_observaciones = $data[9];
            $proveedor_saldo_inicial = $data[10]; // Nueva columna

            // Insertar el proveedor en la base de datos
            $proveedor->insertarProveedor(
                $proveedor_nombre,
                $proveedor_razon_social,
                $proveedor_email,
                $proveedor_telefono,
                $proveedor_direccion,
                $proveedor_localidad,
                $proveedor_provincia,
                $proveedor_cuit,
                $proveedor_condicion_iva,
                $proveedor_observaciones,
                $proveedor_saldo_inicial // Nueva columna
            );
        }

        // Cerrar el archivo CSV
        fclose($handle);

        // Redirigir a la página de lista de proveedores después de la importación
        header('Location: lista_proveedores.php');
        exit();
    } else {
        die('Error al abrir el archivo CSV.');
    }
} else {
    // Si no se sube archivo, mostrar un mensaje de error
    $error_message = "No se ha seleccionado un archivo CSV o ocurrió un error en la carga.";
}

// Crear una instancia de Proveedor
$proveedor = new Proveedor($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de proveedores por página
$proveedores_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $proveedores_por_pagina;

// Obtener los proveedores filtrados con paginación
$proveedores = $proveedor->obtenerProveedores($filtro, $proveedores_por_pagina, $offset);

// Obtener el total de proveedores para la paginación
$total_proveedores = $proveedor->contarProveedores($filtro);
$total_paginas = ceil($total_proveedores / $proveedores_por_pagina);
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
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let exportBtn = document.getElementById("exportExcelBtn");
        if (exportBtn) {
            exportBtn.addEventListener("click", function (event) {
                event.preventDefault(); // Evita que el enlace recargue la página
                window.location.href = "exportar_proveedores_excel.php"; // Redirige manualmente
            });
        }
    });
</script>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Proveedores</title>
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
            padding: 40px;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Proveedores</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por nombre, razón social o email" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>
        <button class="btn btn-success" onclick="window.location.href='nuevo-proveedor.php'">+ Proveedor</button>

       
<!-- Botón para exportar a Excel -->
<!-- Botón para exportar a CSV -->
<a id="exportExcelBtn" class="btn btn-success" href="exportar_proveedores_excel.php">Exportar a Excel</a>
 <!-- Botón verde para mostrar el formulario de importación -->
 <button class="btn btn-success" onclick="toggleImportarForm()">Importar Proveedor</button>



<!-- Formulario de importación de proveedores -->
        <div id="importarForm" class="mt-3">
            <h3>Importar Proveedores desde CSV</h3>
            <form action="importar_proveedores.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Selecciona un archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir CSV</button>
            </form>
        </div>

        <!-- Tabla de proveedores -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>CUIT</th>
                    <th>Razón Social</th>  <!-- Nueva columna -->
                    <th>Saldo Inicial</th>  <!-- Nueva columna -->
                    <th>Condición IVA</th>  <!-- Nueva columna -->
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $proveedores->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['proveedor'] . ' ' . $row['razon_social']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['telefono']; ?></td>
                        <td><?php echo $row['cuit']; ?></td>
                        <td><?php echo $row['razon_social']; ?></td> <!-- Mostrar Razón Social -->
                        <td><?php echo $row['saldo_inicial']; ?></td> <!-- Mostrar Saldo Inicial -->
                        <td><?php echo $row['condicion_iva']; ?></td> <!-- Mostrar Condición IVA -->
                        <td><?php echo $row['observaciones']; ?></td>
                        <td>
    <div class="d-flex">
        <a href="editar_proveedor.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm me-2">Editar</a>
        <a href="eliminar_proveedor.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">Eliminar</a>
    </div>
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
