<?php
// Incluir la clase Proveedor y la conexión
include('config.php');
include('clase/Proveedor.php');

// Crear una instancia de Proveedor
$proveedor = new Proveedor($conn);

// Manejar filtro
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener el número de proveedores por página
$proveedores_por_pagina = 10;

// Calcular la página actual
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginacion - 1) * $proveedores_por_pagina;

// Obtener los proveedores filtrados con paginación
$proveedores = $proveedor->obtenerProveedores($filtro, $proveedores_por_pagina, $offset);

// Configuración de la cabecera para indicar que es un archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="proveedores.csv"');

// Abrir el archivo en "modo escritura"
$output = fopen('php://output', 'w');

// Escribir los encabezados de las columnas en el archivo CSV
fputcsv($output, ['ID', 'Proveedor', 'Email', 'Teléfono', 'CUIT', 'Razón Social', 'Saldo Inicial', 'Condición IVA', 'Observaciones']);

// Escribir los datos de los proveedores en el archivo CSV
while ($row = $proveedores->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['proveedor'] . ' ' . $row['razon_social'],
        $row['email'],
        $row['telefono'],
        $row['cuit'],
        $row['razon_social'],
        $row['saldo_inicial'],
        $row['condicion_iva'],
        $row['observaciones']
    ]);
}

// Cerrar el archivo
fclose($output);
exit();
?>
