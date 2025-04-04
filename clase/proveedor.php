<?php
class Proveedor {
    private $conn;

    // Constructor para establecer la conexión con la base de datos
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para insertar un proveedor en la base de datos
    public function insertarProveedor(
        $proveedor, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, 
        $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, 
        $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, 
        $fecha_saldo_inicial, $saldo_inicial, $observaciones
    ) {
        $query = "INSERT INTO proveedores (proveedor, nombre, apellido, email, telefono, telefono2, direccion, localidad, provincia, dni, cuit, condicion_iva, razon_social, domicilio_fiscal, localidad_fiscal, provincia_fiscal, codigo_postal_fiscal, fecha_saldo_inicial, saldo_inicial, observaciones)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssssssssssds", $proveedor, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $fecha_saldo_inicial, $saldo_inicial, $observaciones);

        return $stmt->execute();
    }

    // Método para obtener todos los proveedores con filtrado y paginación
    public function obtenerProveedores($filtro, $limite, $offset) {
        $query = "SELECT * FROM proveedores WHERE proveedor LIKE ? OR nombre LIKE ? OR apellido LIKE ? ORDER BY id LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $filtro, $filtro, $filtro, $limite, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Método para contar el total de proveedores para la paginación
    public function contarProveedores($filtro) {
        $query = "SELECT COUNT(*) AS total FROM proveedores WHERE proveedor LIKE ? OR nombre LIKE ? OR apellido LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $filtro, $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
       // Método para obtener un proveedor por su ID
       public function obtenerProveedorPorID($id) {
        $query = "SELECT * FROM proveedores WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado;
    }

    // Método para actualizar los datos de un proveedor
    public function actualizarProveedor($id, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $observaciones) {
        $query = "UPDATE proveedores SET 
                    nombre = ?, 
                    apellido = ?, 
                    email = ?, 
                    telefono = ?, 
                    telefono2 = ?, 
                    direccion = ?, 
                    localidad = ?, 
                    provincia = ?, 
                    dni = ?, 
                    cuit = ?, 
                    condicion_iva = ?, 
                    razon_social = ?, 
                    domicilio_fiscal = ?, 
                    localidad_fiscal = ?, 
                    provincia_fiscal = ?, 
                    codigo_postal_fiscal = ?, 
                    observaciones = ? 
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssssssssssssi", 
            $nombre, 
            $apellido, 
            $email, 
            $telefono, 
            $telefono2, 
            $direccion, 
            $localidad, 
            $provincia, 
            $dni, 
            $cuit, 
            $condicion_iva, 
            $razon_social, 
            $domicilio_fiscal, 
            $localidad_fiscal, 
            $provincia_fiscal, 
            $codigo_postal_fiscal, 
            $observaciones, 
            $id);
        $stmt->execute();
        return $stmt->affected_rows;
    }

}
?>
