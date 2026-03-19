<?php
// vistas/panel_estudiante.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';
$id_estudiante = $_SESSION['usuario_id'];

// 1. Consultar Casos para la Trivia (Gamificación) - ACTUALIZADO
// Ahora lee directo de la tabla casos donde el doctor ya puso un diagnóstico final
$sql_trivia = "SELECT id AS id_caso, titulo, descripcion 
               FROM casos 
               WHERE apto_para_trivia = 1 AND diagnostico_final IS NOT NULL
               ORDER BY fecha_creacion DESC";
$stmt_trivia = $pdo->query($sql_trivia);
$casos_trivia = $stmt_trivia->fetchAll(PDO::FETCH_ASSOC);

// 2. Consultar MIS CASOS como paciente
$sql_mis_casos = "SELECT c.*, u.nombre AS nombre_doctor 
                  FROM casos c 
                  LEFT JOIN usuarios u ON c.id_doctor_asignado = u.id 
                  WHERE c.id_paciente = :id_estudiante 
                  ORDER BY c.fecha_creacion DESC";
$stmt_mis = $pdo->prepare($sql_mis_casos);
$stmt_mis->execute(['id_estudiante' => $id_estudiante]);
$mis_casos = $stmt_mis->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal Estudiantil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 pb-12">

    <nav class="bg-indigo-800 text-white p-4 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Portal Odontológico - Área Estudiantil</h1>
            <div class="flex items-center space-x-4">
                <span>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm font-bold transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 p-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-indigo-500">
                <h2 class="text-xl font-bold mb-4 text-slate-700">📝 Solicitar Consulta Médica</h2>
                <p class="text-xs text-slate-500 mb-4">Como estudiante también puedes recibir atención en la clínica.</p>
                
                <?php if (isset($_GET['exito'])): ?>
                    <div class="bg-green-100 text-green-800 text-sm p-3 rounded mb-4">
                        <?php if($_GET['exito'] == 'seguimiento'): ?>
                            ¡Ticket de seguimiento enviado!
                        <?php elseif($_GET['exito'] == 'borrado'): ?>
                            ¡Caso y archivos eliminados exitosamente!
                        <?php else: ?>
                            ¡Tu reporte ha sido enviado a los profesores!
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="../acciones/crear_caso.php" class="space-y-4" enctype="multipart/form-data">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Motivo de consulta:</label>
                        <input type="text" name="titulo" required placeholder="Ej. Revisión de resina"
                               class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Síntomas:</label>
                        <textarea name="descripcion" rows="3" required placeholder="Describe tus molestias..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Evidencia (Opcional):</label>
                        <input type="file" name="archivos[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                        Enviar Reporte a Profesores
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-teal-500">
                <h2 class="text-lg font-bold mb-4 text-slate-700">🗂️ Mis Consultas</h2>
                <?php if (count($mis_casos) > 0): ?>
                    <div class="space-y-3">
                        <?php foreach ($mis_casos as $caso): ?>
                            <div class="border-b pb-2 <?php echo $caso['id_caso_padre'] ? 'bg-yellow-50 p-2 rounded' : ''; ?>">
                                <h3 class="font-bold text-sm text-slate-800">
                                    <?= htmlspecialchars($caso['titulo']) ?>
                                    <?php if($caso['id_caso_padre']): ?>
                                        <span class="text-xs text-yellow-600 ml-1">(Seguimiento)</span>
                                    <?php endif; ?>
                                </h3>
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-xs text-slate-500">Estado: <span class="uppercase font-bold"><?= str_replace('_', ' ', $caso['estado']) ?></span></p>
                                    <a href="detalle_paciente.php?id=<?= $caso['id'] ?>" class="text-xs text-teal-600 hover:text-teal-800 font-bold">Ver Detalles →</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400 italic">No has solicitado atención médica.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-yellow-400">
                <h2 class="text-2xl font-bold mb-2 text-slate-700">🎮 Simulador de Diagnósticos Clínicos</h2>
                <p class="text-sm text-slate-500 mb-6">Lee los síntomas reales de pacientes pasados e intenta adivinar el diagnóstico que dio el profesional a cargo.</p>
                
                <?php if (count($casos_trivia) > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($casos_trivia as $caso): ?>
                            <div class="border border-slate-200 rounded p-4 hover:bg-slate-50 shadow-sm flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-lg text-indigo-900 mb-2">Caso #<?= $caso['id_caso'] ?>: <?= htmlspecialchars($caso['titulo']) ?></h3>
                                    <p class="text-sm text-slate-600 mb-4 line-clamp-3"><?= htmlspecialchars($caso['descripcion']) ?></p>
                                </div>
                                <a href="jugar_trivia.php?id_trivia=<?= $caso['id_caso'] ?>" class="text-center bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded shadow transition mt-2">
                                    Resolver Caso Médico
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-slate-50 p-6 rounded text-center text-slate-500">
                        Los profesores aún no han subido casos a la zona de práctica o faltan diagnósticos finales.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>