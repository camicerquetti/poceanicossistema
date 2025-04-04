<?php
class Compra {
    private $conn;

    // Constructor para establecer la conexión
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insertarCompra($fecha, $proveedor, $estado, $metodo_pago, $categoria_pago, $descripcion, $subtotal, $iva, $total, $productos_seleccionados, $vendedor, $cuenta) {
        // 1. Insertar la compra principal en la tabla 'compras'
        $query_compra = "INSERT INTO compras (emision, proveedor, categoria, subtotal, descuento, cantidad, total, vencimientoPago, tipoCompra, producto, precio, iva, notaInterna, contador, estado, vendedor, cuenta)
                         VALUES ('$fecha', '$proveedor', '$categoria_pago', '$subtotal', 0, 0, '$total', NULL, '$metodo_pago', '$descripcion', 0, '$iva', '$descripcion', '$vendedor', '$estado', '$vendedor', '$cuenta')";
        
        if ($this->conn->query($query_compra) === TRUE) {
            // Obtener el ID de la compra recién insertada
            $compra_id = $this->conn->insert_id;
            
            // 2. Insertar los productos en la tabla 'productos_compra'
            foreach ($productos_seleccionados as $producto) {
                $producto_id = $producto['producto_id']; // ID del producto
                $cantidad = $producto['cantidad']; // Cantidad
                $total_producto = $producto['total']; // Total calculado (cantidad * precio_unitario)
                
                // Obtener el precio unitario (costo) del producto
                $query_precio = "SELECT Costo FROM producto WHERE id = '$producto_id'";
                $result_precio = $this->conn->query($query_precio);
                $precio_unitario = 0;
                
                if ($result_precio->num_rows > 0) {
                    $producto_data = $result_precio->fetch_assoc();
                    $precio_unitario = $producto_data['Costo'];
                }
    
                // Inserción en la tabla productos_compra
                $query_detalle = "INSERT INTO productos_compra (compra_id, producto_id, cantidad, precio_unitario, total)
                                  VALUES ('$compra_id', '$producto_id', '$cantidad', '$precio_unitario', '$total_producto')";
        
                if (!$this->conn->query($query_detalle)) {
                    echo "Error al insertar detalle del producto: " . $this->conn->error;
                }
            }
            
            // 3. Actualizar el saldo de la cuenta
            $query_cuenta = "SELECT * FROM cuentas WHERE Cuenta = '$cuenta'";
            $result_cuenta = $this->conn->query($query_cuenta);
            
            if ($result_cuenta->num_rows > 0) {
                $cuenta_data = $result_cuenta->fetch_assoc();
                $nuevo_saldo = $cuenta_data['Saldo'] - $total; // Restar el total de la compra al saldo
                $query_actualizar_saldo = "UPDATE cuentas SET Saldo = '$nuevo_saldo' WHERE Cuenta = '$cuenta'";
                
                if (!$this->conn->query($query_actualizar_saldo)) {
                    echo "Error al actualizar saldo de la cuenta: " . $this->conn->error;
                }
            } else {
                echo "La cuenta seleccionada no existe.";
            }
    
            echo "Compra insertada correctamente.";
        } else {
            echo "Error al insertar la compra: " . $this->conn->error;
        }
    }
    
    

// Método para obtener las compras con paginación
public function obtenerCompras($filtro, $limit, $offset) {
    $query = "SELECT * FROM compras WHERE 
              CAST(id AS CHAR) LIKE ? OR 
              emision LIKE ? OR 
              vencimiento LIKE ? OR 
              proveedor LIKE ? OR 
              CAST(total AS CHAR) LIKE ? OR 
              categoria LIKE ? OR 
              descripcion LIKE ? OR 
              estado LIKE ? OR 
              metodo_pago LIKE ? 
              LIMIT ? OFFSET ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        'ssssssssssi', 
        $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, 
        $limit, $offset
    );
    
    $stmt->execute();
    return $stmt->get_result();
}

    // Método para contar el total de compras
    public function contarCompras($filtro) {
        $query = "SELECT COUNT(*) AS total FROM compras WHERE proveedor LIKE ? OR producto LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    public function obtenerCompraPorId($id) {
        $sql = "SELECT * FROM compras WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
}
?>
