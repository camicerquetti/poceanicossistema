<?php
class Compra {
    private $conn;

    // Constructor para establecer la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para insertar una compra
    public function insertarCompra($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $total, $productos_seleccionados, $vendedor, $cuenta) {
        // Convertir los productos seleccionados en un array asociativo y luego en formato JSON
        $productos_json = json_encode($productos_seleccionados);

        // 1. Insertar la compra en la tabla 'comprax' con los productos en formato JSON
        $query = "INSERT INTO comprax (emision, proveedor, categoria, subtotal, descuento, cantidad, total, vencimientoPago, tipoCompra, producto, precio, iva, notaInterna, contador, estado, vendedor, productos, cuenta)
                  VALUES ('$fecha', '$proveedor', '$categoria_pago', '$subtotal', 0, 0, '$total', NULL, '$metodo_pago', '$descripcion', NULL, '$iva', '$descripcion', '$vendedor', '$estado', '$vendedor', '$productos_json', '$cuenta')";

        if ($this->conn->query($query) === TRUE) {
            echo "Compra insertada correctamente.";
        } else {
            echo "Error al insertar la compra: " . $this->conn->error;
        }
    }

    // Método para obtener las compras con paginación
    public function obtenerCompras($filtro, $limit, $offset) {
        $query = "SELECT * FROM comprax WHERE proveedor LIKE ? OR producto LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssii', $filtro, $filtro, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar el total de compras
    public function contarCompras($filtro) {
        $query = "SELECT COUNT(*) AS total FROM comprax WHERE proveedor LIKE ? OR producto LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
}
?>
