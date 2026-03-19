<?php
// setup.php
require 'config/db.php';

$usuarios_prueba = [
    ['nombre' => 'Juan Paciente', 'email' => 'paciente@test.com', 'password' => '12345', 'rol' => 'paciente'],
    ['nombre' => 'Dra. Elena', 'email' => 'doctor@test.com', 'password' => '12345', 'rol' => 'doctor'],
    ['nombre' => 'Estudiante Alex', 'email' => 'estudiante@test.com', 'password' => '12345', 'rol' => 'estudiante']
];

foreach ($usuarios_prueba as $u) {
    // Encriptamos la contraseña con el algoritmo BCRYPT
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre' => $u['nombre'],
        'email' => $u['email'],
        'password' => $hash,
        'rol' => $u['rol']
    ]);
}

echo "Usuarios de prueba creados exitosamente. Ya puedes borrar este archivo.";
?>