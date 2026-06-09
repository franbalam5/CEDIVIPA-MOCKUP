document.addEventListener("DOMContentLoaded", async () => {
    const diccionario = new GestorDiccionario();
    await diccionario.inicializar();

    // Elementos UI
    const definicionTexto = document.getElementById("definicion-texto");
    const opcionesContainer = document.getElementById("opciones-container");
    const btnSiguiente = document.getElementById("btn-siguiente");
    const numAciertos = document.getElementById("num-aciertos");
    const numFallos = document.getElementById("num-fallos");
    const numRacha = document.getElementById("num-racha");
    const panelVidas = document.getElementById("contador-vidas");
    const vidasRestantesEl = document.getElementById("vidas-restantes");
    const finSupervivencia = document.getElementById("fin-supervivencia");
    const mensajeFinal = document.getElementById("mensaje-final");
    const btnReintentarSupervivencia = document.getElementById("btn-reintentar-supervivencia");

    const btnModoInfinito = document.getElementById("btn-modo-infinito");
    const btnModoSupervivencia = document.getElementById("btn-modo-supervivencia");

    // Temporizador
    const temporizadorContainer = document.getElementById("temporizador-container");
    const barraProgreso = document.getElementById("barra-progreso");
    const tiempoRestanteTexto = document.getElementById("tiempo-restante-texto");

    // Estado del juego
    let modoActual = 'infinito'; // 'infinito' | 'supervivencia'
    let preguntaActual = null;
    let respondiendo = false;
    let aciertos = 0;
    let fallos = 0;
    let racha = 0;
    let vidas = 3;
    let juegoActivo = false;

    // Temporizador
    const TIEMPO_MAX = 15; // segundos por pregunta en supervivencia
    let tiempoRestante = TIEMPO_MAX;
    let intervaloID = null;
    let tiempoUltimaPregunta = 0;

    // Inicializar interfaz
    function resetUI() {
        aciertos = 0;
        fallos = 0;
        racha = 0;
        vidas = 3;
        actualizarMarcadores();
        btnSiguiente.classList.add('hidden');
        finSupervivencia.classList.add('hidden');
        opcionesContainer.innerHTML = '';
        definicionTexto.textContent = '';
        panelVidas.classList.add('hidden');
        detenerTemporizador();
        temporizadorContainer.classList.add('hidden');
    }

    function actualizarMarcadores() {
        numAciertos.textContent = aciertos;
        numFallos.textContent = fallos;
        numRacha.textContent = racha;

        if (modoActual === 'supervivencia') {
            panelVidas.classList.remove('hidden');
            temporizadorContainer.classList.remove('hidden');
            // Mostrar número con color
            vidasRestantesEl.textContent = vidas;
            // Colores según vidas
            vidasRestantesEl.className = 'font-bold'; // reset
            if (vidas === 3) {
                vidasRestantesEl.classList.add('text-green-600');
            } else if (vidas === 2) {
                vidasRestantesEl.classList.add('text-yellow-600');
            } else if (vidas === 1) {
                vidasRestantesEl.classList.add('text-red-600');
            } else {
                vidasRestantesEl.classList.add('text-gray-600'); // 0
            }
        } else {
            panelVidas.classList.add('hidden');
            temporizadorContainer.classList.add('hidden');
        }
    }

    // --- Temporizador ---
    function iniciarTemporizador() {
        if (modoActual !== 'supervivencia') return;
        detenerTemporizador();
        tiempoRestante = TIEMPO_MAX;
        barraProgreso.style.width = '100%';
        tiempoRestanteTexto.textContent = `${tiempoRestante}s`;
        tiempoUltimaPregunta = Date.now();
        intervaloID = setInterval(() => {
            const ahora = Date.now();
            const transcurrido = Math.floor((ahora - tiempoUltimaPregunta) / 1000);
            tiempoRestante = Math.max(0, TIEMPO_MAX - transcurrido);
            const porcentaje = (tiempoRestante / TIEMPO_MAX) * 100;
            barraProgreso.style.width = `${porcentaje}%`;
            tiempoRestanteTexto.textContent = `${tiempoRestante}s`;
            if (tiempoRestante <= 0) {
                detenerTemporizador();
                tiempoAgotado();
            }
        }, 200);
    }

    function detenerTemporizador() {
        if (intervaloID) {
            clearInterval(intervaloID);
            intervaloID = null;
        }
    }

    function tiempoAgotado() {
        if (respondiendo || !juegoActivo) return;
        respondiendo = true;
        // Revelar respuesta correcta
        const correcta = preguntaActual.termino.palabra;
        const botones = opcionesContainer.querySelectorAll('button');
        botones.forEach(btn => {
            btn.disabled = true;
            if (btn.textContent.toUpperCase() === correcta.toUpperCase()) {
                btn.classList.add('correcta-revelada');
            }
        });
        // Contar como fallo
        fallos++;
        racha = 0;
        vidas--;
        actualizarMarcadores();

        if (vidas <= 0) {
            finalizarSupervivencia();
        } else {
            btnSiguiente.classList.remove('hidden');
            btnSiguiente.focus();
        }
    }

    // Elegir modo
    btnModoInfinito.addEventListener('click', () => {
        modoActual = 'infinito';
        resetUI();
        juegoActivo = true;
        btnModoInfinito.classList.add('bg-blue-700', 'ring-2', 'ring-blue-300');
        btnModoSupervivencia.classList.remove('bg-purple-700', 'ring-2', 'ring-purple-300');
        cargarNuevaPregunta();
    });

    btnModoSupervivencia.addEventListener('click', () => {
        modoActual = 'supervivencia';
        resetUI();
        juegoActivo = true;
        vidas = 3;
        btnModoSupervivencia.classList.add('bg-purple-700', 'ring-2', 'ring-purple-300');
        btnModoInfinito.classList.remove('bg-blue-700', 'ring-2', 'ring-blue-300');
        actualizarMarcadores();
        cargarNuevaPregunta();
    });

    // Cargar pregunta
    function cargarNuevaPregunta() {
        if (!juegoActivo) return;
        detenerTemporizador();
        respondiendo = false;
        btnSiguiente.classList.add('hidden');
        opcionesContainer.innerHTML = '';

        const todos = diccionario.terminos;
        if (todos.length < 4) {
            definicionTexto.textContent = "Se necesitan al menos 4 términos en el diccionario.";
            return;
        }

        const idxCorrecto = Math.floor(Math.random() * todos.length);
        const terminoCorrecto = todos[idxCorrecto];

        const distractores = [];
        const indicesUsados = new Set([idxCorrecto]);
        while (distractores.length < 3) {
            const idx = Math.floor(Math.random() * todos.length);
            if (!indicesUsados.has(idx)) {
                distractores.push(todos[idx].palabra);
                indicesUsados.add(idx);
            }
        }

        const opciones = [terminoCorrecto.palabra, ...distractores];
        for (let i = opciones.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [opciones[i], opciones[j]] = [opciones[j], opciones[i]];
        }

        preguntaActual = {
            termino: terminoCorrecto,
            opciones: opciones,
        };

        definicionTexto.textContent = terminoCorrecto.pista;

        opciones.forEach((opcion) => {
            const btn = document.createElement('button');
            btn.className = 'opcion p-4 sm:p-5 rounded-xl text-lg font-semibold text-gray-700 bg-white';
            btn.textContent = opcion;
            btn.addEventListener('click', () => manejarRespuesta(opcion, btn));
            opcionesContainer.appendChild(btn);
        });

        // Iniciar temporizador solo en supervivencia
        if (modoActual === 'supervivencia') {
            iniciarTemporizador();
        }
    }

    function manejarRespuesta(opcionElegida, btnClickeado) {
        if (respondiendo || !juegoActivo) return;
        respondiendo = true;
        detenerTemporizador();
        const correcta = preguntaActual.termino.palabra;
        const esCorrecta = (opcionElegida.toUpperCase() === correcta.toUpperCase());

        const botones = opcionesContainer.querySelectorAll('button');
        botones.forEach(btn => {
            btn.disabled = true;
            if (btn.textContent.toUpperCase() === correcta.toUpperCase()) {
                btn.classList.add('correcta-revelada');
            }
        });

        if (esCorrecta) {
            btnClickeado.classList.add('seleccionada-correcta');
            aciertos++;
            racha++;
        } else {
            btnClickeado.classList.add('seleccionada-incorrecta');
            fallos++;
            racha = 0;
            if (modoActual === 'supervivencia') {
                vidas--;
                actualizarMarcadores();
                if (vidas <= 0) {
                    finalizarSupervivencia();
                    return;
                }
            }
        }

        actualizarMarcadores();
        btnSiguiente.classList.remove('hidden');
        btnSiguiente.focus();
    }

    btnSiguiente.addEventListener('click', () => {
        if (!juegoActivo) return;
        cargarNuevaPregunta();
    });

    function finalizarSupervivencia() {
        juegoActivo = false;
        respondiendo = true;
        detenerTemporizador();
        finSupervivencia.classList.remove('hidden');
        mensajeFinal.textContent = `Acertaste ${aciertos} preguntas antes de quedarte sin vidas. ¡Sigue repasando!`;
        btnSiguiente.classList.add('hidden');
    }

    btnReintentarSupervivencia.addEventListener('click', () => {
        resetUI();
        modoActual = 'supervivencia';
        juegoActivo = true;
        vidas = 3;
        actualizarMarcadores();
        btnModoSupervivencia.classList.add('bg-purple-700', 'ring-2', 'ring-purple-300');
        btnModoInfinito.classList.remove('bg-blue-700', 'ring-2', 'ring-blue-300');
        cargarNuevaPregunta();
    });

    // Iniciar por defecto en modo infinito
    btnModoInfinito.click();
});