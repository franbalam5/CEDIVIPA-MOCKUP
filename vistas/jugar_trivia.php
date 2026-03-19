<?php
// vistas/jugar_trivia.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if (!isset($_GET['id_trivia'])) { // Cambiaremos id_trivia por id del caso en el panel, pero para compatibilidad lo dejamos igual por ahora
    header("Location: panel_estudiante.php");
    exit;
}

// Ahora pasamos el ID del caso directamente
$id_caso = $_GET['id_trivia']; 

// 1. Obtenemos el caso actual y su diagnóstico correcto
$sql_caso = "SELECT titulo, descripcion, diagnostico_final FROM casos WHERE id = :id_caso AND apto_para_trivia = 1";
$stmt_caso = $pdo->prepare($sql_caso);
$stmt_caso->execute(['id_caso' => $id_caso]);
$caso = $stmt_caso->fetch(PDO::FETCH_ASSOC);

if (!$caso || empty($caso['diagnostico_final'])) die("Trivia no disponible o sin diagnóstico.");

// 2. MAGIA: Obtenemos 3 diagnósticos aleatorios de OTROS casos resueltos
$sql_falsas = "SELECT DISTINCT diagnostico_final FROM casos 
               WHERE estado = 'resuelto' 
               AND id != :id_caso 
               AND diagnostico_final IS NOT NULL 
               ORDER BY RAND() LIMIT 3";
$stmt_falsas = $pdo->prepare($sql_falsas);
$stmt_falsas->execute(['id_caso' => $id_caso]);
$diagnosticos_falsos = $stmt_falsas->fetchAll(PDO::FETCH_ASSOC);

// 3. Armamos el arreglo de opciones
$opciones = [];

// Primero metemos la correcta
$opciones[] = ['texto' => $caso['diagnostico_final'], 'es_correcta' => true];

// Luego metemos las falsas que encontramos en la BD
foreach ($diagnosticos_falsos as $falsa) {
    $opciones[] = ['texto' => $falsa['diagnostico_final'], 'es_correcta' => false];
}

// 4. SALVAVIDAS: Si la BD está muy vacía y no juntó 4 opciones, rellenamos con genéricos
$comodines = [
    "Gingivitis aguda por mala técnica de cepillado",
    "Pulpitis irreversible asintomática",
    "Absceso periapical agudo",
    "Traumatismo dental con fractura de esmalte",
    "Sensibilidad dentinaria por retracción gingival",
    "Periodontitis crónica avanzada"
];

while (count($opciones) < 4) {
    $rand_index = array_rand($comodines);
    $texto_comodin = $comodines[$rand_index];
    
    // Verificamos que no estemos duplicando una respuesta
    $existe = false;
    foreach ($opciones as $op) {
        if ($op['texto'] == $texto_comodin) $existe = true;
    }
    
    if (!$existe) {
        $opciones[] = ['texto' => $texto_comodin, 'es_correcta' => false];
    }
}

// 5. Mezclamos las opciones para que la correcta no siempre sea la primera
shuffle($opciones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trivia Médica Dinámica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans text-slate-800 pb-12 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-3xl border-t-4 border-yellow-500">
        
        <div class="mb-6 flex justify-between items-center border-b pb-4">
            <h2 class="text-2xl font-bold text-slate-800">Evaluación Clínica</h2>
            <a href="panel_estudiante.php" class="text-sm text-indigo-600 hover:underline font-bold">← Salir</a>
        </div>

        <div class="bg-indigo-50 p-6 rounded-lg mb-8 border border-indigo-100 shadow-inner">
            <h3 class="text-lg font-bold text-indigo-900 mb-2">Expediente Clínico: <?= htmlspecialchars($caso['titulo']) ?></h3>
            <p class="text-slate-700 whitespace-pre-wrap"><?= htmlspecialchars($caso['descripcion']) ?></p>
        </div>

        <h3 class="font-bold text-slate-700 mb-4 text-center text-lg">Basado en el expediente de este paciente, ¿cuál fue el diagnóstico final establecido por el doctor?</h3>

        <div class="grid grid-cols-1 gap-4" id="contenedor-opciones">
            <?php foreach ($opciones as $index => $opcion): ?>
                <button onclick="verificarRespuesta(<?= $opcion['es_correcta'] ? 'true' : 'false' ?>, this)" 
                        class="opcion-btn w-full text-left bg-white border-2 border-slate-200 hover:border-indigo-500 p-4 rounded-lg shadow-sm transition duration-200 text-slate-700 font-medium">
                    <?= htmlspecialchars($opcion['texto']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div id="resultado" class="mt-6 hidden text-center p-4 rounded-lg font-bold text-lg"></div>

    </div>

    <script>
        function verificarRespuesta(esCorrecta, botonClickeado) {
            const botones = document.querySelectorAll('.opcion-btn');
            const contenedorResultado = document.getElementById('resultado');

            botones.forEach(btn => {
                btn.disabled = true;
                btn.classList.remove('hover:border-indigo-500');
            });

            if (esCorrecta) {
                botonClickeado.classList.add('bg-green-100', 'border-green-500', 'text-green-800');
                contenedorResultado.classList.remove('hidden', 'bg-red-100', 'text-red-800');
                contenedorResultado.classList.add('bg-green-100', 'text-green-800');
                contenedorResultado.innerHTML = "¡Correcto! Excelente análisis clínico. 🎉";
            } else {
                botonClickeado.classList.add('bg-red-100', 'border-red-500', 'text-red-800');
                contenedorResultado.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                contenedorResultado.classList.add('bg-red-100', 'text-red-800');
                contenedorResultado.innerHTML = "Incorrecto. Repasa la teoría en tus apuntes y vuelve a intentarlo.";
                
                botones.forEach(btn => {
                    if (btn.getAttribute('onclick').includes('true')) {
                        btn.classList.add('border-green-500', 'border-2');
                    }
                });
            }
        }
    </script>
</body>
</html>