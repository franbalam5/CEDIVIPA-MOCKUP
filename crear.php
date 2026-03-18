<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($titulo) && !empty($descripcion)) {
        $sql = "INSERT INTO tickets (titulo, descripcion) VALUES (:titulo, :descripcion)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute(['titulo' => $titulo, 'descripcion' => $descripcion])) {
            header("Location: index.php"); 
            exit;
        } else {
            $error = "Hubo un error al guardar el ticket.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans p-8 flex justify-center items-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-blue-600">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 text-center">Abrir un Nuevo Ticket</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="crear.php" class="space-y-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Título del problema:</label>
                <input type="text" name="titulo" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Descripción detallada:</label>
                <textarea name="descripcion" rows="5" required 
                          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="pt-4 flex items-center justify-between">
                <a href="index.php" class="text-sm text-gray-500 hover:text-gray-700 transition duration-150">
                    ← Cancelar y volver
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition duration-200">
                    Guardar Ticket
                </button>
            </div>
        </form>
    </div>

</body>
</html>