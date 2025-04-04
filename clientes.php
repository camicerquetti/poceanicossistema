<?php
ob_start();
// Incluir la clase Cliente y la conexión
include('config.php');
include('clase/Cliente.php');

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

        // Crear una instancia de Cliente
        $cliente = new Cliente($conn);

        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Asignar cada valor de la línea a una variable
            $cliente_nombre = $data[0];
            $cliente_apellido = $data[1];
            $cliente_email = $data[2];
            $cliente_telefono = $data[3];
            $cliente_telefono2 = $data[4];
            $cliente_direccion = $data[5];
            $cliente_localidad = $data[6];
            $cliente_provincia = $data[7];
            $cliente_dni = $data[8];
            $cliente_cuit = $data[9];
            $cliente_condicion_iva = $data[10];
            $cliente_razon_social = $data[11];
            $cliente_domicilio_fiscal = $data[12];
            $cliente_localidad_fiscal = $data[13];
            $cliente_provincia_fiscal = $data[14];
            $cliente_codigo_postal_fiscal = $data[15];
            $cliente_pagina_web = $data[16];
            $cliente_saldo_inicial = $data[17];
            $cliente_observaciones = $data[18];

            // Insertar el cliente en la base de datos
            $cliente->insertarCliente(
                $cliente_nombre,
                $cliente_apellido,
                $cliente_email,
                $cliente_telefono,
                $cliente_telefono2,
                $cliente_direccion,
                $cliente_localidad,
                $cliente_provincia,
                $cliente_dni,
                $cliente_cuit,
                $cliente_condicion_iva,
                $cliente_razon_social,
                $cliente_domicilio_fiscal,
                $cliente_localidad_fiscal,
                $cliente_provincia_fiscal,
                $cliente_codigo_postal_fiscal,
                $cliente_pagina_web,
                $cliente_saldo_inicial,
                $cliente_observaciones
            );
        }

        // Cerrar el archivo CSV
        fclose($handle);

        // Redirigir a la página de lista de clientes después de la importación
        header('Location: lista_clientes.php');
        exit();
    } else {
        die('Error al abrir el archivo CSV.');
    }
} else {
    // Si no se sube archivo, mostrar un mensaje de error
    $error_message = "No se ha seleccionado un archivo CSV o ocurrió un error en la carga.";
}

// Crear una instancia de Cliente
$cliente = new Cliente($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de clientes por página
$clientes_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $clientes_por_pagina;

// Obtener los clientes filtrados con paginación
$clientes = $cliente->obtenerClientes($filtro, $clientes_por_pagina, $offset);

// Obtener el total de clientes para la paginación
$total_clientes = $cliente->contarClientes($filtro);
$total_paginas = ceil($total_clientes / $clientes_por_pagina);
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
    <title>Lista de Clientes</title>
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
    padding:40px;
}

    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Clientes</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por nombre, apellido, o email" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>
        <button class="btn btn-success" onclick="window.location.href='nuevo-cliente.php'">+ Cliente</button>
        <a id="exportExcelBtn" class="btn btn-success" href="exportar_clientes_excel.php">Exportar a Excel</a>

       
        <!-- Botón verde para mostrar el formulario de importación -->
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Cliente</button>
        

        <!-- Formulario de importación de clientes -->
        <div id="importarForm" class="mt-3">
            <h3>Importar Clientes desde CSV</h3>
            <form action="importar_clientes.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Selecciona un archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir CSV</button>
            </form>
        </div>
        

        <!-- Tabla de clientes -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>DNI</th>
                    <th>Saldo Inicial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $clientes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['cliente'] . ' ' . $row['nombre'] . ' ' . $row['apellido']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['telefono']; ?></td>
                        <td><?php echo $row['dni']; ?></td>
                        <td><?php echo $row['saldo_inicial']; ?></td>
                        <td>
                            <a href="editar_cliente.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_cliente.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginación -->
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
