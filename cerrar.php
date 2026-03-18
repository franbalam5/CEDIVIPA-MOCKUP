<?php
// cerrar.php
require 'db.php';

// Verificamos si recibimos un ID por la URL (método GET)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Preparamos la consulta para actualizar el estado a 'cerrado'
    $sql = "UPDATE tickets SET estado = 'cerrado' WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['id' => $id])) {
        // Si todo sale bien, regresamos a la lista principal
        header("Location: index.php");
        exit;
    } else {
        echo "Error al actualizar el ticket.";
    }
} else {
    // Si alguien entra a cerrar.php sin un ID, lo regresamos al index
    header("Location: index.php");
    exit;
}
?>