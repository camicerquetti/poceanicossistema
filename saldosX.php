<?php
ob_start();  // Comienza el almacenamiento en búfer
include('config.php');  // Incluir la conexión a la base de datos

// Lógica para eliminar una cuenta
if (isset($_GET['eliminar'])) {
    $idCuenta = $_GET['eliminar'];

    // Asegurarse que el id de la cuenta sea válido
    if (is_numeric($idCuenta)) {
        // Consulta para eliminar la cuenta de la tabla 'cuentasx'
        $deleteQuery = "DELETE FROM cuentasx WHERE Id_cuenta = $idCuenta";

        // Ejecutar la consulta de eliminación
        if ($conn->query($deleteQuery) === TRUE) {
            $mensaje = "Cuenta eliminada exitosamente.";
        } else {
            $mensaje = "Error al eliminar la cuenta: " . $conn->error;
        }
    } else {
        $mensaje = "ID de cuenta no válido.";
    }

    // Redireccionar a la misma página para que la tabla se actualice
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Consulta para obtener todos los saldos de la tabla 'cuentasx'
$query = "SELECT Id_cuenta, Cuenta, Tipo, Saldo, Usuario, Categoria FROM cuentasx";
$result = $conn->query($query);

// Comprobar si hay resultados
if ($result->num_rows > 0) {
    $cuentas = $result->fetch_all(MYSQLI_ASSOC);  // Obtener todos los resultados como un array asociativo
} else {
    $cuentas = [];
}

// Lógica para agregar una nueva cuenta
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $cuenta = $_POST['cuenta'];
    $tipo = $_POST['tipo'];
    $saldo = $_POST['saldo'];
    $usuario = $_POST['usuario'];
    $categoria = $_POST['categoria'];  // Obtener la categoría del formulario

    // Consulta para insertar una nueva cuenta en la tabla 'cuentasx'
    $insertQuery = "INSERT INTO cuentasx (Cuenta, Tipo, Saldo, Usuario, Categoria) 
                    VALUES ('$cuenta', '$tipo', '$saldo', '$usuario', '$categoria')";  // Incluir la categoría en la consulta
    if ($conn->query($insertQuery) === TRUE) {
        $mensaje = "Cuenta agregada exitosamente.";
    } else {
        $mensaje = "Error al agregar la cuenta: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldos de Cuentas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container.mt-4 {
            width: 1200px;
            margin-left: 220px;
            padding: 70px;
        }

        .saldos-table {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .saldos-table th, .saldos-table td {
            padding: 10px;
            text-align: center;
        }

        .saldos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .saldos-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .saldo {
            font-weight: bold;
        }

        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-green:hover {
            background-color: #218838;
        }

        .form-container {
            margin-top: 30px;
            display: none; /* Inicialmente oculto */
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-red:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); // Si tienes un header común ?>
    </header>

    <div class="container mt-4">
        <h2>Saldos de las Cuentas</h2>

        <!-- Botón para agregar una nueva cuenta -->
        <button class="btn-green" id="btnMostrarFormulario">Agregar Nueva Cuenta</button>

        <!-- Tabla de saldos -->
        <table class="table saldos-table">
            <thead>
                <tr>
                    <th>ID Cuenta</th>
                    <th>Cuenta</th>
                    <th>Tipo</th>
                    <th>Saldo</th>
                    <th>Usuario</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cuentas) > 0): ?>
                    <?php foreach ($cuentas as $cuenta): ?>
                        <tr>
                            <td><?php echo $cuenta['Id_cuenta']; ?></td>
                            <td><?php echo $cuenta['Cuenta']; ?></td>
                            <td><?php echo $cuenta['Tipo']; ?></td>
                            <td class="saldo"><?php echo '$' . number_format($cuenta['Saldo'], 2); ?></td>
                            <td><?php echo $cuenta['Usuario']; ?></td>
                            <td><?php echo $cuenta['Categoria'] ? $cuenta['Categoria'] : 'N/A'; ?></td>
                            <td>
                                <!-- Botón para eliminar -->
                                <a href="?eliminar=<?php echo $cuenta['Id_cuenta']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta cuenta?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron cuentas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Formulario para agregar nueva cuenta -->
        <div id="formulario" class="form-container">
            <h3>Agregar Nueva Cuenta</h3>
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-info"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="cuenta" class="form-label">Nombre de la Cuenta</label>
                    <input type="text" id="cuenta" name="cuenta" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de Cuenta</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="Ahorro">Ahorro</option>
                        <option value="Corriente">Corriente</option>
                        <option value="Inversion">Inversión</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="saldo" class="form-label">Saldo Inicial</label>
                    <input type="number" id="saldo" name="saldo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" id="categoria" name="categoria" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Crear Cuenta</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript para mostrar u ocultar el formulario
        document.getElementById('btnMostrarFormulario').addEventListener('click', function() {
            var formulario = document.getElementById('formulario');
            if (formulario.style.display === "none" || formulario.style.display === "") {
                formulario.style.display = "block";
            } else {
                formulario.style.display = "none";
            }
        });
    </script>
</body>
</html>
