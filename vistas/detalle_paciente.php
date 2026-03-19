<?php
// vistas/detalle_paciente.php
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['paciente', 'estudiante'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id_caso = $_GET['id'];
$id_usuario = $_SESSION['usuario_id'];
$enlace_volver = ($_SESSION['rol'] === 'estudiante') ? 'panel_estudiante.php' : 'panel_paciente.php';

$sql_caso = "SELECT * FROM casos WHERE id = :id_caso AND id_paciente = :id_usuario";
$stmt_caso = $pdo->prepare($sql_caso);
$stmt_caso->execute(['id_caso' => $id_caso, 'id_usuario' => $id_usuario]);
$caso = $stmt_caso->fetch(PDO::FETCH_ASSOC);

if (!$caso) die("Caso no encontrado.");

$sql_bitacora = "SELECT a.*, u.nombre AS autor, u.rol 
                 FROM actualizaciones_casos a 
                 JOIN usuarios u ON a.id_usuario = u.id 
                 WHERE a.id_caso = :id_caso 
                 ORDER BY a.fecha_actualizacion ASC";
$stmt_bitacora = $pdo->prepare($sql_bitacora);
$stmt_bitacora->execute(['id_caso' => $id_caso]);
$actualizaciones = $stmt_bitacora->fetchAll(PDO::FETCH_ASSOC);

// Archivos del caso
$sql_adj_caso = "SELECT * FROM adjuntos WHERE id_caso = :id_caso AND id_actualizacion IS NULL";
$stmt_adj_caso = $pdo->prepare($sql_adj_caso);
$stmt_adj_caso->execute(['id_caso' => $id_caso]);
$adjuntos_caso = $stmt_adj_caso->fetchAll(PDO::FETCH_ASSOC);

// Archivos de las actualizaciones
$sql_adj_act = "SELECT * FROM adjuntos WHERE id_caso = :id_caso AND id_actualizacion IS NOT NULL";
$stmt_adj_act = $pdo->prepare($sql_adj_act);
$stmt_adj_act->execute(['id_caso' => $id_caso]);
$adjuntos_act_raw = $stmt_adj_act->fetchAll(PDO::FETCH_ASSOC);

$adjuntos_por_act = [];
foreach ($adjuntos_act_raw as $adj) {
    $adjuntos_por_act[$adj['id_actualizacion']][] = $adj;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Caso #<?= $caso['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 pb-12">

    <nav class="bg-teal-700 text-white p-4 shadow-md">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Detalle de mi Reporte</h1>
            <a href="<?= $enlace_volver ?>" class="text-teal-200 hover:text-white transition font-medium">← Volver a mi historial</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-8 p-4">
        
        <div class="flex justify-end mb-4">
            <a href="../acciones/generar_pdf.php?id=<?= $caso['id'] ?>" class="inline-flex items-center bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                📄 Exportar Mi Historial a PDF
            </a>
        </div>

        <div class="bg-white p-6 rounded-t-lg shadow border-b border-slate-200 border-t-4 border-teal-500">
            <h2 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($caso['titulo']) ?></h2>
            <div class="bg-slate-50 p-4 mt-4 rounded border border-slate-200 text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($caso['descripcion']) ?></div>
            
            <?php if (count($adjuntos_caso) > 0): ?>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-sm font-bold text-slate-700 mb-3">📎 Archivos adjuntos a este reporte:</p>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($adjuntos_caso as $adjunto): ?>
                            <a href="../<?= htmlspecialchars($adjunto['ruta_archivo']) ?>" target="_blank" class="inline-flex items-center bg-teal-50 hover:bg-teal-100 border border-teal-200 text-teal-700 font-bold py-1 px-3 rounded shadow-sm transition text-sm">
                                📄 <?= htmlspecialchars($adjunto['nombre_original']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 shadow mb-6 rounded-b-lg">
            <h3 class="text-lg font-bold text-slate-700 mb-4 border-b pb-2">Indicaciones del Profesional</h3>
            
            <?php if (count($actualizaciones) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($actualizaciones as $nota): ?>
                        <div class="p-4 rounded border bg-blue-50 border-blue-200">
                            <span class="font-bold text-sm text-blue-800">👨‍⚕️ <?= htmlspecialchars($nota['autor']) ?></span>
                            <span class="text-xs text-slate-400 ml-2"><?= date('d/m/Y H:i', strtotime($nota['fecha_actualizacion'])) ?></span>
                            <p class="text-slate-700 text-sm mt-2 whitespace-pre-wrap"><?= htmlspecialchars($nota['mensaje']) ?></p>
                            
                            <?php if (isset($adjuntos_por_act[$nota['id']])): ?>
                                <div class="mt-3 pt-3 border-t border-blue-200">
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($adjuntos_por_act[$nota['id']] as $adjunto): ?>
                                            <a href="../<?= htmlspecialchars($adjunto['ruta_archivo']) ?>" target="_blank" class="text-xs font-bold inline-flex items-center bg-white px-2 py-1 rounded border border-blue-300 text-blue-700 hover:bg-blue-100 transition shadow-sm">
                                                📎 <?= htmlspecialchars($adjunto['nombre_original']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-400 italic">El doctor aún no ha dejado indicaciones en este caso.</p>
            <?php endif; ?>
        </div>

        <?php if ($caso['estado'] !== 'resuelto'): ?>
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-yellow-500">
                <h3 class="text-lg font-bold text-slate-800 mb-2">⚠️ ¿Las molestias persisten o hay una actualización?</h3>
                <p class="text-sm text-slate-500 mb-4">Usa este formulario para levantar un reporte de seguimiento enlazado a tu doctor.</p>
                
                <form method="POST" action="../acciones/crear_seguimiento.php" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" name="id_caso_padre" value="<?= $caso['id'] ?>">
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Título del seguimiento:</label>
                        <input type="text" name="titulo" required value="Seguimiento: <?= htmlspecialchars($caso['titulo']) ?>"
                               class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Nuevo reporte de síntomas:</label>
                        <textarea name="descripcion" rows="4" required placeholder="Describe qué ha cambiado..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Evidencia Nueva (Opcional):</label>
                        <input type="file" name="archivos[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                    </div>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded shadow transition">
                        Generar Ticket de Seguimiento
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($caso['estado'] === 'resuelto'): ?>
            <div class="bg-red-50 p-6 rounded-lg shadow border-t-4 border-red-500 mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-red-800">Eliminar Expediente</h3>
                    <p class="text-sm text-red-600">Al borrar este caso, se eliminarán permanentemente todos los mensajes y los archivos adjuntos serán borrados del servidor para liberar espacio.</p>
                </div>
                <form method="POST" action="../acciones/borrar_caso.php" onsubmit="return confirm('¿Estás completamente seguro de borrar este caso y sus evidencias? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="id_caso" value="<?= $caso['id'] ?>">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow transition whitespace-nowrap">
                        🗑️ Borrar Caso Definitivamente
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>