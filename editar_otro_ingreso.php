<?php
// Iniciar la sesión para acceder a los datos del usuario logueado
session_start();

// Incluir el archivo de configuración y la conexión a la base de datos
include('config.php');

// Verificar si se ha enviado el formulario para editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $id = $_POST['id'];  // Obtener el ID del ingreso a editar
    $ingreso = $_POST['ingreso'];
    $categoria = $_POST['categoria'];
    $cuenta = $_POST['cuenta'];
    $vendedor = $_SESSION['usuario'];  // Obtener el usuario logueado de la sesión
    $total = $_POST['total'];
    $descripcion = $_POST['descripcion'];
    $fecha_creacion = $_POST['fecha_creacion'];
    $metodo_cobro = $_POST['metodo_cobro'];  // Recibir el nuevo campo

    // Actualizar los datos en la base de datos
    $query = "UPDATE otros_ingresos 
              SET ingreso = '$ingreso', categoria = '$categoria', cuenta = '$cuenta', 
                  vendedor = '$vendedor', total = '$total', descripcion = '$descripcion', 
                  fecha_creacion = '$fecha_creacion', metodo_cobro = '$metodo_cobro' 
              WHERE id = $id";

    if ($conn->query($query) === TRUE) {
        // Redirigir a la página de éxito
        header('Location: otros_ingresos.php');
        exit(); // Asegúrate de usar exit() después de header() para evitar continuar con la ejecución del script
    } else {
        // En caso de error en la actualización, mostrar un mensaje de error
        echo "Error: " . $conn->error;
    }
}

// Verificar si se ha pasado el ID del ingreso a editar
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los datos del ingreso a editar
    $query = "SELECT * FROM otros_ingresos WHERE id = $id";
    $resultado = $conn->query($query);

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
    } else {
        // Si no se encuentra el ingreso, redirigir
        header('Location: otros_ingresos.php');
        exit();
    }
} else {
    // Si no se pasa el ID, redirigir
    header('Location: otros_ingresos.php');
    exit();
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
    <title>Editar Otro Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include('headeradmin.php'); ?>
    </header>

    <div class="container mt-4">
        <h2>Editar Otro Ingreso</h2>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>"> <!-- Campo oculto con el ID -->

            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                    <!-- Ingreso -->
                    <div class="mb-3">
                        <label for="ingreso" class="form-label">Ingreso</label>
                        <input type="text" name="ingreso" class="form-control" value="<?php echo $row['ingreso']; ?>" required>
                    </div>
                    <!-- Categoría -->
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" name="categoria" class="form-control" value="<?php echo $row['categoria']; ?>" required>
                    </div>
                    <!-- Cuenta -->
                    <div class="mb-3">
                        <label for="cuenta" class="form-label">Cuenta</label>
                        <select name="cuenta" class="form-control" required>
                            <?php
                            // Consulta para obtener las cuentas desde la tabla "cuentas"
                            $query_cuentas = "SELECT Id_cuenta, Cuenta FROM cuentas";
                            $resultado_cuentas = $conn->query($query_cuentas);
                            while ($cuenta = $resultado_cuentas->fetch_assoc()) :
                            ?>
                                <option value="<?php echo $cuenta['Id_cuenta']; ?>" <?php echo ($cuenta['Id_cuenta'] == $row['cuenta']) ? 'selected' : ''; ?>>
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
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    <!-- Total -->
                    <div class="mb-3">
                        <label for="total" class="form-label">Total</label>
                        <input type="number" step="0.01" name="total" class="form-control" value="<?php echo $row['total']; ?>" required>
                    </div>
                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" required><?php echo $row['descripcion']; ?></textarea>
                    </div>
                    <!-- Fecha de Creación -->
                    <div class="mb-3">
                        <label for="fecha_creacion" class="form-label">Fecha de Creación</label>
                        <input type="date" name="fecha_creacion" class="form-control" value="<?php echo $row['fecha_creacion']; ?>" required>
                    </div>
                    <!-- Método de Cobro (nuevo campo) -->
                    <div class="mb-3">
                        <label for="metodo_cobro" class="form-label">Método de Cobro</label>
                        <input type="text" name="metodo_cobro" class="form-control" value="<?php echo $row['metodo_cobro']; ?>" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
