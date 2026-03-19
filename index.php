<?php
// index.php
session_start();
// Si el usuario ya tiene sesión activa, lo sacamos del login
if (isset($_SESSION['usuario_id'])) {
    header("Location: vistas/panel_" . $_SESSION['rol'] . ".php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso - Sistema Odontológico</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Portal Odontológico</h1>
            <p class="text-slate-500 text-sm mt-1">Ingresa tus credenciales</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm text-center">
                Correo o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <form method="POST" action="auth/login.php" class="space-y-4">
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Correo Electrónico:</label>
                <input type="email" name="email" required placeholder="ejemplo@test.com"
                       class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Contraseña:</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 text-xs text-slate-400 text-center">
            <p><strong>Cuentas de prueba (Pass: 12345):</strong></p>
            <p>paciente@test.com | doctor@test.com | estudiante@test.com</p>
        </div>
    </div>

</body>
</html>