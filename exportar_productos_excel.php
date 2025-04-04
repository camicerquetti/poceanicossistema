<?php
// Incluir la clase Producto y la conexión
include('config.php');
include('clase/Producto.php');

// Crear una instancia de Producto
$producto = new Producto($conn);

// Manejar filtro (opcional)
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener los productos filtrados
$productos = $producto->obtenerProductos($filtro, 1000, 0); // Obtener todos los productos sin paginación

// Configuración de la cabecera para indicar que es un archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="productos.csv"');

// Abrir el archivo en "modo escritura"
$output = fopen('php://output', 'w');

// Escribir los encabezados de las columnas en el archivo CSV
fputcsv($output, ['ID', 'Nombre', 'Tipo', 'Proveedor', 'Código', 'Stock Total', 'Costo', 'Precio de Venta', 'IVA Compras', 'IVA Ventas']);

// Escribir los datos de los productos en el archivo CSV
while ($row = $productos->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['Nombre'],
        $row['Tipo'],
        $row['Proveedor'],
        $row['Codigo'],
        $row['Stock_Total'],
        $row['Costo'],
        $row['Precio_de_Venta'],
        $row['IVA_Compras'],
        $row['IVA_Ventas']
    ]);
}

// Cerrar el archivo
fclose($output);
exit();
?>
