<?php
ob_start();
// Incluir la conexión a la base de datos
include('config.php');

// Establecer la configuración regional para que los nombres de los meses estén en español
$conn->query("SET lc_time_names = 'es_ES'");

// Obtener el primer y último día del mes actual
$primer_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

// Consulta SQL para obtener el empleado con mayores ingresos del mes actual
$query = "
    SELECT 
        i.empleado_responsable AS Empleado,
        SUM(i.monto) AS Ingresos
    FROM 
        ingresos i
    WHERE 
        i.estado = 'facturado'
        AND i.fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    GROUP BY 
        i.empleado_responsable
    ORDER BY 
        Ingresos DESC
    LIMIT 1
";

// Ejecutar la consulta y manejar posibles errores
if ($result = $conn->query($query)) {
    // Verificar si se obtuvieron resultados
    if ($row = $result->fetch_assoc()) {
        $empleado = htmlspecialchars($row['Empleado']);
        $ingresos = number_format($row['Ingresos'], 2);
    } else {
        $empleado = 'No hay datos';
        $ingresos = '0.00';
    }
    // Liberar el conjunto de resultados
    $result->free();
} else {
    // Mostrar mensaje de error si la consulta falla
    echo "Error en la consulta SQL: " . $conn->error;
    $empleado = 'Error';
    $ingresos = '0.00';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleado con Más Ingresos del Mes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Centrar la tabla en el espacio disponible */
        .container {
            max-width: 700px; /* Limitar el ancho de la tabla */
            margin: 100px auto; /* Centrar la tabla vertical y horizontalmente */
            padding: 20px;
        }
        table {
            width: 100%;
        }
        th, td {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Incluir el header de administración -->
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container">
        <h2>Empleado con Más Ingresos del Mes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $empleado; ?></td>
                    <td>$<?php echo $ingresos; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
