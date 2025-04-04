<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir el encabezado y la configuración de la base de datos
include('headeradmin.php');
include('config.php');

// Consulta SQL para obtener los clientes con saldo pendiente
$query = "
    SELECT id, CONCAT(nombre, ' ', apellido) AS cliente, 
           saldo_inicial, deuda AS saldo_actual
    FROM clientes
    WHERE deuda > 0
    ORDER BY saldo_actual DESC
";

// Ejecutar la consulta
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Corriente de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            width: 1200px;
            margin-left: 320px;
            padding: 0px;
            margin-top: -420px;
        }
        table {
            width: 100%;
        }
        th, td {
            text-align: center;
        }
        .vencido {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cuenta Corriente de Clientes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Saldo Inicial</th>
                    <th>Saldo Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Asignar clase CSS según el nivel de deuda
                        $class = ($row['saldo_actual'] > 5000) ? 'deuda-alta' :
                                 (($row['saldo_actual'] > 1000) ? 'deuda-media' : 'deuda-baja');

                        echo "<tr class='$class'>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['cliente']) . "</td>";
                        echo "<td>$" . number_format($row['saldo_inicial'], 2) . "</td>";
                        echo "<td>$" . number_format($row['saldo_actual'], 2) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No hay clientes con deudas pendientes.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
