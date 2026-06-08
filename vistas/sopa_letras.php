<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sopa de Letras - CEDIVIPA</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .celda-encontrada {
            background-color: #22c55e !important; /* green-500 */
            color: white !important;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        }

        /* Va después para que el azul resalte sobre el verde al arrastrar en intersecciones */
        .celda-seleccionada {
            background-color: #3b82f6 !important; /* blue-500 */
            color: white !important;
            transform: scale(0.90);
        }

        .palabra-tachada {
            text-decoration: line-through;
            opacity: 0.5;
            background-color: #f3f4f6 !important; /* gray-100 */
            color: #9ca3af !important; /* gray-400 */
            border-color: transparent !important;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans flex flex-col">

    <header class="bg-[#1e3a8a] text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Portal Odontológico - Juegos Didácticos</h1>
        <div class="flex items-center gap-4">
            <span>Hola, <strong>Estudiante</strong></span>
            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded text-sm font-semibold transition-colors">
                Cerrar Sesión
            </button>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-4xl flex-grow flex flex-col items-center">
        
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Sopa de Letras</h2>
            <p class="text-gray-500 mt-2 text-lg">Encuentra los términos clínicos ocultos en el tablero.</p>
        </div>

        <div class="w-full max-w-2xl bg-white p-3 sm:p-5 rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div id="tablero-sopa" class="grid gap-1 sm:gap-1.5 w-full aspect-square">
                </div>
        </div>

        <div class="w-full max-w-2xl text-center mb-8">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Palabras a encontrar</h3>
            <ul id="lista-palabras" class="flex flex-wrap justify-center gap-2 sm:gap-3">
                </ul>
        </div>

        <button id="btn-reiniciar" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition-colors shadow-md flex items-center gap-2">
            <span class="text-xl">↻</span> Generar nuevo tablero
        </button>

    </main>

    <div id="toast-definicion" class="fixed top-8 left-1/2 transform -translate-x-1/2 -translate-y-32 opacity-0 bg-white border-t-8 border-blue-500 shadow-2xl rounded-2xl p-6 z-50 w-11/12 max-w-2xl pointer-events-none transition-all duration-500 flex flex-col sm:flex-row items-center sm:items-start gap-4">
        <div class="text-5xl">💡</div>
        <div class="text-center sm:text-left">
            <h4 id="toast-palabra" class="text-2xl font-extrabold text-blue-800 uppercase tracking-widest mb-2"></h4>
            <p id="toast-pista" class="text-gray-700 text-lg leading-relaxed font-medium"></p>
        </div>
    </div>

    <div id="modal-victoria" class="fixed inset-0 bg-slate-900 bg-opacity-80 backdrop-blur-sm flex items-center justify-center hidden z-50 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 sm:p-10 max-w-md text-center shadow-2xl transform scale-95 transition-transform duration-300" id="modal-content">
            <div class="text-6xl mb-4 animate-bounce">🏆</div>
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">¡Misión Cumplida!</h2>
            <p class="text-gray-600 mb-8 text-lg">Has encontrado y repasado todos los términos odontológicos.</p>
            <button id="btn-modal-reiniciar" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all w-full text-lg">
                ¿Volver a jugar?
            </button>
        </div>
    </div>

    <script src="../js/gestor_diccionario.js"></script>
    <script src="../js/sopa_letras.js"></script>
</body>
</html>