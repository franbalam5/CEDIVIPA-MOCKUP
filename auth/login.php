<?php
// auth/login.php
session_start(); // ¡Vital! Inicia el gestor de sesiones de PHP
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Buscamos al usuario por su email
    $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario existe, verificamos que la contraseña coincida con el hash
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Guardamos los datos importantes en la sesión del servidor
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // Redirigimos según el rol
        if ($usuario['rol'] == 'paciente') header("Location: ../vistas/panel_paciente.php");
        if ($usuario['rol'] == 'doctor') header("Location: ../vistas/panel_doctor.php");
        if ($usuario['rol'] == 'estudiante') header("Location: ../vistas/panel_estudiante.php");
        exit;
    } else {
        // Credenciales inválidas
        header("Location: ../index.php?error=1");
        exit;
    }
}
?>