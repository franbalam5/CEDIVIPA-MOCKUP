<?php
// vistas/panel_doctor.php
session_start();

// 1. EL CADENERO: Validar seguridad
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';
$id_doctor = $_SESSION['usuario_id'];

// 2. Consultar MIS CASOS asignados
// Usamos un JOIN para traer también el nombre del paciente desde la tabla usuarios
$sql_mis_casos = "SELECT c.*, u.nombre AS nombre_paciente 
                  FROM casos c 
                  JOIN usuarios u ON c.id_paciente = u.id 
                  WHERE c.id_doctor_asignado = :id_doctor 
                  ORDER BY c.fecha_creacion DESC";
$stmt_mis = $pdo->prepare($sql_mis_casos);
$stmt_mis->execute(['id_doctor' => $id_doctor]);
$mis_casos = $stmt_mis->fetchAll(PDO::FETCH_ASSOC);

// 3. Consultar CASOS ABIERTOS (Pool general)
$sql_abiertos = "SELECT c.*, u.nombre AS nombre_paciente 
                 FROM casos c 
                 JOIN usuarios u ON c.id_paciente = u.id 
                 WHERE c.id_doctor_asignado IS NULL 
                 ORDER BY c.fecha_creacion ASC";
$stmt_abiertos = $pdo->query($sql_abiertos);
$casos_abiertos = $stmt_abiertos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <nav class="bg-blue-800 text-white p-4 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Portal Odontológico - Área Médica</h1>
            <div class="flex items-center space-x-4">
                <span>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm font-bold transition">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-8 p-4 grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="bg-white p-6 rounded-lg shadow border-t-4 border-yellow-400">
            <h2 class="text-2xl font-bold mb-4 text-slate-700">📋 Casos Disponibles</h2>
            <p class="text-sm text-slate-500 mb-4">Pacientes esperando a ser asignados a un especialista.</p>
            
            <?php if (count($casos_abiertos) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($casos_abiertos as $caso): ?>
                        <div class="border border-slate-200 rounded p-4 hover:bg-slate-50">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($caso['titulo']) ?></h3>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">SIN ASIGNAR</span>
                            </div>
                            <p class="text-sm text-slate-600 mb-2"><strong>Paciente:</strong> <?= htmlspecialchars($caso['nombre_paciente']) ?></p>
                            <p class="text-sm text-slate-500 line-clamp-2 mb-4"><?= htmlspecialchars($caso['descripcion']) ?></p>
                            
                            <a href="../acciones/tomar_caso.php?id=<?= $caso['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm font-bold transition">
                                Atender este caso
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 p-6 rounded text-center text-slate-500">
                    No hay casos nuevos en espera.
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-t-4 border-blue-600">
            <h2 class="text-2xl font-bold mb-4 text-slate-700">👨‍⚕️ Mis Pacientes Asignados</h2>
            <p class="text-sm text-slate-500 mb-4">Casos que están bajo tu supervisión y tratamiento.</p>
            
            <?php if (count($mis_casos) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($mis_casos as $caso): ?>
                        <div class="border border-slate-200 rounded p-4 hover:bg-slate-50 border-l-4 border-l-blue-500">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($caso['titulo']) ?></h3>
                                <?php 
                                    $colorEstado = $caso['estado'] == 'resuelto' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
                                ?>
                                <span class="<?= $colorEstado ?> text-xs font-bold px-2 py-1 rounded uppercase">
                                    <?= str_replace('_', ' ', $caso['estado']) ?>
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 mb-2"><strong>Paciente:</strong> <?= htmlspecialchars($caso['nombre_paciente']) ?></p>
                            <p class="text-sm text-slate-400 mb-4">Iniciado: <?= date('d/m/Y', strtotime($caso['fecha_creacion'])) ?></p>
                            
                            <a href="expediente.php?id=<?= $caso['id'] ?>" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded text-sm font-bold transition">
                                Abrir Expediente →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 p-6 rounded text-center text-slate-500">
                    Actualmente no tienes casos en tratamiento.
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>