<?php
// Incluir la clase Ingreso y la conexión
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Manejar filtro (opcional)
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';
$estado_filtro = isset($_POST['estado']) ? $_POST['estado'] : '%%';

// Obtener los ingresos filtrados (todos los ingresos si no se aplica un filtro)
$ingresos = $ingreso->obtenerIngresosVentas($filtro, $estado_filtro, 1000, 0); // Obtener todos los ingresos sin paginación

// Configuración de la cabecera para indicar que es un archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ventas.csv"');

// Abrir el archivo en "modo escritura"
$output = fopen('php://output', 'w');

// Escribir los encabezados de las columnas en el archivo CSV
fputcsv($output, ['ID', 'Fecha', 'Tipo Ingreso', 'Descripción', 'Monto', 'Cliente', 'Factura AFIP', 'Estado']);

// Escribir los datos de los ingresos en el archivo CSV
while ($row = $ingresos->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['fecha'],
        $row['tipo_ingreso'],
        $row['descripcion'],
        '$' . number_format($row['monto'], 2),
        $row['cliente'],
        $row['factura_afip'],
        ucfirst($row['estado'])  // Formato de estado con la primera letra en mayúscula
    ]);
}

// Cerrar el archivo
fclose($output);
exit();
?>
