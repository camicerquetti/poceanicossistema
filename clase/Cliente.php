<?php
class Cliente {
    private $conn;

    // Constructor: establece la conexión a la base de datos
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para insertar un cliente en la base de datos
    public function insertarCliente($cliente, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $pagina_web, $saldo_inicial, $observaciones) {
        $query = "INSERT INTO clientes (cliente, nombre, apellido, email, telefono, telefono2, direccion, localidad, provincia, dni, cuit, condicion_iva, razon_social, domicilio_fiscal, localidad_fiscal, provincia_fiscal, codigo_postal_fiscal, pagina_web, saldo_inicial, observaciones) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssssssssssssssssdss', $cliente, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $pagina_web, $saldo_inicial, $observaciones);
        $stmt->execute();
        $stmt->close();
    }

    public function obtenerClientes($filtro, $clientes_por_pagina, $offset) {
        $sql = "SELECT * FROM clientes WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $filtro, $filtro, $filtro, $clientes_por_pagina, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    

    // Método para contar el total de clientes
    public function contarClientes($filtro = '%%') {
        $query = "SELECT COUNT(*) AS total FROM clientes WHERE cliente LIKE ? OR nombre LIKE ? OR apellido LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sss', $filtro, $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }

    // Método para obtener un cliente por ID
    public function obtenerClientePorId($id) {
        $query = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
        return $cliente;
    }

    // Método para actualizar los datos de un cliente
    public function actualizarCliente($id, $cliente, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $pagina_web, $saldo_inicial, $observaciones) {
        $query = "UPDATE clientes SET cliente = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, telefono2 = ?, direccion = ?, localidad = ?, provincia = ?, dni = ?, cuit = ?, condicion_iva = ?, razon_social = ?, domicilio_fiscal = ?, localidad_fiscal = ?, provincia_fiscal = ?, codigo_postal_fiscal = ?, pagina_web = ?, saldo_inicial = ?, observaciones = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssssssssssssssssdssi', $cliente, $nombre, $apellido, $email, $telefono, $telefono2, $direccion, $localidad, $provincia, $dni, $cuit, $condicion_iva, $razon_social, $domicilio_fiscal, $localidad_fiscal, $provincia_fiscal, $codigo_postal_fiscal, $pagina_web, $saldo_inicial, $observaciones, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Método para eliminar un cliente
    public function eliminarCliente($id) {
        $query = "DELETE FROM clientes WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
    
}
?>
