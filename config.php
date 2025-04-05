<?php
// Configuración de la base de datos
$servidor = "botcdmg86r1dbzg5mpyr-mysql.services.clever-cloud.com"; // Nombre del servidor de la base de datos
$usuario = "upztvkwubpdw87yq";       // Nombre de usuario de la base de datos
$password = "upztvkwubpdw87yq";         // Contraseña de la base de datos (deja vacío si no tiene)
$basededatos = "botcdmg86r1dbzg5mpyr"; // Reemplaza con el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servidor, $usuario, $password, $basededatos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>
