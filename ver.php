<?php
// ver.php
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM tickets WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Ticket no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Ticket #<?= $ticket['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans p-8 flex justify-center items-start min-h-screen pt-20">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl border-t-4 border-blue-600">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($ticket['titulo']) ?></h2>
            <span class="text-sm text-gray-500 font-bold">Ticket #<?= $ticket['id'] ?></span>
        </div>
        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase mb-2">Descripción del problema:</h3>
            <div class="bg-gray-50 p-4 rounded border border-gray-200 whitespace-pre-wrap text-gray-800"><?= htmlspecialchars($ticket['descripcion']) ?></div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <span class="block text-sm font-bold text-gray-700 uppercase mb-1">Estado:</span>
                <?php 
                    $colorClase = 'bg-gray-200 text-gray-700';
                    if ($ticket['estado'] == 'abierto') $colorClase = 'bg-red-200 text-red-700';
                    if ($ticket['estado'] == 'en_progreso') $colorClase = 'bg-yellow-200 text-yellow-700';
                    if ($ticket['estado'] == 'cerrado') $colorClase = 'bg-green-200 text-green-700';
                ?>
                <span class="<?= $colorClase ?> py-1 px-3 rounded-full text-xs font-bold uppercase inline-block">
                    <?= str_replace('_', ' ', $ticket['estado']) ?>
                </span>
            </div>
            <div>
                <span class="block text-sm font-bold text-gray-700 uppercase mb-1">Fecha de creación:</span>
                <span class="text-gray-800"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium transition duration-150">
                ← Volver al panel
            </a>
            <div class="space-x-2">
                <?php if ($ticket['estado'] != 'cerrado'): ?>
                    <a href="cerrar.php?id=<?= $ticket['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded shadow transition duration-200">
                        Cerrar Ticket
                    </a>
                <?php endif; ?>
                <a href="eliminar.php?id=<?= $ticket['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este ticket definitivamente?');" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded shadow transition duration-200">
                    Eliminar
                </a>
            </div>
        </div>
    </div>

</body>
</html>