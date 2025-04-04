<?php
class OtrosIngreso {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para obtener los otros ingresos con filtro y paginación
    public function obtenerOtrosIngresos($filtro, $limit, $offset) {
        $sql = "SELECT id, ingreso, categoria, cuenta, vendedor, total, descripcion, metodo_cobro 
                FROM otros_ingresos 
                WHERE ingreso LIKE ? 
                OR cuenta LIKE ? 
                OR categoria LIKE ? 
                OR vendedor LIKE ? 
                OR total LIKE ? 
                OR descripcion LIKE ? 
                OR metodo_cobro LIKE ? 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssssii', $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, $filtro, $limit, $offset);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    // Método para contar los otros ingresos
    public function contarOtrosIngresos($filtro) {
        $sql = "SELECT COUNT(*) 
                FROM otros_ingresos 
                WHERE ingreso LIKE ? OR cuenta LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        return $total;
    }
     // Método para insertar un nuevo ingreso
     public function insertarIngreso($ingreso, $categoria, $cuenta, $vendedor, $total, $descripcion) {
        $sql = "INSERT INTO otros_ingresos (ingreso, categoria, cuenta, vendedor, total, descripcion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        // Verificar si la preparación fue exitosa
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $this->conn->error);
        }

        // Vincular los parámetros a la consulta preparada
        $stmt->bind_param('ssssds', $ingreso, $categoria, $cuenta, $vendedor, $total, $descripcion);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;  // Éxito
        } else {
            return false; // Error al insertar
        }
    }
    // Función para eliminar un registro de otros ingresos
   // Función para eliminar un registro de otros ingresos
public function eliminarOtroIngreso($id) {
    // Verificamos si $id es un valor numérico
    if (!is_numeric($id)) {
        return false;
    }

    // Consulta SQL para eliminar un ingreso
    $sql = "DELETE FROM otros_ingresos WHERE id = ?";
    $stmt = $this->conn->prepare($sql);

    // Verificamos si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $this->conn->error);
    }

    // Vinculamos el parámetro
    $stmt->bind_param("i", $id);

    // Ejecutamos la consulta y verificamos si fue exitosa
    if ($stmt->execute()) {
        return true;
    } else {
        // Si hay un error, mostramos el mensaje de error
        echo "Error al ejecutar la eliminación: " . $this->conn->error;
        return false;
    }
}

    
}
?>
