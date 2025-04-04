<?php
class NotaCredito {
    private $conn;

    // Constructor para la clase NotaCredito
    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Crear una nueva nota de crédito
    public function crearNotaCredito($factura_id, $razon_social, $cuit, $total_credito, $descripcion) {
        // Preparamos la consulta SQL
        $sql = "INSERT INTO notas_credito (factura_id, razon_social, cuit, total_credito, descripcion, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        // Preparamos y ejecutamos la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issds", $factura_id, $razon_social, $cuit, $total_credito, $descripcion);
        $stmt->execute();

        // Devolvemos el ID de la nota de crédito recién creada
        return $stmt->insert_id;
    }

    // Agregar productos a la nota de crédito
    public function agregarProductoNotaCredito($nota_credito_id, $producto_id, $cantidad_devuelta) {
        // Insertamos la devolución de productos en la tabla nota_credito_productos
        $sql = "INSERT INTO nota_credito_productos (nota_credito_id, producto_id, cantidad_devuelta)
                VALUES (?, ?, ?)";
        
        // Preparamos y ejecutamos la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $nota_credito_id, $producto_id, $cantidad_devuelta);
        $stmt->execute();
    }
}
?>
