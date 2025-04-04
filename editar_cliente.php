<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir el header admin y la conexión a la base de datos
include('config.php');

// Verificar si el 'id' ha sido pasado por la URL
if (isset($_GET['id'])) {
    $cliente_id = $_GET['id'];

    // Consultar el cliente actual
    $query = "SELECT * FROM clientes WHERE id = '$cliente_id'";
    $result = $conn->query($query);

    // Verificar si la consulta fue exitosa
    if ($result === false) {
        die("Error en la consulta SQL: " . $conn->error);
    }

    // Comprobar si la consulta devolvió algún resultado
    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
    } else {
        die("Cliente no encontrado.");
    }

    // Si se envía el formulario, actualizar el cliente en la base de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitizar los datos recibidos del formulario para evitar inyecciones SQL
        $cliente_dato = $conn->real_escape_string($_POST['cliente']);
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $apellido = $conn->real_escape_string($_POST['apellido']);
        $email = $conn->real_escape_string($_POST['email']);
        $telefono = $conn->real_escape_string($_POST['telefono']);
        $telefono2 = $conn->real_escape_string($_POST['telefono2']);
        $direccion = $conn->real_escape_string($_POST['direccion']);
        $localidad = $conn->real_escape_string($_POST['localidad']);
        $provincia = $conn->real_escape_string($_POST['provincia']);
        $dni = $conn->real_escape_string($_POST['dni']);
        $cuit = $conn->real_escape_string($_POST['cuit']);
        $condicion_iva = $conn->real_escape_string($_POST['condicion_iva']);
        $razon_social = $conn->real_escape_string($_POST['razon_social']);
        $domicilio_fiscal = $conn->real_escape_string($_POST['domicilio_fiscal']);
        $localidad_fiscal = $conn->real_escape_string($_POST['localidad_fiscal']);
        $provincia_fiscal = $conn->real_escape_string($_POST['provincia_fiscal']);
        $codigo_postal_fiscal = $conn->real_escape_string($_POST['codigo_postal_fiscal']);
        $pagina_web = $conn->real_escape_string($_POST['pagina_web']);
        $saldo_inicial = $conn->real_escape_string($_POST['saldo_inicial']);
        $observaciones = $conn->real_escape_string($_POST['observaciones']);

        // Consulta preparada para actualizar el cliente
        $query_update = $conn->prepare("UPDATE clientes SET
            cliente = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, telefono2 = ?, direccion = ?, localidad = ?, provincia = ?, dni = ?, cuit = ?, condicion_iva = ?, razon_social = ?, domicilio_fiscal = ?, localidad_fiscal = ?, provincia_fiscal = ?, codigo_postal_fiscal = ?, pagina_web = ?, saldo_inicial = ?, observaciones = ?
            WHERE id = ?");

        // Vincular los parámetros a la consulta preparada
        $query_update->bind_param('ssssssssssssssssssds', 
            $cliente_dato, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $pagina_web, $saldo_inicial, $observaciones, $cliente_id);

        // Ejecutar la consulta
        if ($query_update->execute()) {
            $mensaje = "Cliente actualizado exitosamente.";
        } else {
            $mensaje = "Error al actualizar el cliente: " . $query_update->error;
        }
    }
} else {
    die("ID de cliente no especificado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
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
        <h2>Editar Cliente</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para editar el cliente -->
        <div class="form-container">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="cliente" class="form-label">Cliente</label>
                    <input type="text" id="cliente" name="cliente" class="form-control" value="<?php echo $cliente['cliente']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo $cliente['nombre']; ?>">
                </div>

                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" value="<?php echo $cliente['apellido']; ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $cliente['email']; ?>">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo $cliente['telefono']; ?>">
                </div>

                <div class="mb-3">
                    <label for="telefono2" class="form-label">Teléfono 2</label>
                    <input type="text" id="telefono2" name="telefono2" class="form-control" value="<?php echo $cliente['telefono2']; ?>">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo $cliente['direccion']; ?>">
                </div>

                <div class="mb-3">
                    <label for="localidad" class="form-label">Localidad</label>
                    <input type="text" id="localidad" name="localidad" class="form-control" value="<?php echo $cliente['localidad']; ?>">
                </div>

                <div class="mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" id="provincia" name="provincia" class="form-control" value="<?php echo $cliente['provincia']; ?>">
                </div>

                <div class="mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" id="dni" name="dni" class="form-control" value="<?php echo $cliente['dni']; ?>">
                </div>

                <div class="mb-3">
                    <label for="cuit" class="form-label">CUIT</label>
                    <input type="text" id="cuit" name="cuit" class="form-control" value="<?php echo $cliente['cuit']; ?>">
                </div>

                <div class="mb-3">
                    <label for="condicion_iva" class="form-label">Condición IVA</label>
                    <input type="text" id="condicion_iva" name="condicion_iva" class="form-control" value="<?php echo $cliente['condicion_iva']; ?>">
                </div>

                <div class="mb-3">
                    <label for="razon_social" class="form-label">Razón Social</label>
                    <input type="text" id="razon_social" name="razon_social" class="form-control" value="<?php echo $cliente['razon_social']; ?>">
                </div>

                <div class="mb-3">
                    <label for="domicilio_fiscal" class="form-label">Domicilio Fiscal</label>
                    <input type="text" id="domicilio_fiscal" name="domicilio_fiscal" class="form-control" value="<?php echo $cliente['domicilio_fiscal']; ?>">
                </div>

                <div class="mb-3">
                    <label for="localidad_fiscal" class="form-label">Localidad Fiscal</label>
                    <input type="text" id="localidad_fiscal" name="localidad_fiscal" class="form-control" value="<?php echo $cliente['localidad_fiscal']; ?>">
                </div>

                <div class="mb-3">
                    <label for="provincia_fiscal" class="form-label">Provincia Fiscal</label>
                    <input type="text" id="provincia_fiscal" name="provincia_fiscal" class="form-control" value="<?php echo $cliente['provincia_fiscal']; ?>">
                </div>

                <div class="mb-3">
                    <label for="codigo_postal_fiscal" class="form-label">Código Postal Fiscal</label>
                    <input type="text" id="codigo_postal_fiscal" name="codigo_postal_fiscal" class="form-control" value="<?php echo $cliente['codigo_postal_fiscal']; ?>">
                </div>

                <div class="mb-3">
                    <label for="pagina_web" class="form-label">Página Web</label>
                    <input type="text" id="pagina_web" name="pagina_web" class="form-control" value="<?php echo $cliente['pagina_web']; ?>">
                </div>

                <div class="mb-3">
                    <label for="saldo_inicial" class="form-label">Saldo Inicial</label>
                    <input type="number" id="saldo_inicial" name="saldo_inicial" class="form-control" value="<?php echo $cliente['saldo_inicial']; ?>" step="0.01">
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" class="form-control"><?php echo $cliente['observaciones']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Actualizar Cliente</button>
                <a href="lista_clientes.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
