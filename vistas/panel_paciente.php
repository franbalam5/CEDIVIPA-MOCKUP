<?php
// vistas/panel_paciente.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';
$id_paciente = $_SESSION['usuario_id'];

// Consultamos los casos de este paciente y buscamos el nombre del doctor (si ya fue asignado)
$sql = "SELECT c.*, u.nombre AS nombre_doctor 
        FROM casos c 
        LEFT JOIN usuarios u ON c.id_doctor_asignado = u.id 
        WHERE c.id_paciente = :id_paciente 
        ORDER BY c.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_paciente' => $id_paciente]);
$mis_casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Paciente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <nav class="bg-teal-700 text-white p-4 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Portal Odontológico - Pacientes</h1>
            <div class="flex items-center space-x-4">
                <span>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm font-bold transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 p-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow border-t-4 border-teal-500">
                <h2 class="text-xl font-bold mb-4 text-slate-700">📝 Reportar Nuevo Caso</h2>
                
                <?php if (isset($_GET['exito'])): ?>
                    <div class="bg-green-100 text-green-800 text-sm p-3 rounded mb-4">
                        <?php if($_GET['exito'] == 'seguimiento'): ?>
                            ¡Ticket de seguimiento creado con éxito! Tu doctor ha sido notificado.
                        <?php else: ?>
                            ¡Tu caso ha sido enviado y está en la lista de espera!
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="../acciones/crear_caso.php" class="space-y-4" enctype="multipart/form-data">
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">Motivo de consulta (Título):</label>
        <input type="text" name="titulo" required placeholder="Ej. Dolor en muela derecha"
               class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500">
    </div>
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">Detalles de los síntomas:</label>
        <textarea name="descripcion" rows="4" required placeholder="Describe desde cuándo tienes molestias..."
                  class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
    </div>
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">Evidencia (Opcional):</label>
        <p class="text-xs text-slate-500 mb-2">Adjunta una foto o radiografía (JPG, PNG o PDF).</p>
        <input type="file" name="archivos[]" multiple accept=".jpg,.jpeg,.png,.pdf"
               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
    </div>
    
    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded transition">
        Enviar Reporte Original
    </button>
</form>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-bold mb-4 text-slate-700">🗂️ Mi Historial Médico</h2>
                
                <?php if (count($mis_casos) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach ($mis_casos as $caso): ?>
                            <div class="border <?php echo $caso['id_caso_padre'] ? 'border-yellow-400 bg-yellow-50' : 'border-slate-200 hover:bg-slate-50'; ?> rounded p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-lg">
                                        <?= htmlspecialchars($caso['titulo']) ?>
                                        <?php if($caso['id_caso_padre']): ?>
                                            <span class="text-xs text-yellow-600 ml-2">(Ticket de Seguimiento)</span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <?php 
                                        $colorEstado = 'bg-slate-200 text-slate-800';
                                        if ($caso['estado'] == 'abierto') $colorEstado = 'bg-yellow-100 text-yellow-800';
                                        if ($caso['estado'] == 'en_tratamiento') $colorEstado = 'bg-blue-100 text-blue-800';
                                        if ($caso['estado'] == 'resuelto') $colorEstado = 'bg-green-100 text-green-800';
                                    ?>
                                    <span class="<?= $colorEstado ?> text-xs font-bold px-2 py-1 rounded uppercase">
                                        <?= str_replace('_', ' ', $caso['estado']) ?>
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 mb-2">
                                    <strong>Profesional Asignado:</strong> 
                                    <?php if ($caso['nombre_doctor']): ?>
                                        <span class="text-blue-600"><?= htmlspecialchars($caso['nombre_doctor']) ?></span>
                                    <?php else: ?>
                                        <span class="text-yellow-600 italic">En lista de espera...</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm text-slate-500 mb-4 line-clamp-2"><?= htmlspecialchars($caso['descripcion']) ?></p>
                                <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-200">
                                    <span class="text-xs text-slate-400">Reportado: <?= date('d/m/Y H:i', strtotime($caso['fecha_creacion'])) ?></span>
                                    
                                    <a href="detalle_paciente.php?id=<?= $caso['id'] ?>" class="text-teal-600 hover:text-teal-800 text-sm font-bold transition flex items-center">
                                        Ver Detalles / Seguimiento →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-slate-50 p-6 rounded text-center text-slate-500">
                        Aún no tienes ningún caso reportado. Usa el formulario de la izquierda para abrir uno.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>