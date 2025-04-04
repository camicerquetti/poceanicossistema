<?php
// Configuración de la base de datos
$servidor = "34.42.218.48"; // Nombre del servidor de la base de datos
$usuario = "productosoceanicos";       // Nombre de usuario de la base de datos
$password = "Oceanicos1234";         // Contraseña de la base de datos (deja vacío si no tiene)
$basededatos = "productosoceanicos"; // Reemplaza con el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servidor, $usuario, $password, $basededatos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>
