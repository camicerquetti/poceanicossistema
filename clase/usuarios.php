<?php
class Usuario {
    private $conn;
    private $tabla = 'usuarios';
    public $direccion; // Añadir esta línea

    public $id;
    public $usuario;
    public $nombre;
    public $apellido;
    public $email;
    public $cargo;
    public $rol;
    public $avatar;
    public $contraseña;

    // Constructor con la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener los datos del usuario
    public function obtenerDatos() {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $this->usuario = $fila['usuario'];
            $this->nombre = $fila['nombre'];
            $this->apellido = $fila['apellido'];
            $this->email = $fila['email'];
            $this->cargo = $fila['cargo'];
            $this->rol = $fila['rol'];
            $this->direccion = $fila['direccion'];
            
        }
    }

    // Método para verificar las credenciales de login
    public function verificarCredenciales($usuario, $contraseña) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $usuario); // Parametro tipo string
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            // Verifica la contraseña
            if (password_verify($contraseña, $fila['contraseña'])) {
                return $fila; // Devuelve los datos del usuario si las credenciales son correctas
            }
        }
        return false; // Retorna falso si no se encuentran coincidencias o la contraseña es incorrecta
    }

    public function actualizarDatos() {
        $query = "UPDATE usuarios SET 
                    usuario = ?, 
                    nombre = ?, 
                    apellido = ?, 
                    email = ?, 
                    cargo = ?, 
                    rol = ?, 
                    contraseña = ?
                  WHERE id = ?";
    
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
    
        if ($stmt === false) {
            // Si no se pudo preparar la consulta, muestra un error detallado con mysqli_error()
            echo "Error al preparar la consulta: " . $this->conn->error;
            return false;
        }
    
        // Asignar los parámetros a los valores
        $stmt->bind_param('sssssssi', 
            $this->usuario, 
            $this->nombre, 
            $this->apellido, 
            $this->email, 
            $this->cargo, 
            $this->rol,
            $this->contraseña, 
            $this->id
        );
    
        // Si la contraseña está vacía, no la actualizamos
        if (empty($this->contraseña)) {
            $this->contraseña = null; // En este caso, debes asignar un valor nulo a la contraseña
        }
    
        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;
        } else {
            // Si la ejecución falla, muestra el error detallado con mysqli_error()
            echo "Error al ejecutar la consulta: " . $this->conn->error;
            return false;
        }
    }

    

    
     // Método para agregar un nuevo usuario
     public function agregarUsuario() {
        // Encriptar la contraseña
        $hashed_password = password_hash($this->contraseña, PASSWORD_BCRYPT);
        
        $query = "INSERT INTO " . $this->tabla . " (usuario, nombre, apellido, email, cargo, rol, direccion, contraseña)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssss", $this->usuario, $this->nombre, $this->apellido, $this->email, $this->cargo, $this->rol, $this->direccion, $hashed_password);

        if ($stmt->execute()) {
            return true; // Si la inserción es exitosa
        }
        return false; // Si hubo un error
    }
    // Método para obtener todos los usuarios
    public function obtenerTodosUsuarios() {
        $query = "SELECT * FROM " . $this->tabla;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $usuarios = [];
        while ($row = $resultado->fetch_assoc()) {
            $usuarios[] = $row; // Agregar cada usuario al array
        }

        return $usuarios; // Retornar el array de usuarios
    }
    public function obtenerContraseñaActual() {
        $query = "SELECT contraseña FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['contraseña']; // Devuelve la contraseña actual
    }
    public function obtenerUsuarioPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc(); // Devuelve un array asociativo con los datos del usuario
        } else {
            return false; // Retorna falso si el usuario no existe
        }
    }
    
}
?>
