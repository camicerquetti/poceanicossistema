<?php
class Producto {
    private $conn;
    private $table = 'producto'; // Nombre de la tabla en la base de datos

    // Constructor para inicializar la conexión
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener los productos con un filtro opcional
    public function obtenerProductos($filtro = "", $productos_por_pagina = 10, $offset = 0) {
        // Consulta SQL con filtro por nombre o código, y paginación
        $query = "SELECT * FROM " . $this->table . " WHERE Nombre LIKE ? OR Codigo LIKE ? LIMIT ? OFFSET ?";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            die('Error al preparar la consulta: ' . $this->conn->error);
        }
    
        // Asociar los parámetros al filtro, el número de productos por página y el offset
        $filtro_param = "%" . $filtro . "%"; // Agregar los porcentajes para el LIKE
        $stmt->bind_param('ssii', $filtro_param, $filtro_param, $productos_por_pagina, $offset); // 'ssii' indica dos strings y dos enteros
        
        // Ejecutar la consulta
        $stmt->execute(); 
    
        // Obtener el resultado
        $result = $stmt->get_result();
    
        return $result; // Retornar el objeto mysqli_result
    }
    
    // Agregar una función para contar el número total de productos
public function contarProductos($filtro = "") {
    $query = "SELECT COUNT(*) AS total FROM " . $this->table . " WHERE Nombre LIKE ? OR Codigo LIKE ?";

    $stmt = $this->conn->prepare($query);
    if ($stmt === false) {
        die('Error al preparar la consulta: ' . $this->conn->error);
    }

    $stmt->bind_param('ss', $filtro, $filtro);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
public function actualizarProducto($id, $nombre, $tipo, $tipo_producto_servicio, $proveedor, $codigo, $deposito_1, $general, $stock_total, $costo, $iva_compras, $precio_venta, $iva_ventas, $descripcion, $activo, $mostrar_en_ventas, $mostrar_en_compras, $imagen) {
    $sql = "UPDATE producto SET 
            Nombre = ?, Tipo = ?, Tipo_Producto_Servicio = ?, Proveedor = ?, Codigo = ?, 
            Deposito_1 = ?, General = ?, Stock_Total = ?, Costo = ?, IVA_Compras = ?, 
            Precio_de_Venta = ?, IVA_Ventas = ?, Descripcion = ?, Activo = ?, 
            Mostrar_en_Ventas = ?, Mostrar_en_Compras = ?, imagen = ?
            WHERE id = ?";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('ssssssssssssssssi', $nombre, $tipo, $tipo_producto_servicio, $proveedor, $codigo, $deposito_1, $general, $stock_total, $costo, $iva_compras, $precio_venta, $iva_ventas, $descripcion, $activo, $mostrar_en_ventas, $mostrar_en_compras, $imagen, $id);
    $stmt->execute();
}
 // Método para obtener un producto por ID
 public function obtenerProductoPorID($id) {
    $sql = "SELECT * FROM producto WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' indica que el parámetro es un entero
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado;
}
// Método para insertar un producto
public function insertarProducto($nombre, $tipo, $tipo_producto_servicio, $proveedor, $codigo, $deposito_1, $general, $stock_total, $costo, $iva_compras, $precio_venta, $iva_ventas, $descripcion, $activo, $mostrar_en_ventas, $mostrar_en_compras, $imagen) {
    // Preparar la consulta SQL para insertar el producto
    $sql = "INSERT INTO productos (Nombre, Tipo, Tipo_Producto_Servicio, Proveedor, Codigo, Deposito_1, General, Stock_Total, Costo, IVA_Compras, Precio_de_Venta, IVA_Ventas, Descripcion, Activo, Mostrar_en_Ventas, Mostrar_en_Compras, imagen) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar la declaración
    $stmt = $this->conn->prepare($sql);

    // Vincular los parámetros
    $stmt->bind_param("ssssssssddddssss", $nombre, $tipo, $tipo_producto_servicio, $proveedor, $codigo, $deposito_1, $general, $stock_total, $costo, $iva_compras, $precio_venta, $iva_ventas, $descripcion, $activo, $mostrar_en_ventas, $mostrar_en_compras, $imagen);

    // Ejecutar la consulta
    $stmt->execute();

    // Cerrar la declaración
    $stmt->close();
}

}

?>
