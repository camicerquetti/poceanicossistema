<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir el header admin y la conexión a la base de datos

include('config.php');

// Si se envía el formulario, insertar el proveedor en la base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor = $_POST['proveedor'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $telefono2 = $_POST['telefono2'];
    $direccion = $_POST['direccion'];
    $localidad = $_POST['localidad'];
    $provincia = $_POST['provincia'];
    $dni = $_POST['dni'];
    $cuit = $_POST['cuit'];
    $condicion_iva = $_POST['condicion_iva'];
    $razon_social = $_POST['razon_social'];
    $domicilio_fiscal = $_POST['domicilio_fiscal'];
    $localidad_fiscal = $_POST['localidad_fiscal'];
    $provincia_fiscal = $_POST['provincia_fiscal'];
    $codigo_postal_fiscal = $_POST['codigo_postal_fiscal'];
    $fecha_saldo_inicial = $_POST['fecha_saldo_inicial'];
    $saldo_inicial = $_POST['saldo_inicial'];
    $observaciones = $_POST['observaciones'];

    // Consulta para insertar el nuevo proveedor
    $query = "INSERT INTO proveedores (proveedor, nombre, apellido, email, telefono, telefono2, direccion, localidad, provincia, dni, cuit, condicion_iva, razon_social, domicilio_fiscal, localidad_fiscal, provincia_fiscal, codigo_postal_fiscal, fecha_saldo_inicial, saldo_inicial, observaciones)
              VALUES ('$proveedor', '$nombre', '$apellido', '$email', '$telefono', '$telefono2', '$direccion', '$localidad', '$provincia', '$dni', '$cuit', '$condicion_iva', '$razon_social', '$domicilio_fiscal', '$localidad_fiscal', '$provincia_fiscal', '$codigo_postal_fiscal', '$fecha_saldo_inicial', '$saldo_inicial', '$observaciones')";

    if ($conn->query($query) === TRUE) {
        $mensaje = "Proveedor agregado exitosamente.";
    } else {
        $mensaje = "Error al agregar el proveedor: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1000px;
            margin-left: 300px;
            margin-right: auto;
            padding: 40px;
        }

        body {
            padding-top: 60px;
        }

        .form-container {
            margin-top: 40px;
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
    </style>
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container">
        <h2>Agregar Nuevo Proveedor</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar un nuevo proveedor -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <input type="text" id="proveedor" name="proveedor" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" id="apellido" name="apellido" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="telefono2" class="form-label">Teléfono 2</label>
                    <input type="text" id="telefono2" name="telefono2" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="localidad" class="form-label">Localidad</label>
                    <input type="text" id="localidad" name="localidad" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" id="provincia" name="provincia" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" id="dni" name="dni" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="cuit" class="form-label">CUIT</label>
                    <input type="text" id="cuit" name="cuit" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="condicion_iva" class="form-label">Condición IVA</label>
                    <input type="text" id="condicion_iva" name="condicion_iva" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="razon_social" class="form-label">Razón Social</label>
                    <input type="text" id="razon_social" name="razon_social" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="domicilio_fiscal" class="form-label">Domicilio Fiscal</label>
                    <input type="text" id="domicilio_fiscal" name="domicilio_fiscal" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="localidad_fiscal" class="form-label">Localidad Fiscal</label>
                    <input type="text" id="localidad_fiscal" name="localidad_fiscal" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="provincia_fiscal" class="form-label">Provincia Fiscal</label>
                    <input type="text" id="provincia_fiscal" name="provincia_fiscal" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="codigo_postal_fiscal" class="form-label">Código Postal Fiscal</label>
                    <input type="text" id="codigo_postal_fiscal" name="codigo_postal_fiscal" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="fecha_saldo_inicial" class="form-label">Fecha Saldo Inicial</label>
                    <input type="date" id="fecha_saldo_inicial" name="fecha_saldo_inicial" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="saldo_inicial" class="form-label">Saldo Inicial</label>
                    <input type="number" id="saldo_inicial" name="saldo_inicial" class="form-control" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Guardar Proveedor</button>
                <a href="lista_proveedores.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
