<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Odontológico - CEDIVIPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .opcion {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid #e2e8f0;
        }
        .opcion:hover:not(:disabled) {
            border-color: #2563eb;
            background-color: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }
        .opcion.seleccionada-correcta {
            background-color: #22c55e !important;
            border-color: #166534 !important;
            color: white !important;
            -webkit-text-fill-color: white !important;
        }
        .opcion.seleccionada-incorrecta {
            background-color: #ef4444 !important;
            border-color: #991b1b !important;
            color: white !important;
            -webkit-text-fill-color: white !important;
        }
        .opcion.correcta-revelada {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
            box-shadow: 0 0 0 2px #22c55e;
        }
        .opcion:disabled {
            opacity: 1 !important;
            cursor: default;
        }
        .opcion.seleccionada-correcta:disabled,
        .opcion.seleccionada-incorrecta:disabled {
            color: white !important;
            -webkit-text-fill-color: white !important;
        }

        .opcion.seleccionada-correcta.correcta-revelada {
            background-color: #22c55e !important;
            color: white !important;
            -webkit-text-fill-color: white !important;
        }
        #definicion-texto {
            min-height: 4rem;
        }
        .barra-tiempo {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .barra-tiempo-progreso {
            height: 100%;
            background: linear-gradient(to right, #22c55e, #facc15, #ef4444);
            transition: width 0.3s linear;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
    <header class="bg-[#1e3a8a] text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl sm:text-2xl font-bold">Quiz Clínico</h1>
        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded text-sm">Cerrar Sesión</button>
    </header>

    <main class="flex-grow container mx-auto px-4 sm:px-8 py-8 max-w-7xl flex flex-col items-center">
        
        <!-- Selector de modo -->
        <div class="w-full mb-10 flex flex-col sm:flex-row gap-4 justify-center">
            <button id="btn-modo-infinito" class="px-8 py-4 rounded-2xl font-bold text-xl bg-blue-600 text-white shadow-md hover:bg-blue-700 transition-colors flex-1 max-w-sm">
                ♾️ Modo Infinito
            </button>
            <button id="btn-modo-supervivencia" class="px-8 py-4 rounded-2xl font-bold text-xl bg-purple-600 text-white shadow-md hover:bg-purple-700 transition-colors flex-1 max-w-sm">
                ⚡ Supervivencia
            </button>
        </div>

        <!-- Panel de puntuación / vidas / temporizador -->
        <div id="panel-puntuacion" class="w-full flex flex-wrap justify-between items-center mb-8 p-6 bg-white rounded-2xl shadow-sm border gap-4">
            <div class="flex flex-wrap gap-6 text-lg font-semibold">
                <div id="contador-aciertos" class="text-green-700">
                    ✅ Aciertos: <span id="num-aciertos">0</span>
                </div>
                <div id="contador-fallos" class="text-red-700">
                    ❌ Fallos: <span id="num-fallos">0</span>
                </div>
                <div id="contador-vidas" class="hidden text-lg font-semibold">
                    Vidas: <span id="vidas-restantes" class="font-bold"></span>
                </div>
            </div>
            <div id="contador-racha" class="text-xl font-semibold text-orange-600">
                🔥 Racha: <span id="num-racha">0</span>
            </div>
            <!-- Temporizador (solo visible en supervivencia) -->
            <div id="temporizador-container" class="hidden w-full mt-2">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Tiempo restante</span>
                    <span id="tiempo-restante-texto">15s</span>
                </div>
                <div class="barra-tiempo">
                    <div id="barra-progreso" class="barra-tiempo-progreso" style="width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Área de la definición -->
        <div class="w-full bg-white p-8 sm:p-10 rounded-2xl shadow-md border mb-10 text-center">
            <p class="text-gray-400 uppercase tracking-wider text-sm mb-3">¿Qué término corresponde a esta definición?</p>
            <h2 id="definicion-texto" class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-gray-800 leading-relaxed px-2">
                <!-- Definición -->
            </h2>
        </div>

        <!-- Opciones -->
        <div id="opciones-container" class="w-full grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <!-- 4 botones generados dinámicamente -->
        </div>

        <!-- Botón siguiente -->
        <button id="btn-siguiente" class="hidden bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-12 rounded-full shadow-lg text-xl transition-colors">
            Siguiente ▶
        </button>

        <!-- Fin de supervivencia -->
        <div id="fin-supervivencia" class="hidden w-full text-center mt-8 p-8 bg-white rounded-2xl shadow-md border">
            <h3 class="text-3xl font-extrabold text-gray-800 mb-2">¡Juego terminado!</h3>
            <p id="mensaje-final" class="text-lg text-gray-600 mb-6"></p>
            <button id="btn-reintentar-supervivencia" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-10 rounded-full shadow-lg text-lg">
                Intentar de nuevo
            </button>
        </div>

    </main>

    <script src="../js/gestor_diccionario.js"></script>
    <script src="../js/quiz.js"></script>
</body>
</html>