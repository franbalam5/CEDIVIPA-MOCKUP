<?php
// eliminar.php
require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Preparamos la consulta para eliminar el registro
    $sql = "DELETE FROM tickets WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['id' => $id])) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error al eliminar el ticket.";
    }
} else {
    header("Location: index.php");
    exit;
}
?>