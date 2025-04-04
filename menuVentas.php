<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la conexión a la base de datos
include('config.php');

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

// Función para obtener las compras a cobrar
function obtenerComprasACobrar($conn) {
    $query = "SELECT SUM(total) AS total_compras_a_cobrar FROM compras WHERE estado = 'pendiente'"; // La columna 'estado' está en la tabla 'compras'
    $result = $conn->query($query);
    
    if ($result === false) {
        die('Error en la consulta SQL: ' . $conn->error);
    }

    $row = $result->fetch_assoc();
    return $row['total_compras_a_cobrar'] ? $row['total_compras_a_cobrar'] : 0;
}

// Función para obtener los gastos a pagar
function obtenerGastosAPagar($conn) {
    $query = "SELECT SUM(total) AS total_gastos_a_pagar FROM gastos WHERE estado = 'pendiente'"; // La columna 'estado' está en la tabla 'gastos'
    $result = $conn->query($query);
    
    if ($result === false) {
        die('Error en la consulta SQL: ' . $conn->error);
    }

    $row = $result->fetch_assoc();
    return $row['total_gastos_a_pagar'] ? $row['total_gastos_a_pagar'] : 0;
}

// Obtener las fechas del mes actual y el mes anterior
$fecha_actual = date('Y-m-d');
$fecha_mes_anterior = date('Y-m-d', strtotime('-1 month', strtotime($fecha_actual)));

$inicio_mes_actual = date('Y-m-01', strtotime($fecha_actual));
$fin_mes_actual = date('Y-m-t', strtotime($fecha_actual));

$inicio_mes_anterior = date('Y-m-01', strtotime($fecha_mes_anterior));
$fin_mes_anterior = date('Y-m-t', strtotime($fecha_mes_anterior));

// Obtener ingresos del mes actual y el mes anterior
$ingresos_mes_actual = obtenerIngresosPorMes($conn, $inicio_mes_actual, $fin_mes_actual);
$ingresos_mes_anterior = obtenerIngresosPorMes($conn, $inicio_mes_anterior, $fin_mes_anterior);

// Calcular el porcentaje de cambio
$porcentaje_cambio = 0;
if ($ingresos_mes_anterior > 0) {
    $porcentaje_cambio = (($ingresos_mes_actual - $ingresos_mes_anterior) / $ingresos_mes_anterior) * 100;
}

// Obtener ingresos por estado
$ingresos_estado = obtenerIngresosPorEstado($conn);

// Obtener compras a cobrar y gastos a pagar
$compras_a_cobrar = obtenerComprasACobrar($conn);
$gastos_a_pagar = obtenerGastosAPagar($conn);
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
        .canvas-container { width: 400px; height: 400px; margin: auto; }
    </style>
</head>
<body>

    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container">
        <h2 class="text-center">Dashboard de Ingresos y Finanzas</h2>

        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <h4>Ingresos del Mes Actual</h4>
                    <h3>$<?php echo number_format($ingresos_mes_actual, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <h4>Ingresos del Mes Anterior</h4>
                    <h3>$<?php echo number_format($ingresos_mes_anterior, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <h4>Compras a Cobrar</h4>
                    <h3>$<?php echo number_format($compras_a_cobrar, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <h4>Gastos a Pagar</h4>
                    <h3>$<?php echo number_format($gastos_a_pagar, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h5 class="text-center">Comparación de Ingresos</h5>
                    <canvas id="graficoBarra"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <h5 class="text-center">Ingresos por Estado</h5>
                    <canvas id="graficoDona"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h5 class="text-center">Comparación de Gastos</h5>
                    <canvas id="graficoGastos"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <h5 class="text-center">Compras a Cobrar</h5>
                    <canvas id="graficoCompras"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gráfico de barras para los ingresos
        var ctxBarra = document.getElementById('graficoBarra').getContext('2d');
        new Chart(ctxBarra, {
            type: 'bar',
            data: {
                labels: ['Mes Anterior', 'Mes Actual'],
                datasets: [{
                    label: 'Ingresos',
                    data: [<?php echo $ingresos_mes_anterior; ?>, <?php echo $ingresos_mes_actual; ?>],
                    backgroundColor: ['#FF5733', '#33FF57'],
                    borderColor: ['#C70039', '#00FF00'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Gráfico de dona para ingresos por estado
        var ctxDona = document.getElementById('graficoDona').getContext('2d');
        new Chart(ctxDona, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($ingresos_estado)); ?>,
                datasets: [{
                    label: 'Ingresos por Estado',
                    data: <?php echo json_encode(array_values($ingresos_estado)); ?>,
                    backgroundColor: ['#FF5733', '#33FF57', '#FFC300', '#C70039']
                }]
            },
            options: { responsive: true }
        });

        // Gráfico de barras para comparar gastos a pagar y compras a cobrar
        var ctxGastos = document.getElementById('graficoGastos').getContext('2d');
        new Chart(ctxGastos, {
            type: 'bar',
            data: {
                labels: ['Gastos a Pagar', 'Compras a Cobrar'],
                datasets: [{
                    label: 'Total',
                    data: [<?php echo $gastos_a_pagar; ?>, <?php echo $compras_a_cobrar; ?>],
                    backgroundColor: ['#FF5733', '#33FF57'],
                    borderColor: ['#C70039', '#00FF00'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Gráfico de barras para las compras a cobrar
        var ctxCompras = document.getElementById('graficoCompras').getContext('2d');
        new Chart(ctxCompras, {
            type: 'bar',
            data: {
                labels: ['Compras a Cobrar'],
                datasets: [{
                    label: 'Total Compras a Cobrar',
                    data: [<?php echo $compras_a_cobrar; ?>],
                    backgroundColor: ['#33FF57'],
                    borderColor: ['#00FF00'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
