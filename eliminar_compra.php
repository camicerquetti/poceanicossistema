<?php

include('config.php');
include('clase/Compra.php');

// Verificar que se ha recibido el ID de la compra a eliminar
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $compra = new Compra($conn);
    
    // Llama al método para eliminar la compra (asegúrate de implementarlo en la clase Compra)
    if ($compra->eliminarCompra($id)) {
        header('Location: compras.php');
        exit();
    } else {
        echo "Error al eliminar la compra.";
    }
} else {
    echo "ID de compra no especificado.";
}
?>