<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crucigrama Clínico - CEDIVIPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Diseño libre, flotante y moderno */
        .celda-crucigrama { 
            text-transform: uppercase; 
            text-align: center; 
            font-weight: 800; 
            font-size: 1.25rem;
            color: #334155; /* slate-700 */
            border-radius: 8px; /* Bordes redondeados modernos */
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @media (min-width: 640px) {
            .celda-crucigrama { font-size: 1.75rem; }
        }

        .celda-vacia { 
            visibility: hidden; /* Oculta totalmente las celdas sin letras */
            pointer-events: none;
        }

        .celda-activa { 
            background-color: #ffffff;
            border: 2px solid #cbd5e1; /* slate-300 */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Estado: Foco (El usuario está escribiendo aquí) */
        .celda-activa:focus { 
            background-color: #ffffff;
            border-color: #3b82f6 !important; /* blue-500 */
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3) !important;
            transform: translateY(-2px) scale(1.05); /* Efecto 3D de saltito */
            z-index: 20;
            outline: none; 
        }

        /* Estado: Resaltado (Palabra seleccionada) */
        .celda-resaltada { 
            background-color: #eff6ff !important; /* blue-50 */
            border-color: #93c5fd !important; /* blue-300 */
            color: #1e40af !important; /* blue-800 */
        }

        /* Estado: Correcta (Verde) */
        .celda-correcta { 
            background-color: #22c55e !important; 
            color: #ffffff !important; 
            border-color: #166534 !important; 
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3) !important;
        }

        /* Si pasas sobre una correcta */
        .celda-correcta.celda-resaltada {
            background-color: #4ade80 !important; 
            border-color: #14532d !important; 
        }

        /* Número flotante */
        .celda-numero {
            pointer-events: none;  
            user-select: none;     
            color: #475569; /* slate-600 */
            background: rgba(255, 255, 255, 0.9);
            border-radius: 4px;
            padding: 1px 4px;
        }
        
        .pista-tachada {
            text-decoration: line-through;
            opacity: 0.5;
            color: #9ca3af !important;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <header class="bg-[#1e3a8a] text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl sm:text-2xl font-bold">Portal Odontológico - Juegos Didácticos</h1>
        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded text-sm font-semibold transition-colors">Cerrar Sesión</button>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-7xl flex-grow flex flex-col">
        
        <div class="flex flex-col md:flex-row justify-between items-center md:items-end border-b border-slate-200 pb-4 mb-8">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Crucigrama Clínico</h2>
                <p class="text-gray-500 mt-2 text-lg">Resuelve los términos usando el teclado.</p>
            </div>
            <button id="btn-reiniciar" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-md flex items-center gap-2 transition-all w-full md:w-auto justify-center">
                <span class="text-xl">↻</span> Generar nuevo tablero
            </button>
        </div>

        <div class="flex flex-col xl:flex-row gap-8 lg:gap-12 items-start justify-center w-full">
            
            <div class="w-full xl:w-2/3 flex justify-center overflow-x-auto pb-4">
                <div id="tablero-crucigrama" class="grid gap-1 sm:gap-2 w-full max-w-4xl min-w-[300px] p-4">
                    </div>
            </div>

            <div class="w-full xl:w-1/3 flex flex-col sm:flex-row xl:flex-col gap-6">
                <div class="flex-1 bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-lg text-blue-600 mb-4 flex items-center gap-2">➡️ Horizontales</h3>
                    <ul id="pistas-horizontales" class="text-sm text-gray-700 space-y-3"></ul>
                </div>
                <div class="flex-1 bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-lg text-purple-600 mb-4 flex items-center gap-2">⬇️ Verticales</h3>
                    <ul id="pistas-verticales" class="text-sm text-gray-700 space-y-3"></ul>
                </div>
            </div>
        </div>

    </main>

    <div id="modal-victoria" class="fixed inset-0 bg-slate-900 bg-opacity-80 backdrop-blur-sm flex items-center justify-center hidden z-50 opacity-0 transition-opacity duration-300">
        <div id="modal-contenido" class="bg-white rounded-3xl p-8 sm:p-12 max-w-md text-center shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="text-6xl mb-6 animate-bounce">🎓</div>
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">¡Completado!</h2>
            <p class="text-gray-600 mb-8 text-lg">Has descifrado todos los diagnósticos y términos.</p>
            <button id="btn-modal-reiniciar" class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-10 rounded-full shadow-lg hover:shadow-xl transition-all w-full text-lg">
                Jugar de nuevo
            </button>
        </div>
    </div>

    <script src="../js/gestor_diccionario.js"></script>
    <script src="../js/crucigrama.js"></script>
</body>
</html>