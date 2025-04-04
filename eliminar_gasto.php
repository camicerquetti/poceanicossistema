<?php
ob_start();
include('config.php');
include('clase/Gasto.php');

// Verificar si el usuario está logueado (si tienes sistema de autenticación)
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gastos.php?error=ID de gasto no válido');
    exit();
}

try {
    // Obtener el ID del gasto
    $id = (int)$_GET['id'];

    // Crear instancia de la clase Gasto
    $gasto = new Gasto($conn);

    // Verificar si el gasto existe antes de eliminarlo
    $gasto_actual = $gasto->obtenerGastoPorId($id);
    if (!$gasto_actual) {
        throw new Exception("El gasto no existe");
    }

    // Intentar eliminar el gasto
    if ($gasto->eliminarGasto($id)) {
        header('Location: gastos.php?mensaje=Gasto eliminado correctamente');
    } else {
        throw new Exception("No se pudo eliminar el gasto");
    }

} catch (Exception $e) {
    header('Location: gastos.php?error=' . urlencode($e->getMessage()));
}
exit();
?> 