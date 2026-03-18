<?php
require 'db.php';

$sql = "SELECT * FROM tickets ORDER BY fecha_creacion DESC";
$stmt = $pdo->query($sql);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans p-8">
    
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Panel de Tickets</h1>
            <a href="crear.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200">
                + Crear Nuevo Ticket
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left w-1/4">Título</th>
                        <th class="py-3 px-6 text-left w-1/3">Descripción</th>
                        <th class="py-3 px-6 text-center">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (count($tickets) > 0): ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-medium"><?= $ticket['id'] ?></td>
                            <td class="py-3 px-6 text-left font-semibold text-gray-800"><?= htmlspecialchars($ticket['titulo']) ?></td>
                            <td class="py-3 px-6 text-left truncate max-w-xs" title="Haz clic en 'Ver' para leer completa">
                                <?= htmlspecialchars($ticket['descripcion']) ?>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <?php 
                                    $colorClase = 'bg-gray-200 text-gray-700';
                                    if ($ticket['estado'] == 'abierto') $colorClase = 'bg-red-200 text-red-700';
                                    if ($ticket['estado'] == 'en_progreso') $colorClase = 'bg-yellow-200 text-yellow-700';
                                    if ($ticket['estado'] == 'cerrado') $colorClase = 'bg-green-200 text-green-700';
                                ?>
                                <span class="<?= $colorClase ?> py-1 px-3 rounded-full text-xs font-bold uppercase inline-block">
                                    <?= str_replace('_', ' ', $ticket['estado']) ?>
                               </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center space-x-2">
                                    <a href="ver.php?id=<?= $ticket['id'] ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded transition duration-150">
                                        Ver
                                    </a>
                                    
                                    <?php if ($ticket['estado'] != 'cerrado'): ?>
                                        <a href="cerrar.php?id=<?= $ticket['id'] ?>" class="text-xs bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded transition duration-150">
                                            Cerrar
                                        </a>
                                    <?php endif; ?>

                                    <a href="eliminar.php?id=<?= $ticket['id'] ?>" onclick="return confirm('¿Eliminar este ticket definitivamente?');" class="text-xs bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded transition duration-150">
                                        Borrar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-6 px-6 text-center text-gray-500 text-lg">No hay tickets registrados aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>