<?php
ob_start();
// Incluir la conexión a la base de datos
include('config.php');  // Asegúrate de que la conexión a la base de datos esté configurada correctamente

// Inicializar las variables de fecha y cuenta
$whereCondition = '';

// Comprobar si se seleccionó un filtro de fecha
if (isset($_POST['fecha'])) {
    $fechaSeleccionada = $_POST['fecha'];

    // Determinar el rango de fechas según el filtro seleccionado
    switch ($fechaSeleccionada) {
        case 'hoy':
            $whereCondition .= " WHERE DATE(m.Fecha) = CURDATE()";
            break;
        case 'ayer':
            $whereCondition .= " WHERE DATE(m.Fecha) = CURDATE() - INTERVAL 1 DAY";
            break;
        case 'semana_pasada':
            $whereCondition .= " WHERE m.Fecha >= CURDATE() - INTERVAL 1 WEEK";
            break;
        case 'mes_pasado':
            $whereCondition .= " WHERE m.Fecha >= CURDATE() - INTERVAL 1 MONTH";
            break;
        case 'esta_semana':
            $whereCondition .= " WHERE YEARWEEK(m.Fecha, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        default:
            $whereCondition .= '';  // Si no se selecciona nada, no se filtra por fecha
            break;
    }
}

// Filtro adicional por cuenta
if (isset($_POST['cuenta']) && $_POST['cuenta'] != '') {
    $cuentaSeleccionada = $_POST['cuenta'];
    $whereCondition .= ($whereCondition ? " AND" : " WHERE") . " c.Cuenta = '{$cuentaSeleccionada}'";
}

// Consulta para obtener los movimientos de la tabla 'movimientosx' y los detalles de la cuenta desde 'cuentasx'
$query = "SELECT m.Id_movimiento, m.Tipo, m.Monto, m.Fecha, m.Descripcion, m.Metodo_pago, c.Cuenta
          FROM movimientosx m
          JOIN cuentasx c ON m.Id_cuenta = c.Id_cuenta
          $whereCondition
          ORDER BY m.Fecha DESC";  // Ordenar los movimientos por fecha

$result = $conn->query($query);

// Comprobar si hay resultados
if ($result->num_rows > 0) {
    $movimientos = $result->fetch_all(MYSQLI_ASSOC);  // Obtener todos los resultados como un array asociativo
} else {
    $movimientos = [];
}

// Obtener las cuentas para el filtro de cuentas desde la tabla 'cuentasx'
$queryCuentas = "SELECT DISTINCT Cuenta FROM cuentasx";
$resultCuentas = $conn->query($queryCuentas);
$cuentas = $resultCuentas->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Cuentas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container.mt-4 {
            width: 1200px;
            margin-left: 220px;
            padding: 70px;
        }

        .movimientos-table {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .movimientos-table th, .movimientos-table td {
            padding: 10px;
            text-align: center;
        }

        .movimientos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .movimientos-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .monto-ingreso {
            color: green;
            font-weight: bold;
        }

        .monto-egreso {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); // Si tienes un header común ?>
    </header>

    <div class="container mt-4">
        <h2>Movimientos de Cuentas</h2>

        <!-- Formulario de filtro de fecha y cuenta -->
        <form method="POST" action="" class="mb-3">
            <div class="row">
                <div class="col">
                    <label for="fecha" class="form-label">Filtrar por fecha:</label>
                    <select name="fecha" id="fecha" class="form-select">
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="semana_pasada">Semana Pasada</option>
                        <option value="mes_pasado">Mes Pasado</option>
                        <option value="esta_semana">Esta Semana</option>
                        <option value="">Sin Filtro</option>
                    </select>
                </div>
                <div class="col">
                    <label for="cuenta" class="form-label">Filtrar por Cuenta:</label>
                    <select name="cuenta" id="cuenta" class="form-select">
                        <option value="">Seleccionar Cuenta</option>
                        <?php foreach ($cuentas as $cuenta): ?>
                            <option value="<?php echo $cuenta['Cuenta']; ?>"><?php echo $cuenta['Cuenta']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Tabla de movimientos -->
        <table class="table movimientos-table">
            <thead>
                <tr>
                    <th>ID Movimiento</th>
                    <th>Cuenta</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Método de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) > 0): ?>
                    <?php foreach ($movimientos as $movimiento): ?>
                        <tr>
                            <td><?php echo $movimiento['Id_movimiento']; ?></td>
                            <td><?php echo $movimiento['Cuenta']; ?></td>
                            <td><?php echo $movimiento['Tipo']; ?></td>
                            <td class="<?php echo ($movimiento['Tipo'] == 'Ingreso') ? 'monto-ingreso' : 'monto-egreso'; ?>">
                                <?php echo '$' . number_format($movimiento['Monto'], 2); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($movimiento['Fecha'])); ?></td>
                            <td><?php echo $movimiento['Descripcion']; ?></td>
                            <td><?php echo $movimiento['Metodo_pago']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron movimientos</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
