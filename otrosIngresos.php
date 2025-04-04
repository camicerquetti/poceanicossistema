<?php
class OtrosIngreso {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para obtener los otros ingresos con filtro y paginación
    public function obtenerOtrosIngresos($filtro, $limit, $offset) {
        $sql = "SELECT id, ingreso, categoria, cuenta, vendedor, total, descripcion,metodo_cobro
                FROM otros_ingresos 
                WHERE ingreso LIKE ? OR cuenta LIKE ? 
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $filtro, $filtro, $limit, $offset);
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
}
?>
