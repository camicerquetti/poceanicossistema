<?php
ob_start();  // Comienza el almacenamiento en búfer
include('config.php');
$porcentaje_cambio = 0;
if ($ingresos_mes_anterior > 0) {
    $porcentaje_cambio = (($ingresos_mes_actual - $ingresos_mes_anterior) / $ingresos_mes_anterior) * 100;
}

// Función para obtener los ingresos por mes
function obtenerIngresosPorMes($conn, $fecha_inicio, $fecha_fin) {
    $query = "SELECT SUM(monto) AS total_ingresos FROM ingresos WHERE fecha BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_ingresos'] ? $row['total_ingresos'] : 0;
}

// Función para obtener los ingresos por estado
function obtenerIngresosPorEstado($conn) {
    $query = "SELECT estado, SUM(monto) AS total_ingresos FROM ingresos GROUP BY estado";
    $result = $conn->query($query);
    $ingresos_por_estado = [];
    while ($row = $result->fetch_assoc()) {
        $ingresos_por_estado[$row['estado']] = $row['total_ingresos'];
    }
    return $ingresos_por_estado;
}

// Función para obtener la cantidad de compras a pagar
function obtenerCantidadComprasAPagar($conn) {
    $query = "SELECT COUNT(*) AS cantidad_compras_a_cobrar FROM compras WHERE estado = 'a_cobrar'";
    $result = $conn->query($query);
    if ($result === false) {
        die('Error en la consulta SQL (compras): ' . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['cantidad_compras_a_cobrar'] ? $row['cantidad_compras_a_cobrar'] : 0;
}

// Función para obtener la cantidad de gastos a pagar
function obtenerCantidadGastosAPagar($conn) {
    $query = "SELECT COUNT(*) AS cantidad_gastos_a_pagar FROM gastos WHERE estado = 'a_pagar'";
    $result = $conn->query($query);
    if ($result === false) {
        die('Error en la consulta SQL (gastos): ' . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['cantidad_gastos_a_pagar'] ? $row['cantidad_gastos_a_pagar'] : 0;
}

// Consultas adicionales para el nuevo div de ventas y otros ingresos
// Se consideran como "ventas" aquellos ingresos cuyo tipo_ingreso sea 'venta'
$queryVentas = "SELECT COUNT(*) AS cantidad_ventas, SUM(monto) AS total_ventas FROM ingresos WHERE tipo_ingreso = 'venta'";
$resultVentas = $conn->query($queryVentas);
if ($resultVentas) {
    $ventas_data = $resultVentas->fetch_assoc();
    $cantidad_ventas = $ventas_data['cantidad_ventas'];
    $total_ventas = $ventas_data['total_ventas'];
} else {
    $cantidad_ventas = 0;
    $total_ventas = 0;
}

// Cantidad de otros ingresos (registros en otros_ingresos)
$queryOtrosIngresos = "SELECT COUNT(*) AS cantidad_otros_ingresos FROM otros_ingresos";
$resultOtrosIngresos = $conn->query($queryOtrosIngresos);
if ($resultOtrosIngresos) {
    $otros_ingresos_data = $resultOtrosIngresos->fetch_assoc();
    $cantidad_otros_ingresos = $otros_ingresos_data['cantidad_otros_ingresos'];
} else {
    $cantidad_otros_ingresos = 0;
}

// Obtener fechas para comparar ingresos de mes actual y mes anterior
$fecha_actual = date('Y-m-d');
$fecha_mes_anterior = date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));

$inicio_mes_actual = date('Y-m-01', strtotime($fecha_actual));
$fin_mes_actual = date('Y-m-t', strtotime($fecha_actual));

$inicio_mes_anterior = date('Y-m-01', strtotime($fecha_mes_anterior));
$fin_mes_anterior = date('Y-m-t', strtotime($fecha_mes_anterior));

$ingresos_mes_actual = obtenerIngresosPorMes($conn, $inicio_mes_actual, $fin_mes_actual);
$ingresos_mes_anterior = obtenerIngresosPorMes($conn, $inicio_mes_anterior, $fin_mes_anterior);

$porcentaje_cambio = 0;
if ($ingresos_mes_anterior > 0) {
    $porcentaje_cambio = (($ingresos_mes_actual - $ingresos_mes_anterior) / $ingresos_mes_anterior) * 100;
}

$ingresos_estado = obtenerIngresosPorEstado($conn);
$cantidad_compras_a_pagar = obtenerCantidadComprasAPagar($conn);
$cantidad_gastos_a_pagar = obtenerCantidadGastosAPagar($conn);

// NUEVA CONSULTA DIARIA: separar en Ventas, Presupuestos y Otros Ingresos
$queryDiario = "SELECT fecha,
                     SUM(CASE WHEN tipo_ingreso = 'venta' THEN monto ELSE 0 END) AS ventas,
                     SUM(CASE WHEN tipo_ingreso = 'presupuesto' THEN monto ELSE 0 END) AS presupuestos,
                     SUM(CASE WHEN tipo_ingreso NOT IN ('venta', 'presupuesto') THEN monto ELSE 0 END) AS otros
               FROM ingresos
               GROUP BY fecha
               ORDER BY fecha ASC";
$resultDiario = $conn->query($queryDiario);
$fechas = [];
$ventasDiario = [];
$presupuestosDiario = [];
$otrosDiario = [];
if ($resultDiario) {
    while ($row = $resultDiario->fetch_assoc()) {
        $fechas[] = $row['fecha'];
        $ventasDiario[] = $row['ventas'];
        $presupuestosDiario[] = $row['presupuestos'];
        $otrosDiario[] = $row['otros'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ingresos y Finanzas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 60px; width: 980px; margin-left: 320px; }
        .card { padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .canvas-container { width: 200px; height: 200px; margin: auto; }
        /* Estilos para los gráficos pequeños */
        .canvas-small { width: 250px; height: 250px; }
    </style>
</head>
<body>

<header>
    <?php include('headeradmin.php'); ?>
</header>

<div class="container">
    <h2 class="text-center">Dashboard de Ingresos y Finanzas</h2>

    <!-- Fila de tarjetas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card text-center">
                <h4>Ingresos del Mes Actual</h4>
                <h3>$<?php echo number_format($ingresos_mes_actual, 2); ?></h3>
                 <!-- Mostrar el crecimiento con respecto al mes anterior -->
                 <p style="font-size: 14px; color: <?php echo $porcentaje_cambio >= 0 ? 'green' : 'red'; ?>;">
                    Crecimiento: <?php echo number_format($porcentaje_cambio, 2); ?>%
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center">
                <h4>Ingresos del Mes Anterior</h4>
                <h3>$<?php echo number_format($ingresos_mes_anterior, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center">
                <h4>Compras a cobrar</h4>
                <h3><?php echo $cantidad_compras_a_pagar; ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center">
                <h4>Gastos a Pagar</h4>
                <h3><?php echo $cantidad_gastos_a_pagar; ?></h3>
            </div>
        </div>
    </div>

    <!-- Fila de ventas y otros ingresos -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <h4>Ventas Creadas</h4>
                <h3><?php echo $cantidad_ventas; ?></h3>
                 <!-- Mostrar el crecimiento con respecto al mes anterior -->
                 <p style="font-size: 14px; color: <?php echo $porcentaje_cambio >= 0 ? 'green' : 'red'; ?>;">
                    Crecimiento: <?php echo number_format($porcentaje_cambio, 2); ?>%
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <h4>Total Ventas</h4>
                <h3>$<?php echo number_format($total_ventas, 2); ?></h3>
                 <!-- Mostrar el crecimiento con respecto al mes anterior -->
                 <p style="font-size: 14px; color: <?php echo $porcentaje_cambio >= 0 ? 'green' : 'red'; ?>;">
                    Crecimiento: <?php echo number_format($porcentaje_cambio, 2); ?>%
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <h4>Otros Ingresos</h4>
                <h3><?php echo $cantidad_otros_ingresos; ?></h3>
                 <!-- Mostrar el crecimiento con respecto al mes anterior -->
                 <p style="font-size: 14px; color: <?php echo $porcentaje_cambio >= 0 ? 'green' : 'red'; ?>;">
                    Crecimiento: <?php echo number_format($porcentaje_cambio, 2); ?>%
                </p>
            </div>
        </div>
    </div>

<!-- Fila de gráficos uno al lado del otro -->
<div class="row">
    <!-- GRÁFICO 1: Comparación por Fecha -->
    <div class="col-md-8">
        <div class="card">
            <h5 class="text-center">Comparación Diaria: Ventas vs. Otros Ingresos vs. Presupuestos</h5>
            <canvas id="graficoLineas"></canvas>
        </div>
    </div>

    <!-- GRÁFICO 3: Ingresos por Estado -->
    <div class="col-md-4">
        <div class="card text-center">
            <h5>Ingresos por Estado</h5>
            <canvas class="canvas-small" id="graficoDona"></canvas>
        </div>
    </div>
</div>

<!-- Fila de gráficos uno al lado del otro -->
<div class="row">
    <!-- GRÁFICO 2: Comparación de Ingresos -->
    <div class="col-md-6">
        <div class="card text-center">
            <h5>Comparación de Ingresos</h5>
            <canvas class="canvas-small" id="graficoBarra"></canvas>
        </div>
    </div>

  

    <!-- GRÁFICO 4: Comparación Gastos vs. Compras a Pagar -->
    <div class="col-md-6">
        <div class="card text-center">
            <h5>Comparación: Gastos vs. Compras a Pagar</h5>
            <canvas class="canvas-small" id="graficoGastosCompras"></canvas>
        </div>
    </div>
</div>

<script>
    // Gráfico de barras horizontales agrupadas para Gastos y Compras a Pagar
    var ctxGastosCompras = document.getElementById('graficoGastosCompras').getContext('2d');
    new Chart(ctxGastosCompras, {
        type: 'bar',
        data: {
            labels: ['Comparación'], // Puedes modificar esto si necesitas más categorías
            datasets: [
                {
                    label: 'Gastos a Pagar',
                    data: [<?php echo $cantidad_gastos_a_pagar; ?>],
                    backgroundColor: '#FF5733', // Color para los gastos
                    borderColor: '#D43F00', // Color del borde
                    borderWidth: 1
                },
                {
                    label: 'Compras a Pagar',
                    data: [<?php echo $cantidad_compras_a_pagar; ?>],
                    backgroundColor: '#33FF57', // Color para las compras
                    borderColor: '#28B442', // Color del borde
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            indexAxis: 'y', // Cambiar a barras horizontales
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top', // Opcional: Cambiar la posición de la leyenda
                    labels: {
                        font: {
                            family: 'Arial', // Cambiar la tipografía si se desea
                            size: 14
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw; // Personaliza las etiquetas
                        }
                    }
                }
            }
        }
    });
</script>



<!-- Script para los gráficos -->
<script>
    // Gráfico de líneas para comparación diaria
    var ctxLine = document.getElementById('graficoLineas').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($fechas); ?>,
            datasets: [
                {
                    label: 'Ventas',
                    data: <?php echo json_encode($ventasDiario); ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false,
                    tension: 0.3
                },
                {
                    label: 'Presupuestos',
                    data: <?php echo json_encode($presupuestosDiario); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: false,
                    tension: 0.3
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Gráfico de barras para comparación de ingresos
    var ctxBarra = document.getElementById('graficoBarra').getContext('2d');
    new Chart(ctxBarra, {
        type: 'bar',
        data: {
            labels: ['Mes Anterior', 'Mes Actual'],
            datasets: [{
                label: 'Ingresos',
                data: [<?php echo $ingresos_mes_anterior; ?>, <?php echo $ingresos_mes_actual; ?>],
                backgroundColor: ['#FF5733', '#33FF57']
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Gráfico de dona para ingresos por estado
    var ctxDona = document.getElementById('graficoDona').getContext('2d');
    new Chart(ctxDona, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($ingresos_estado)); ?>,
            datasets: [{ data: <?php echo json_encode(array_values($ingresos_estado)); ?>, backgroundColor: [ '#FFC300','#FF5733','#33FF57'] }]
        },
        options: { responsive: true }
    });

    // Gráfico de barras para gastos y compras a pagar
    var ctxGastosCompras = document.getElementById('graficoGastosCompras').getContext('2d');
    new Chart(ctxGastosCompras, {
        type: 'bar',
        data: {
            labels: ['Gastos a Pagar', 'Compras a Pagar'],
            datasets: [{
                label: 'Cantidad',
                data: [<?php echo $cantidad_gastos_a_pagar; ?>, <?php echo $cantidad_compras_a_pagar; ?>],
                backgroundColor: ['#FF5733', '#33FF57']
            }]
        },
        options: { responsive: true }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
