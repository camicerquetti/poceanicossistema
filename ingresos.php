<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Ingreso y la conexión
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener las fechas de inicio y fin (si se proporcionan)
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

// Calcular la condición de fecha
$condicion_fecha = '';
if ($fecha_inicio && $fecha_fin) {
    // Si ambas fechas están presentes
    $condicion_fecha = "AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
} elseif ($fecha_inicio) {
    // Si solo hay una fecha de inicio
    $condicion_fecha = "AND fecha >= '$fecha_inicio'";
} elseif ($fecha_fin) {
    // Si solo hay una fecha de fin
    $condicion_fecha = "AND fecha <= '$fecha_fin'";
}

// Obtener el número de ingresos por página
$ingresos_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $ingresos_por_pagina;

// Obtener los ingresos filtrados con paginación y fechas
$sql = "SELECT * FROM ingresos WHERE (tipo_ingreso LIKE ? OR cliente LIKE ?) $condicion_fecha LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $filtro, $filtro, $offset, $ingresos_por_pagina);
$stmt->execute();
$ingresos = $stmt->get_result();

// Obtener el total de ingresos para la paginación con las fechas
$sql_total = "SELECT COUNT(*) FROM ingresos WHERE (tipo_ingreso LIKE ? OR cliente LIKE ?) $condicion_fecha";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('ss', $filtro, $filtro);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_ingresos = $result_total->fetch_row()[0];
$total_paginas = ceil($total_ingresos / $ingresos_por_pagina);
?>

<style>
    .pagination {
        width: 100%;
        justify-content: center;  /* Centra la paginación */
    }
    .container.mt-4 {
        width:1200px;  
        margin-left: 220px;
        padding: 70px;
    }
    .estado-vencido {
        color: red;
    }
    .estado-pendiente {
        color: yellow;
    }
    .estado-facturado {
        color: green;
    }
    .button-container {
    display: flex;            /* Alinea los botones de forma horizontal */
    gap: 2px;                /* Espacio entre los botones */
    justify-content: center;  /* Centra los botones en el contenedor */
}

.button-container a {
    width: 70px;  
    height:  35px;  /* Ajusta el ancho de los botones para que todos tengan el mismo tamaño */
    text-align: center;      /* Centra el texto dentro de los botones */
font-size:10px;
}

</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Ingresos</h2>
        <!-- Botón "+ INGRESO" -->
        <a href="nuevo_ingreso.php" class="btn btn-success mb-3">
            <strong>+ INGRESO</strong>
        </a>
        <a id="exportExcelBtn" class="btn btn-success mb-3" href="exportar_ingresos_excel.php">Exportar a Excel</a>

        <!-- Formulario de filtro -->
        <form method="POST">
    <div class="input-group">
        <select name="campo" class="form-select">
            <option value="todos" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="fecha" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'Estado') ? 'selected' : ''; ?>>Estado</option>
            <option value="proveedor" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'ingreso') ? 'selected' : ''; ?>>ingreso</option>
            <option value="descripcion" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'descripcion') ? 'selected' : ''; ?>>Descripción</option>
            <option value="categoria" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'categoria') ? 'selected' : ''; ?>>Categoría</option>
            <option value="monto" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'monto') ? 'selected' : ''; ?>>Monto</option>

            <option value="metodo_pago" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'vendedor') ? 'selected' : ''; ?>>vendedor</option>
            <option value="metodo_pago" <?php echo (isset($_POST['campo']) && $_POST['campo'] == 'metodo_cobro') ? 'selected' : ''; ?>>Método de cobro</option>
        </select>
        <input type="text" name="filtro" class="form-control" placeholder="Buscar..." value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>

        <!-- Tabla de ingresos -->
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo Ingreso</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Cliente</th>
                    <th>Factura AFIP</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ingresos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['fecha']; ?></td>
                        <td><?php echo $row['tipo_ingreso']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo '$' . number_format($row['monto'], 2); ?></td>
                        <td><?php echo $row['cliente']; ?></td>
                        <td><?php echo $row['factura_afip']; ?></td>
                        <td class="<?php echo 'estado-' . $row['estado']; ?>">
                            <?php echo ucfirst($row['estado']); ?>
                        </td>
                        <td>
                        <div class="button-container">
    <!-- Enlace para generar la factura -->
    <a href="generar_factura.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Factura</a>
    
    <!-- Enlace para eliminar con confirmación -->
    <a href="eliminar_ingreso.php?id=<?php echo $row['id']; ?>" 
       class="btn btn-danger btn-sm" 
       onclick="return confirm('¿Estás seguro de que deseas eliminar este ingreso? Esta acción no se puede deshacer.');">
       Eliminar
    </a>
    <a href="generar_factura.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-success btn-sm">Imprimir Factura</a>

    <!-- Enlace para ver "Arca" -->
    <a href="ver_arca.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"> Arca</a>

    <!-- Enlace para ver "Nota de Crédito" -->
    <a href="ver_nota_credito.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"> Nota de Crédito</a>
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
                // Mostrar las páginas
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
