<?php
// vistas/expediente.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: panel_doctor.php");
    exit;
}

$id_caso = $_GET['id'];
$id_doctor = $_SESSION['usuario_id'];

$sql_caso = "SELECT c.*, u.nombre AS nombre_paciente 
             FROM casos c 
             JOIN usuarios u ON c.id_paciente = u.id 
             WHERE c.id = :id_caso AND c.id_doctor_asignado = :id_doctor";
$stmt_caso = $pdo->prepare($sql_caso);
$stmt_caso->execute(['id_caso' => $id_caso, 'id_doctor' => $id_doctor]);
$caso = $stmt_caso->fetch(PDO::FETCH_ASSOC);

if (!$caso) die("Acceso denegado o el caso no existe.");

$sql_bitacora = "SELECT a.*, u.nombre AS autor, u.rol 
                 FROM actualizaciones_casos a 
                 JOIN usuarios u ON a.id_usuario = u.id 
                 WHERE a.id_caso = :id_caso 
                 ORDER BY a.fecha_actualizacion ASC";
$stmt_bitacora = $pdo->prepare($sql_bitacora);
$stmt_bitacora->execute(['id_caso' => $id_caso]);
$actualizaciones = $stmt_bitacora->fetchAll(PDO::FETCH_ASSOC);

// Consultar los archivos adjuntos del reporte original
$sql_adj_caso = "SELECT * FROM adjuntos WHERE id_caso = :id_caso AND id_actualizacion IS NULL";
$stmt_adj_caso = $pdo->prepare($sql_adj_caso);
$stmt_adj_caso->execute(['id_caso' => $id_caso]);
$adjuntos_caso = $stmt_adj_caso->fetchAll(PDO::FETCH_ASSOC);

// Consultar todos los archivos adjuntos de las actualizaciones y agruparlos
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
    <title>Expediente #<?= $caso['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 pb-12">

    <nav class="bg-blue-800 text-white p-4 shadow-md">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Expediente Clínico</h1>
            <a href="panel_doctor.php" class="text-blue-200 hover:text-white transition font-medium">← Volver al Panel</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-8 p-4">
        
        <div class="flex justify-end mb-4">
            <a href="../acciones/generar_pdf.php?id=<?= $caso['id'] ?>" class="inline-flex items-center bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded shadow transition text-sm">
                📄 Descargar Expediente Completo (PDF)
            </a>
        </div>

        <div class="bg-white p-6 rounded-t-lg shadow border-b border-slate-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800"><?= htmlspecialchars($caso['titulo']) ?></h2>
                    <p class="text-sm text-slate-500 mt-1"><strong>Paciente:</strong> <?= htmlspecialchars($caso['nombre_paciente']) ?></p>
                </div>
                <span class="bg-blue-100 text-blue-800 text-sm font-bold px-3 py-1 rounded-full uppercase shadow-sm">
                    <?= str_replace('_', ' ', $caso['estado']) ?>
                </span>
            </div>
            <div class="bg-slate-50 p-4 rounded border border-slate-200 text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($caso['descripcion']) ?></div>
            
            <?php if (count($adjuntos_caso) > 0): ?>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-sm font-bold text-slate-700 mb-3">📎 Evidencia clínica inicial:</p>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($adjuntos_caso as $adjunto): ?>
                            <a href="../<?= htmlspecialchars($adjunto['ruta_archivo']) ?>" target="_blank" class="inline-flex items-center bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 font-bold py-1 px-3 rounded shadow-sm transition text-sm">
                                📄 <?= htmlspecialchars($adjunto['nombre_original']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 shadow">
            <h3 class="text-lg font-bold text-slate-700 mb-4 border-b pb-2">Bitácora de Seguimiento</h3>
            
            <div class="space-y-4 mb-6">
                <?php if (count($actualizaciones) > 0): ?>
                    <?php foreach ($actualizaciones as $nota): ?>
                        <?php 
                            $esDoctor = $nota['rol'] == 'doctor';
                            $bgClass = $esDoctor ? 'bg-blue-50 border-blue-200 ml-8' : 'bg-teal-50 border-teal-200 mr-8';
                            $nombreAutor = $esDoctor ? '👨‍⚕️ ' . $nota['autor'] : '👤 ' . $nota['autor'];
                        ?>
                        <div class="p-4 rounded border <?= $bgClass ?>">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-sm <?= $esDoctor ? 'text-blue-800' : 'text-teal-800' ?>">
                                    <?= htmlspecialchars($nombreAutor) ?>
                                </span>
                                <span class="text-xs text-slate-400"><?= date('d/m/Y H:i', strtotime($nota['fecha_actualizacion'])) ?></span>
                            </div>
                            <p class="text-slate-700 text-sm whitespace-pre-wrap"><?= htmlspecialchars($nota['mensaje']) ?></p>
                            
                            <?php if (isset($adjuntos_por_act[$nota['id']])): ?>
                                <div class="mt-3 pt-3 border-t <?= $esDoctor ? 'border-blue-200' : 'border-teal-200' ?>">
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($adjuntos_por_act[$nota['id']] as $adjunto): ?>
                                            <a href="../<?= htmlspecialchars($adjunto['ruta_archivo']) ?>" target="_blank" class="text-xs font-bold inline-flex items-center bg-white px-2 py-1 rounded border <?= $esDoctor ? 'border-blue-300 text-blue-700 hover:bg-blue-100' : 'border-teal-300 text-teal-700 hover:bg-teal-100' ?> transition shadow-sm">
                                                📎 <?= htmlspecialchars($adjunto['nombre_original']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-slate-400 italic text-center py-4">Aún no hay actualizaciones en este caso.</p>
                <?php endif; ?>
            </div>

            <?php if ($caso['estado'] !== 'resuelto'): ?>
                <form method="POST" action="../acciones/agregar_actualizacion.php" class="mt-4 border-t pt-4" enctype="multipart/form-data">
                    <input type="hidden" name="id_caso" value="<?= $caso['id'] ?>">
                    
                    <label class="block text-sm font-bold text-slate-700 mb-2">Agregar nota médica o indicación:</label>
                    <textarea name="mensaje" rows="3" required placeholder="Escribe aquí los avances del tratamiento..."
                              class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"></textarea>
                    
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="w-full md:w-auto">
                            <input type="file" name="archivos[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                                   class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">
                            Guardar Nota
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-green-100 text-green-800 p-4 rounded text-center text-sm font-bold">
                    Este caso ha sido marcado como resuelto. Ya no se pueden agregar más notas.
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($caso['estado'] !== 'resuelto'): ?>
            <div class="bg-white p-6 rounded-b-lg shadow border-t border-slate-200 flex justify-between items-center bg-slate-50">
                <span class="text-sm text-slate-500">¿El tratamiento ha concluido?</span>
                <a href="resolver_caso.php?id=<?= $caso['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded shadow transition">
                    Marcar como Resuelto ✔️
                </a>
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