<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crucigrama - CEDIVIPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .celda-crucigrama { text-transform: uppercase; text-align: center; font-weight: bold; }
        .celda-vacia { background-color: transparent; border: none; }
        .celda-activa { background-color: white; border: 1px solid #94a3b8; }
        .celda-activa:focus { background-color: #dbeafe; border-color: #2563eb; outline: none; }
        .celda-correcta { background-color: #22c55e !important; color: white !important; border-color: #166534 !important; }
        .celda-resaltada { background-color: #bfdbfe !important; }
        .celda-numero {
        pointer-events: none;  /* Para que los clics pasen al input */
        user-select: none;      /* Evita que se seleccione el número */
    }
        
        /* Estilo para pistas completadas */
        .pista-tachada {
            text-decoration: line-through;
            opacity: 0.5;
            color: #9ca3af !important;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="bg-[#1e3a8a] text-white p-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Crucigrama Clínico</h1>
        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded text-sm">Cerrar Sesión</button>
    </header>

    <main class="container mx-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7 flex justify-center">
            <div id="tablero-crucigrama" class="grid bg-slate-800 p-2 border-4 border-slate-800 gap-[2px]"></div>
        </div>
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <h3 class="font-bold text-blue-600 mb-2">Horizontales</h3>
                <ul id="pistas-horizontales" class="text-sm"></ul>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <h3 class="font-bold text-purple-600 mb-2">Verticales</h3>
                <ul id="pistas-verticales" class="text-sm"></ul>
            </div>
            <button id="btn-reiniciar" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold">Generar nuevo</button>
        </div>
    </main>

    <!-- Modal de victoria (oculto por defecto) -->
    <div id="modal-victoria" class="fixed inset-0 bg-slate-900 bg-opacity-80 backdrop-blur-sm flex items-center justify-center hidden z-50 opacity-0 transition-opacity duration-300">
        <div id="modal-contenido" class="bg-white rounded-3xl p-8 sm:p-10 max-w-md text-center shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="text-6xl mb-4 animate-bounce">🏆</div>
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">¡Crucigrama Completado!</h2>
            <p class="text-gray-600 mb-8 text-lg">Has completado todas las palabras correctamente.</p>
            <button id="btn-modal-reiniciar" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all w-full text-lg">
                ¿Volver a jugar?
            </button>
        </div>
    </div>

    <script src="../js/gestor_diccionario.js"></script>
    <script src="../js/crucigrama.js"></script>
</body>
</html>