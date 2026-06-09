document.addEventListener("DOMContentLoaded", async () => {
    const contenedorCaso = document.getElementById('contenedor-caso');
    const mesEtiqueta = document.getElementById('mes-etiqueta');
    const tituloCaso = document.getElementById('titulo-caso');
    const pacienteCaso = document.getElementById('paciente-caso');
    const historiaCaso = document.getElementById('historia-caso');
    const opcionesContainer = document.getElementById('opciones-container');
    const feedbackCaja = document.getElementById('feedback-caja');
    const feedbackIcono = document.getElementById('feedback-icono');
    const feedbackTitulo = document.getElementById('feedback-titulo');
    const feedbackTexto = document.getElementById('feedback-texto');
    const imagenCaso = document.getElementById('imagen-caso');

    let casoActual = null;
    let respondido = false;

    // 1. Cargar datos
    try {
        const respuesta = await fetch('../config/casos.json');
        if (!respuesta.ok) throw new Error("No se pudo cargar los casos");
        const datos = await respuesta.json();
        
        // Buscar el caso marcado como activo
        casoActual = datos.casos.find(c => c.activo === true);
        
        if (casoActual) {
            renderizarCaso(casoActual);
        } else {
            mesEtiqueta.textContent = "No hay caso disponible este mes.";
        }
    } catch (error) {
        console.error(error);
        mesEtiqueta.textContent = "Error al cargar el sistema de casos.";
    }

    // 2. Pintar la interfaz
    function renderizarCaso(caso) {
        mesEtiqueta.textContent = `Reto de ${caso.mes}`;
        tituloCaso.textContent = caso.titulo;
        pacienteCaso.textContent = caso.paciente;
        historiaCaso.textContent = caso.historia_clinica;
        
        // Manejo de imagen opcional
        if (caso.imagen_url && caso.imagen_url !== "") {
            imagenCaso.src = caso.imagen_url;
            imagenCaso.classList.remove('hidden');
        }

        // Crear botones de opciones
        caso.opciones_diagnosticas.forEach(opcion => {
            const btn = document.createElement('button');
            btn.className = 'w-full text-left p-4 rounded-xl border-2 border-slate-200 font-semibold text-slate-700 hover:border-blue-500 hover:bg-blue-50 transition-all';
            btn.textContent = opcion;
            btn.onclick = () => evaluarRespuesta(opcion, btn);
            opcionesContainer.appendChild(btn);
        });

        contenedorCaso.classList.remove('hidden');
    }

    // 3. Evaluar diagnóstico
    function evaluarRespuesta(seleccion, btnSeleccionado) {
        if (respondido) return; // Bloquear si ya contestó
        respondido = true;

        const esCorrecta = seleccion === casoActual.respuesta_correcta;
        const botones = opcionesContainer.querySelectorAll('button');

        botones.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Resaltar siempre la correcta de verde
            if (btn.textContent === casoActual.respuesta_correcta) {
                btn.classList.remove('border-slate-200', 'opacity-50');
                btn.classList.add('bg-green-100', 'border-green-500', 'text-green-800');
            }
        });

        // Si se equivocó, marcar de rojo la que eligió
        if (!esCorrecta) {
            btnSeleccionado.classList.remove('border-slate-200', 'opacity-50');
            btnSeleccionado.classList.add('bg-red-100', 'border-red-500', 'text-red-800');
            
            feedbackCaja.classList.add('border-red-400', 'bg-red-50');
            feedbackIcono.textContent = '❌';
            feedbackTitulo.textContent = 'Diagnóstico incorrecto';
            feedbackTitulo.classList.add('text-red-800');
        } else {
            feedbackCaja.classList.add('border-green-400', 'bg-green-50');
            feedbackIcono.textContent = '✅';
            feedbackTitulo.textContent = '¡Diagnóstico acertado!';
            feedbackTitulo.classList.add('text-green-800');
        }

        feedbackTexto.textContent = casoActual.explicacion;
        feedbackCaja.classList.remove('hidden');
    }
});