<?php
// Iniciar la sesión para acceder a los datos del usuario logueado
session_start();

// Incluir el archivo de configuración y la conexión a la base de datos
include('config.php');

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $ingreso = $_POST['ingreso'];
    $categoria = $_POST['categoria'];
    $cuenta = $_POST['cuenta'];
    $vendedor = $_SESSION['usuario'];  // Obtener el usuario logueado de la sesión
    $total = $_POST['total'];
    $descripcion = $_POST['descripcion'];
    $fecha_creacion = $_POST['fecha_creacion'];

    // Insertar los datos en la base de datos
    $query = "INSERT INTO otros_ingresos (ingreso, categoria, cuenta, vendedor, total, descripcion, fecha_creacion) 
              VALUES ('$ingreso', '$categoria', '$cuenta', '$vendedor', '$total', '$descripcion', '$fecha_creacion')";

    if ($conn->query($query) === TRUE) {
        echo "Nuevo ingreso registrado correctamente";
        // Redirigir o mostrar un mensaje de éxito
        header('Location: otros_ingresos_ventas.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<style>
    .container.mt-4 {
        width: 1200px;
        margin-left: 220px;
        padding: 80px;
    }
</style>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Otro Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headerventas.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Nuevo Otro Ingreso</h2>

        <form method="POST">
            <!-- Ingreso -->
            <div class="mb-3">
                <label for="ingreso" class="form-label">Ingreso</label>
                <input type="text" name="ingreso" class="form-control" required>
            </div>

            <!-- Categoría -->
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoría</label>
                <input type="text" name="categoria" class="form-control" required>
            </div>

            <!-- Cuenta -->
            <div class="mb-3">
    <label for="cuenta" class="form-label">Cuenta</label>
    <select name="cuenta" class="form-control" required>
        <?php
        // Consulta para obtener las cuentas desde la tabla "cuentas"
        $query_cuentas = "SELECT Id_cuenta, Cuenta FROM cuentas";
        $resultado_cuentas = $conn->query($query_cuentas);

        // Mostrar las cuentas en el select
        while ($cuenta = $resultado_cuentas->fetch_assoc()) :
        ?>
            <option value="<?php echo $cuenta['Id_cuenta']; ?>">
                <?php echo $cuenta['Cuenta']; ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>


            <!-- Vendedor (obtenido de la sesión) -->
            <div class="mb-3">
                <label for="vendedor" class="form-label">Vendedor</label>
                <input type="text" name="vendedor" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
            </div>

            <!-- Total -->
            <div class="mb-3">
                <label for="total" class="form-label">Total</label>
                <input type="number" step="0.01" name="total" class="form-control" required>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" required></textarea>
            </div>

            <!-- Fecha de Creación -->
            <div class="mb-3">
                <label for="fecha_creacion" class="form-label">Fecha de Creación</label>
                <input type="date" name="fecha_creacion" class="form-control" required>
            </div>

            <!-- Botón para guardar -->
            <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
