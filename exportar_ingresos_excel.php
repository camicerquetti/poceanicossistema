<?php
// Incluir la conexión y la clase Ingreso
include('config.php');
include('clase/Ingreso.php');

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Manejar el filtro si existe
$filtro = isset($_POST['filtro']) ? '%' . $_POST['filtro'] . '%' : '%%';

// Obtener todos los ingresos filtrados
$ingresos = $ingreso->obtenerIngresos($filtro, PHP_INT_MAX, 0);

// Definir los encabezados para la exportación
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment;filename=ingresos.xls");
header("Cache-Control: max-age=0");

// Abrir el archivo para escribir
echo "<table border='1'>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Tipo Ingreso</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Cliente</th>
                <th>Factura AFIP</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>";

// Recorrer los ingresos y exportarlos
while ($row = $ingresos->fetch_assoc()) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['fecha']}</td>
            <td>{$row['tipo_ingreso']}</td>
            <td>{$row['descripcion']}</td>
            <td>" . number_format($row['monto'], 2) . "</td>
            <td>{$row['cliente']}</td>
            <td>{$row['factura_afip']}</td>
            <td>{$row['estado']}</td>
        </tr>";
}

echo "</tbody></table>";
exit;
?>
