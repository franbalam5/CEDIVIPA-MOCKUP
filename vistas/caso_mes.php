<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caso del Mes - CEDIVIPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <header class="bg-[#1e3a8a] text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl sm:text-2xl font-bold">Diagnóstico Clínico</h1>
        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded text-sm">Cerrar Sesión</button>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="mb-8 text-center sm:text-left flex flex-col sm:flex-row justify-between items-end border-b pb-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Caso del Mes</h2>
                <p id="mes-etiqueta" class="text-blue-600 font-bold text-lg mt-1">Cargando...</p>
            </div>
            <a href="historico_casos.php" class="text-sm text-gray-500 hover:text-blue-600 mt-4 sm:mt-0 underline">
                Ver casos anteriores
            </a>
        </div>

        <div id="contenedor-caso" class="grid grid-cols-1 lg:grid-cols-12 gap-8 hidden">
            
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white p-4 rounded-2xl shadow-sm border">
                    <div class="w-full h-64 sm:h-80 bg-slate-200 rounded-xl flex items-center justify-center overflow-hidden relative">
                        <span class="text-slate-400 text-xl font-bold absolute">📷 Área de Imagen Clínica/Radiográfica</span>
                        <img id="imagen-caso" src="" alt="Radiografía del caso" class="w-full h-full object-cover hidden">
                    </div>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border">
                    <h3 id="titulo-caso" class="text-2xl font-bold text-gray-800 mb-4"></h3>
                    <div class="flex items-center gap-2 mb-4 text-sm font-semibold text-gray-500 bg-gray-100 w-fit px-3 py-1 rounded-full">
                        <span>👤</span> <span id="paciente-caso"></span>
                    </div>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        <h4 class="font-bold text-gray-900">Historia Clínica y Exploración:</h4>
                        <p id="historia-caso" class="mt-2 text-lg"></p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex flex-col">
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border sticky top-8">
                    <h4 class="text-xl font-bold text-blue-800 mb-4">🩺 ¿Cuál es tu diagnóstico presuntivo?</h4>
                    <p class="text-sm text-gray-500 mb-6">Analiza la historia clínica y selecciona la opción correcta.</p>
                    
                    <div id="opciones-container" class="space-y-3">
                        </div>

                    <div id="feedback-caja" class="mt-8 hidden p-5 rounded-xl border-2">
                        <div class="flex items-center gap-3 mb-2">
                            <span id="feedback-icono" class="text-2xl"></span>
                            <h5 id="feedback-titulo" class="font-bold text-lg"></h5>
                        </div>
                        <p id="feedback-texto" class="text-gray-700 text-sm leading-relaxed"></p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <script src="../js/caso_mes.js"></script>
</body>
</html>