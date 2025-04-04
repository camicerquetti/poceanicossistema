<?php
// Incluir la clase Cliente y la conexión
include('config.php');
include('clase/Cliente.php');

// Crear una instancia de Cliente
$cliente = new Cliente($conn);

// Manejar filtro (opcional)
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener los clientes filtrados (todos los clientes si no se aplica un filtro)
$clientes = $cliente->obtenerClientes($filtro, 1000, 0); // Obtener todos los clientes sin paginación

// Configuración de la cabecera para indicar que es un archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="clientes.csv"');

// Abrir el archivo en "modo escritura"
$output = fopen('php://output', 'w');

// Escribir los encabezados de las columnas en el archivo CSV
fputcsv($output, ['ID', 'Cliente', 'Email', 'Teléfono', 'DNI', 'Saldo Inicial']);

// Escribir los datos de los clientes en el archivo CSV
while ($row = $clientes->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['cliente'] . ' ' . $row['nombre'] . ' ' . $row['apellido'],
        $row['email'],
        $row['telefono'],
        $row['dni'],
        $row['saldo_inicial']
    ]);
}

// Cerrar el archivo
fclose($output);
exit();
?>
