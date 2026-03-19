<?php
// vistas/resolver_caso.php
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

$sql = "SELECT titulo FROM casos WHERE id = :id_caso AND id_doctor_asignado = :id_doctor AND estado != 'resuelto'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_caso' => $id_caso, 'id_doctor' => $id_doctor]);
$caso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caso) die("Caso no disponible o ya resuelto.");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cerrar Caso</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 pb-12 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl border-t-4 border-green-500">
        <h2 class="text-2xl font-bold mb-2 text-slate-800">Finalizar Tratamiento</h2>
        <p class="text-slate-500 mb-6">Estás cerrando el caso: <strong><?= htmlspecialchars($caso['titulo']) ?></strong></p>

        <form method="POST" action="../acciones/procesar_resolucion.php" class="space-y-6">
            <input type="hidden" name="id_caso" value="<?= $id_caso ?>">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Diagnóstico Final (Respuesta Correcta):</label>
                <p class="text-xs text-slate-500 mb-2">Este diagnóstico quedará en el expediente y será la respuesta correcta en la trivia.</p>
                <textarea name="diagnostico_final" rows="3" required placeholder="Ej. Caries de segundo grado tratada con resina..."
                          class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            <div class="flex items-center bg-indigo-50 p-4 rounded border border-indigo-200">
                <input type="checkbox" id="enviar_trivia" name="enviar_trivia" value="1" class="w-5 h-5 text-indigo-600 rounded">
                <label for="enviar_trivia" class="ml-3 text-sm font-bold text-indigo-800 cursor-pointer">
                    🎮 Enviar este caso a la Trivia de Estudiantes
                </label>
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="expediente.php?id=<?= $id_caso ?>" class="text-slate-500 hover:text-slate-700 text-sm font-bold">← Cancelar</a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow transition">
                    Cerrar Expediente Definitivamente
                </button>
            </div>
        </form>
    </div>
</body>
</html>