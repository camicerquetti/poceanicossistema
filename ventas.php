<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Ingreso y la conexión
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';
$estado_filtro = isset($_POST['estado']) ? $_POST['estado'] : '%%';

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

// Obtener los ingresos filtrados con paginación, incluyendo los filtros por fecha
$sql = "SELECT * FROM ingresos WHERE (tipo_ingreso LIKE ? OR cliente LIKE ?) AND (estado LIKE ?) $condicion_fecha LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssii', $filtro, $filtro, $estado_filtro, $offset, $ingresos_por_pagina);
$stmt->execute();
$ingresos = $stmt->get_result();

// Obtener el total de ingresos para la paginación con fechas
$sql_total = "SELECT COUNT(*) FROM ingresos WHERE (tipo_ingreso LIKE ? OR cliente LIKE ?) AND (estado LIKE ?) $condicion_fecha";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('sss', $filtro, $filtro, $estado_filtro);
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
        width: 1200px;  
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
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Lista de Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Lista de Ventas</h2>
        
        <!-- Botón "+ INGRESO" -->
        <a href="nuevo_ingreso.php" class="btn btn-success mb-3">
            <strong>+ INGRESO</strong>
        </a>
        <a id="exportExcelBtn" class="btn btn-success mb-3" href="exportar_ventas_excel.php">Exportar a Excel</a>

        <!-- Formulario de filtro -->
  

        <!-- Filtro por estado -->
        <form method="POST" class="mb-4">
    <div class="row">
        <!-- Campo de búsqueda general -->
        <div class="col-md-3">
            <input type="text" name="filtro" class="form-control" placeholder="Buscar..." value="<?php echo isset($_POST['filtro']) ? $_POST['filtro'] : ''; ?>">
        </div>

        <!-- Filtro por tipo de ingreso -->
        <div class="col-md-2">
            <input type="text" name="tipo_ingreso" class="form-control" placeholder="Tipo de ingreso" value="<?php echo isset($_POST['tipo_ingreso']) ? $_POST['tipo_ingreso'] : ''; ?>">
        </div>

        <!-- Filtro por descripción -->
        <div class="col-md-2">
            <input type="text" name="descripcion" class="form-control" placeholder="Descripción" value="<?php echo isset($_POST['descripcion']) ? $_POST['descripcion'] : ''; ?>">
        </div>

        <!-- Filtro por cliente -->
        <div class="col-md-2">
            <input type="text" name="cliente" class="form-control" placeholder="Cliente" value="<?php echo isset($_POST['cliente']) ? $_POST['cliente'] : ''; ?>">
        </div>

        <!-- Filtro por estado -->
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="%%" <?php echo (isset($_POST['estado']) && $_POST['estado'] == '%%') ? 'selected' : ''; ?>>Todos</option>
                <option value="vencido" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'vencido') ? 'selected' : ''; ?>>Vencidos</option>
                <option value="pendiente" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendientes</option>
                <option value="facturado" <?php echo (isset($_POST['estado']) && $_POST['estado'] == 'facturado') ? 'selected' : ''; ?>>Facturado</option>
            </select>
        </div>

        <!-- Filtro por monto -->
        <div class="col-md-2 mt-2">
            <input type="number" step="0.01" name="monto" class="form-control" placeholder="Monto" value="<?php echo isset($_POST['monto']) ? $_POST['monto'] : ''; ?>">
        </div>

        <!-- Filtro de Fecha Inicio -->
        <div class="col-md-2 mt-2">
            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : ''; ?>">
        </div>

        <!-- Filtro de Fecha Fin -->
        <div class="col-md-2 mt-2">
            <input type="date" name="fecha_fin" class="form-control" value="<?php echo isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : ''; ?>">
        </div>

        <!-- Botón Filtrar -->
        <div class="col-md-2 mt-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
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
                            <!-- Enlace para ver más detalles o generar factura -->
                            <a href="detalles_venta.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Detalles</a>
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
