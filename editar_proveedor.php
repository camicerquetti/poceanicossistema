<?php
ob_start();  // Comienza el almacenamiento en búfer
// Incluir la clase Proveedor y la conexión
include('config.php');
include('clase/Proveedor.php');

// Crear una instancia de Proveedor
$proveedor = new Proveedor($conn);

// Verificar si se pasó un ID en la URL
if (isset($_GET['id'])) {
    $id_proveedor = $_GET['id'];

    // Obtener los detalles del proveedor con el ID proporcionado
    $resultado = $proveedor->obtenerProveedorPorID($id_proveedor);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
    } else {
        die('Proveedor no encontrado');
    }
} else {
    die('ID de proveedor no proporcionado');
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos del formulario
    $nombre = $_POST['Nombre'];
    $apellido = $_POST['Apellido'];
    $email = $_POST['Email'];
    $telefono = $_POST['Telefono'];
    $telefono2 = $_POST['Telefono2'];
    $direccion = $_POST['Direccion'];
    $localidad = $_POST['Localidad'];
    $provincia = $_POST['Provincia'];
    $dni = $_POST['Dni'];
    $cuit = $_POST['Cuit'];
    $condicion_iva = $_POST['Condicion_iva'];
    $razon_social = $_POST['Razon_Social'];
    $domicilio_fiscal = $_POST['Domicilio_Fiscal'];
    $localidad_fiscal = $_POST['Localidad_Fiscal'];
    $provincia_fiscal = $_POST['Provincia_Fiscal'];
    $codigo_postal_fiscal = $_POST['Codigo_Postal_Fiscal'];
    $observaciones = $_POST['Observaciones'];

    // Actualizar los datos del proveedor
    $proveedor->actualizarProveedor($id_proveedor, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $observaciones);

    // Redirigir después de actualizar
    header('Location: lista_proveedores.php');
    exit();
}
?>
<style>
    /* Ajuste para que el contenido quede fuera del encabezado */
    body {
        padding-top: 80px; /* Agrega espacio debajo del header (ajusta según el tamaño del header) */
    }

    .container {
        margin-left: 140px; /* Ajusta el margen a la izquierda según lo necesites */
    }

    /* Si deseas un margen solo en el formulario, puedes hacerlo así */
    form {
        height:40%;
        margin-left: 10px; /* Asegúrate de ajustar esto según sea necesario */
    }

    /* Opcional: Si deseas un mayor margen a nivel de los campos de formulario */
    .form-control {
        margin-left:5px; /* Ajusta según lo necesites */
    }
    .container.mt-4 {
        width: 1200px;
        margin-left: 400px;
        padding: 80px;
        margin-top:70px;

        }

    </style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header>
    <?php include('headeradmin.php'); ?>
</header>
<main>
    <div class="container mt-4">
        <h2>Editar Proveedor</h2>

        <!-- Formulario para editar proveedor -->
        <form method="POST">
            <div class="mb-3">
                <label for="Nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="Nombre" name="Nombre" value="<?php echo $row['nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="Apellido" name="Apellido" value="<?php echo $row['apellido']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Email" class="form-label">Email</label>
                <input type="email" class="form-control" id="Email" name="Email" value="<?php echo $row['email']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="Telefono" name="Telefono" value="<?php echo $row['telefono']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="Telefono2" class="form-label">Teléfono 2</label>
                <input type="text" class="form-control" id="Telefono2" name="Telefono2" value="<?php echo $row['telefono2']; ?>">
            </div>
            <div class="mb-3">
                <label for="Direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="Direccion" name="Direccion" value="<?php echo $row['direccion']; ?>">
            </div>
            <div class="mb-3">
                <label for="Localidad" class="form-label">Localidad</label>
                <input type="text" class="form-control" id="Localidad" name="Localidad" value="<?php echo $row['localidad']; ?>">
            </div>
            <div class="mb-3">
                <label for="Provincia" class="form-label">Provincia</label>
                <input type="text" class="form-control" id="Provincia" name="Provincia" value="<?php echo $row['provincia']; ?>">
            </div>
            <div class="mb-3">
                <label for="Dni" class="form-label">DNI</label>
                <input type="text" class="form-control" id="Dni" name="Dni" value="<?php echo $row['dni']; ?>">
            </div>
            <div class="mb-3">
                <label for="Cuit" class="form-label">CUIT</label>
                <input type="text" class="form-control" id="Cuit" name="Cuit" value="<?php echo $row['cuit']; ?>">
            </div>
            <div class="mb-3">
                <label for="Condicion_iva" class="form-label">Condición IVA</label>
                <input type="text" class="form-control" id="Condicion_iva" name="Condicion_iva" value="<?php echo $row['condicion_iva']; ?>">
            </div>
            <div class="mb-3">
                <label for="Razon_Social" class="form-label">Razón Social</label>
                <input type="text" class="form-control" id="Razon_Social" name="Razon_Social" value="<?php echo $row['razon_social']; ?>">
            </div>
            <div class="mb-3">
                <label for="Domicilio_Fiscal" class="form-label">Domicilio Fiscal</label>
                <input type="text" class="form-control" id="Domicilio_Fiscal" name="Domicilio_Fiscal" value="<?php echo $row['domicilio_fiscal']; ?>">
            </div>
            <div class="mb-3">
                <label for="Localidad_Fiscal" class="form-label">Localidad Fiscal</label>
                <input type="text" class="form-control" id="Localidad_Fiscal" name="Localidad_Fiscal" value="<?php echo $row['localidad_fiscal']; ?>">
            </div>
            <div class="mb-3">
                <label for="Provincia_Fiscal" class="form-label">Provincia Fiscal</label>
                <input type="text" class="form-control" id="Provincia_Fiscal" name="Provincia_Fiscal" value="<?php echo $row['provincia_fiscal']; ?>">
            </div>
            <div class="mb-3">
                <label for="Codigo_Postal_Fiscal" class="form-label">Código Postal Fiscal</label>
                <input type="text" class="form-control" id="Codigo_Postal_Fiscal" name="Codigo_Postal_Fiscal" value="<?php echo $row['codigo_postal_fiscal']; ?>">
            </div>
            <div class="mb-3">
                <label for="Observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="Observaciones" name="Observaciones"><?php echo $row['observaciones']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
