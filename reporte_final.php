<?php
ob_start();
// Incluir la conexión a la base de datos
include('config.php');

// Establecer la configuración regional para que los nombres de los meses estén en español
$conn->query("SET lc_time_names = 'es_ES'");

// Obtener el primer y último día del mes actual
$primer_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

// Consultas SQL para obtener los totales
$consultas = [
    'ingresos' => "
        SELECT SUM(total) AS total_ingresos
        FROM ingresos
        WHERE estado = 'facturado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'otros_ingresos' => "
        SELECT SUM(total) AS total_otros_ingresos
        FROM otros_ingresos
        WHERE fecha_creacion BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'ingresos_lista' => "
        SELECT * FROM ingresos
        WHERE estado = 'facturado' AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ",
    'otros_ingresos_lista' => "
        SELECT * FROM otros_ingresos
        WHERE fecha_creacion BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    "
];

// Ejecutar las consultas y almacenar los resultados
$resultados = [];
foreach ($consultas as $clave => $query) {
    if ($result = $conn->query($query)) {
        if (strpos($clave, 'lista') !== false) {
            // Si es una lista, almacenamos los resultados como arrays
            $resultados[$clave] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $row = $result->fetch_assoc();
            
            // Verificamos que los valores no sean nulos antes de formatearlos
            if (isset($row['total_ingresos']) && $row['total_ingresos'] !== null) {
                $resultados[$clave] = number_format($row['total_ingresos'], 2);
            } elseif (isset($row['total_otros_ingresos']) && $row['total_otros_ingresos'] !== null) {
                $resultados[$clave] = number_format($row['total_otros_ingresos'], 2);
            } else {
                $resultados[$clave] = '0.00';  // Si no hay resultados, mostramos 0.00
            }
        }
        $result->free();
    } else {
        // En caso de error en la consulta, asignar '0.00'
        $resultados[$clave] = '0.00';
    }
}

// Verificar si los resultados de las listas están definidos
if (!isset($resultados['ingresos_lista'])) {
    $resultados['ingresos_lista'] = [];
}
if (!isset($resultados['otros_ingresos_lista'])) {
    $resultados['otros_ingresos_lista'] = [];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Final</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            width: 1200px;
            margin-left:320px;
            padding: 0px;
            margin-top:-420px;
        }
        table {
            width: 100%;
        }
        th, td {
            text-align: center;
        }
        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleVisibility(id) {
            var element = document.getElementById(id);
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
</head>
<body>
<?php
include('headeradmin.php');
?>
    <div class="container">
        <h2>Reporte Final del Mes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ingresos</td>
                    <td>$<?php echo $resultados['ingresos']; ?></td>
                </tr>
                <tr>
                    <td>Otros Ingresos</td>
                    <td>$<?php echo $resultados['otros_ingresos']; ?></td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-info" onclick="toggleVisibility('ingresosLista')">Ver Ingresos</button>
        <button class="btn btn-info" onclick="toggleVisibility('otrosIngresosLista')">Ver Otros Ingresos</button>

        <!-- Listas detalladas (ocultas por defecto) -->
        <div id="ingresosLista" class="hidden">
            <h3>Ingresos</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['ingresos_lista'] as $ingreso): ?>
                        <tr>
                            <td><?php echo $ingreso['id']; ?></td>
                            <td><?php echo $ingreso['fecha']; ?></td>
                            <td>$<?php echo number_format($ingreso['total'], 2); ?></td>
                            <td><?php echo $ingreso['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="otrosIngresosLista" class="hidden">
            <h3>Otros Ingresos</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha de Creación</th>
                        <th>Total</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados['otros_ingresos_lista'] as $otros_ingreso): ?>
                        <tr>
                            <td><?php echo $otros_ingreso['id']; ?></td>
                            <td><?php echo $otros_ingreso['fecha_creacion']; ?></td>
                            <td>$<?php echo number_format($otros_ingreso['total'], 2); ?></td>
                            <td><?php echo $otros_ingreso['descripcion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
