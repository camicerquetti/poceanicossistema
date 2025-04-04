<?php
class Cliente {
    private $conn;

    // Constructor recibe la conexión a la base de datos
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para obtener los clientes con su deuda
    public function obtenerClientesConDeudas($filtro = '%%', $estado_filtro = '%%', $limite = 10, $offset = 0) {
        // Consulta SQL para obtener el nombre, razón social y el total de la deuda
        $sql = "
            SELECT c.id, c.nombre, c.razon_social, SUM(i.monto) AS saldo_deuda, i.estado
            FROM clientes c
            LEFT JOIN ingresos i ON c.nombre = i.cliente
            WHERE (i.estado IN ('pendiente', 'vencido') OR i.estado IS NULL) 
              AND (c.nombre LIKE ? OR c.razon_social LIKE ?)
            GROUP BY c.id
            LIMIT ? OFFSET ?";

        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $filtro, $filtro, $limite, $offset);

        // Ejecutar la consulta
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Retornar el resultado
        return $resultado;
    }

    // Método para contar los clientes con deudas
    public function contarClientesConDeudas($filtro = '%%', $estado_filtro = '%%') {
        // Consulta SQL para contar el número de clientes con deudas
        $sql = "
            SELECT COUNT(DISTINCT c.id) AS total
            FROM clientes c
            LEFT JOIN ingresos i ON c.nombre = i.cliente
            WHERE (i.estado IN ('pendiente', 'vencido') OR i.estado IS NULL) 
              AND (c.nombre LIKE ? OR c.razon_social LIKE ?)";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $filtro, $filtro);

        // Ejecutar la consulta
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();

        return $total;
    }
}
?>
