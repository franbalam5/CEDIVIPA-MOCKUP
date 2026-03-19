<?php
// config/db.php
$host = '127.0.0.1';
$dbname = 'MOCKUP'; // Asegúrate de que tu BD se llame así
$username = 'root'; 
$password = 'superbatman1'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>