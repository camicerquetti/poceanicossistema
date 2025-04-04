<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Producto y la conexión
include('config.php');
include('clase/Producto.php');

// Crear una instancia de Producto
$producto = new Producto($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de productos por página
$productos_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $productos_por_pagina;

// Obtener los productos filtrados con paginación
$productos = $producto->obtenerProductos($filtro, $productos_por_pagina, $offset);

// Obtener el total de productos para la paginación
$total_productos = $producto->contarProductos($filtro);
$total_paginas = ceil($total_productos / $productos_por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px; /* Contenedor más pequeño */
            margin-left: 300px;
            margin-right: auto;
        }

        table {
            margin-top: 20px;
        }

        body {
            padding-top: 60px;
        }
           /* Centrar la paginación debajo de la tabla */
           .pagination {
            justify-content: center; /* Centra los elementos de la paginación */
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Productos</h2>

        <!-- Formulario de filtro -->
        <form method="POST">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar por nombre o código" value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
            <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
        </form>
    
        <button class="btn btn-success" onclick="window.location.href='nuevo-producto.php'">+ Producto</button>
        <a id="exportExcelBtn" class="btn btn-success" href="exportar_productos_excel.php">Exportar a Excel</a>
        <button class="btn btn-success" onclick="toggleImportarForm()">Importar Producto</button>

        <!-- Formulario de importación -->
        <div id="importarForm" class="mt-3" style="display:none;">
            <h3>Importar Productos desde CSV</h3>
            <form action="importar_productos.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Selecciona un archivo CSV</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                </div>
                <button type="submit" class="btn btn-primary">Subir CSV</button>
            </form>
        </div>



        <!-- Tabla de productos -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Proveedor</th>
                    <th>Código</th>
                    <th>Stock Total</th>
                    <th>Costo</th>
                    <th>Precio de Venta</th>
                    <th>IVA Compras</th>
                    <th>IVA Ventas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $productos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['Nombre']; ?></td>
                        <td><?php echo $row['Tipo']; ?></td>
                        <td><?php echo $row['Proveedor']; ?></td>
                        <td><?php echo $row['Codigo']; ?></td>
                        <td><?php echo $row['Stock_Total']; ?></td>
                        <td><?php echo $row['Costo']; ?></td>
                        <td><?php echo $row['Precio_de_Venta']; ?></td>
                        <td><?php echo $row['IVA_Compras']; ?></td>
                        <td><?php echo $row['IVA_Ventas']; ?></td>
                        <td>
    <div class="d-flex">
        <a href="editar_producto.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm me-2">Editar</a>
        <a href="eliminar_producto.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
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
