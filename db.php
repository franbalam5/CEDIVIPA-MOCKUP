<?php
// db.php
$host = '127.0.0.1';
$dbname = 'sistema_tickets';
$username = 'root'; // Cambia si tu usuario es distinto
$password = 'superbatman1';     // Cambia si tienes contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Este mensaje lo borraremos después, es solo para probar
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>