<?php
// Incluir las dependencias necesarias
include('config.php');
include('clase/Ingreso.php');
include('afip_wsdl/FeCabeceraRequest.php');  // Librería de AFIP
include('afip_wsdl/FeDetRequest.php');
include('afip_wsdl/FeRequest.php');

// Obtener el ID de la factura desde la solicitud POST
if (isset($_POST['factura_id'])) {
    $factura_id = (int)$_POST['factura_id'];
} else {
    echo json_encode(['success' => false, 'error' => 'ID de factura no proporcionado']);
    exit();
}

// Crear una instancia de la clase Ingreso
$ingreso = new Ingreso($conn);

// Obtener los detalles de la factura
$detalle_ingreso = $ingreso->obtenerIngresoPorId($factura_id);
if (!$detalle_ingreso) {
    echo json_encode(['success' => false, 'error' => 'Factura no encontrada']);
    exit();
}

// Generar el XML para la Nota de Crédito
$xml = new SimpleXMLElement('<FacturaElectronica></FacturaElectronica>');
$xml->addChild('CUIT', '20343456789');  // El CUIT de la empresa
$xml->addChild('TipoComprobante', '11');  // Nota de Crédito
$xml->addChild('NumeroComprobante', '1');  // Número de la Nota de Crédito
$xml->addChild('FechaEmision', date('Y-m-d'));  // Fecha de emisión

// Datos de la factura original
$facturaOriginal = $xml->addChild('FacturaOriginal');
$facturaOriginal->addChild('CAE', $detalle_ingreso['factura_afip']);  // El CAE de la factura original
$facturaOriginal->addChild('FechaEmisionOriginal', $detalle_ingreso['fecha']);  // Fecha de emisión de la factura original

// Detalles del ajuste
$ajuste = $xml->addChild('Ajuste');
$ajuste->addChild('Monto', $detalle_ingreso['total_venta']);  // Total de la factura
$ajuste->addChild('Motivo', 'Ajuste de precio');  // Motivo de la Nota de Crédito

// Conectar con AFIP usando Web Services (esto debe realizarse mediante un servicio SOAP o una librería que facilite la conexión)
$fe = new FeRequest();
$fe->setToken('TOKEN');  // Usar el token obtenido para la autenticación
$fe->setSign('SIGNATURE');
$fe->setCUIT('20343456789');  // El CUIT de la empresa

// Parámetros de la solicitud de la Nota de Crédito
$feCabecera = new FeCabeceraRequest();
$feCabecera->setConcepto('Nota de Crédito');
$feCabecera->setPuntoVenta('001');  // Punto de venta
$feCabecera->setTipoComprobante('11');  // Tipo de comprobante: Nota de Crédito
$feCabecera->setNumComprobante('1');  // Número del comprobante

$feDetRequest = new FeDetRequest();
$feDetRequest->setFechaEmision(date('Y-m-d'));  // Fecha de emisión

// Enviar la solicitud a AFIP
$resultado = $fe->enviar($feCabecera, $feDetRequest);

// Procesar la respuesta
if ($resultado->getResultado() == 'A') {
    echo json_encode(['success' => true, 'cae' => $resultado->getCAE()]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al emitir la Nota de Crédito']);
}
?>
