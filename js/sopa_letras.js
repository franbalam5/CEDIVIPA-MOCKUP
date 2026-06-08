document.addEventListener("DOMContentLoaded", async () => {
    const diccionario = new GestorDiccionario();
    const cargaExitosa = await diccionario.inicializar();

    if (!cargaExitosa) {
        alert("Error al cargar el diccionario. Revisa la consola.");
        return;
    }

    // Nodos de la interfaz
    const tableroEl = document.getElementById('tablero-sopa');
    const listaEl = document.getElementById('lista-palabras');
    const btnReiniciar = document.getElementById('btn-reiniciar');
    const btnModalReiniciar = document.getElementById('btn-modal-reiniciar');
    
    const modalVictoria = document.getElementById('modal-victoria');
    const toastDefinicion = document.getElementById('toast-definicion');
    const toastPalabra = document.getElementById('toast-palabra');
    const toastPista = document.getElementById('toast-pista');
    
    // Parametrización de la cuadrícula
    const TAMANO_TABLERO = 12;
    const CANTIDAD_PALABRAS = 6;
    let cuadricula = [];
    let celdasDOM = []; 
    
    // Estado interno de la partida
    let palabrasObjetivo = []; 
    let palabrasEncontradas = 0;
    
    // Estado del arrastre (Drag)
    let isDragging = false;
    let celdaInicio = null;
    let celdasSeleccionadas = [];

    // Matriz de desplazamientos [Fila, Columna]
    const DIRECCIONES = [
        [0, 1],   // Derecha
        [1, 0],   // Abajo
        [1, 1],   // Diagonal Abajo-Derecha
        [-1, 1],  // Diagonal Arriba-Derecha
        [0, -1],  // Izquierda
        [-1, 0],  // Arriba
        [-1, -1], // Diagonal Arriba-Izquierda
        [1, -1]   // Diagonal Abajo-Izquierda
    ];

    function iniciarJuego() {
        tableroEl.innerHTML = '';
        listaEl.innerHTML = '';
        tableroEl.style.gridTemplateColumns = `repeat(${TAMANO_TABLERO}, 1fr)`;
        
        cuadricula = Array(TAMANO_TABLERO).fill(null).map(() => Array(TAMANO_TABLERO).fill(''));
        celdasDOM = Array(TAMANO_TABLERO).fill(null).map(() => Array(TAMANO_TABLERO).fill(null));
        palabrasObjetivo = [];
        palabrasEncontradas = 0;
        ocultarModal();

        // Extraer términos aptos del diccionario JSON
        const palabrasCandidatas = diccionario.obtenerPalabrasPorLongitud(TAMANO_TABLERO);
        const palabrasSeleccionadas = palabrasCandidatas.sort(() => 0.5 - Math.random()).slice(0, CANTIDAD_PALABRAS);

        palabrasSeleccionadas.forEach(p => {
            if (intentarColocarPalabra(p.palabra.toUpperCase())) {
                palabrasObjetivo.push(p); 
                
                const li = document.createElement('li');
                li.className = 'px-4 py-2 bg-white border border-gray-200 text-gray-700 font-semibold rounded-full shadow-sm text-sm sm:text-base transition-all duration-300';
                li.id = `palabra-${p.palabra.toUpperCase()}`;
                li.textContent = p.palabra.toUpperCase();
                listaEl.appendChild(li);
            }
        });

        rellenarVacios();
        dibujarTablero();
        configurarEventosRaton();
    }

    function intentarColocarPalabra(palabra) {
        const letras = palabra.split('');
        const opcionesValidas = [];

        for (let fila = 0; fila < TAMANO_TABLERO; fila++) {
            for (let col = 0; col < TAMANO_TABLERO; col++) {
                for (let dir of DIRECCIONES) {
                    if (puedeColocar(letras, fila, col, dir[0], dir[1])) {
                        opcionesValidas.push({ fila, col, dx: dir[0], dy: dir[1] });
                    }
                }
            }
        }

        if (opcionesValidas.length === 0) return false;

        const elegida = opcionesValidas[Math.floor(Math.random() * opcionesValidas.length)];
        for (let i = 0; i < letras.length; i++) {
            cuadricula[elegida.fila + (elegida.dx * i)][elegida.col + (elegida.dy * i)] = letras[i];
        }
        return true;
    }

    function puedeColocar(letras, filaInicio, colInicio, dx, dy) {
        let f = filaInicio, c = colInicio;
        for (let i = 0; i < letras.length; i++) {
            if (f < 0 || f >= TAMANO_TABLERO || c < 0 || c >= TAMANO_TABLERO) return false;
            if (cuadricula[f][c] !== '' && cuadricula[f][c] !== letras[i]) return false; 
            f += dx; c += dy;
        }
        return true;
    }

    function rellenarVacios() {
        const ALFABETO = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for (let f = 0; f < TAMANO_TABLERO; f++) {
            for (let c = 0; c < TAMANO_TABLERO; c++) {
                if (cuadricula[f][c] === '') {
                    cuadricula[f][c] = ALFABETO.charAt(Math.floor(Math.random() * ALFABETO.length));
                }
            }
        }
    }

    function dibujarTablero() {
        for (let f = 0; f < TAMANO_TABLERO; f++) {
            for (let c = 0; c < TAMANO_TABLERO; c++) {
                const celda = document.createElement('div');
                celda.className = 'bg-white text-gray-700 font-bold text-lg sm:text-xl flex items-center justify-center aspect-square cursor-pointer select-none rounded hover:bg-blue-50 transition-colors shadow-sm';
                celda.textContent = cuadricula[f][c];
                celda.dataset.fila = f;
                celda.dataset.col = c;
                
                celdasDOM[f][c] = celda;
                tableroEl.appendChild(celda);
            }
        }
    }

    function configurarEventosRaton() {
        tableroEl.addEventListener('mousedown', (e) => {
            if (!e.target.dataset.fila) return;
            isDragging = true;
            celdaInicio = e.target;
            limpiarSeleccionActual();
            seleccionarCelda(e.target);
            e.preventDefault(); 
        });

        tableroEl.addEventListener('mouseover', (e) => {
            if (!isDragging || !e.target.dataset.fila) return;
            calcularLineaRecta(celdaInicio, e.target);
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                validarPalabraSeleccionada();
            }
        });
    }

    function seleccionarCelda(celda) {
        if (!celdasSeleccionadas.includes(celda)) {
            celdasSeleccionadas.push(celda);
            celda.classList.add('celda-seleccionada');
        }
    }

    function limpiarSeleccionActual() {
        celdasSeleccionadas.forEach(c => c.classList.remove('celda-seleccionada'));
        celdasSeleccionadas = [];
    }

    function calcularLineaRecta(inicio, fin) {
        limpiarSeleccionActual();
        
        const f1 = parseInt(inicio.dataset.fila);
        const c1 = parseInt(inicio.dataset.col);
        const f2 = parseInt(fin.dataset.fila);
        const c2 = parseInt(fin.dataset.col);

        const deltaFila = f2 - f1;
        const deltaCol = c2 - c1;

        const esHorizontal = deltaFila === 0;
        const esVertical = deltaCol === 0;
        const esDiagonal = Math.abs(deltaFila) === Math.abs(deltaCol);

        if (esHorizontal || esVertical || esDiagonal) {
            const pasos = Math.max(Math.abs(deltaFila), Math.abs(deltaCol));
            const pasoFila = deltaFila === 0 ? 0 : deltaFila / Math.abs(deltaFila);
            const pasoCol = deltaCol === 0 ? 0 : deltaCol / Math.abs(deltaCol);

            for (let i = 0; i <= pasos; i++) {
                const fActual = f1 + (pasoFila * i);
                const cActual = c1 + (pasoCol * i);
                seleccionarCelda(celdasDOM[fActual][cActual]);
            }
        } else {
            seleccionarCelda(inicio);
        }
    }

    function validarPalabraSeleccionada() {
        if (celdasSeleccionadas.length < 2) {
            limpiarSeleccionActual();
            return;
        }

        const palabraFormada = celdasSeleccionadas.map(c => c.textContent).join('');
        const palabraInvertida = palabraFormada.split('').reverse().join('');

        const objetoPalabra = palabrasObjetivo.find(p => 
            p.palabra.toUpperCase() === palabraFormada || 
            p.palabra.toUpperCase() === palabraInvertida
        );

        if (objetoPalabra) {
            marcarComoEncontrada();
            mostrarDefinicion(objetoPalabra);
            
            const li = document.getElementById(`palabra-${objetoPalabra.palabra.toUpperCase()}`);
            if (li) {
                li.classList.add('palabra-tachada');
            }

            palabrasObjetivo = palabrasObjetivo.filter(p => p.palabra.toUpperCase() !== objetoPalabra.palabra.toUpperCase());
            palabrasEncontradas++;

            if (palabrasObjetivo.length === 0) {
                setTimeout(mostrarModalVictoria, 1500); 
            }
        } else {
            limpiarSeleccionActual();
        }
        celdasSeleccionadas = [];
    }

    function marcarComoEncontrada() {
        celdasSeleccionadas.forEach(c => {
            c.classList.remove('celda-seleccionada');
            c.classList.add('celda-encontrada');
        });
    }

    function mostrarDefinicion(objPalabra) {
        toastPalabra.textContent = objPalabra.palabra;
        toastPista.textContent = objPalabra.pista;
        
        toastDefinicion.classList.remove('opacity-0', '-translate-y-32');
        toastDefinicion.classList.add('opacity-100', 'translate-y-0');
        
        clearTimeout(window.toastTimer);
        window.toastTimer = setTimeout(() => {
            toastDefinicion.classList.remove('opacity-100', 'translate-y-0');
            toastDefinicion.classList.add('opacity-0', '-translate-y-32');
        }, 6000); 
    }

    function mostrarModalVictoria() {
        modalVictoria.classList.remove('hidden');
        setTimeout(() => {
            modalVictoria.classList.remove('opacity-0');
            document.getElementById('modal-content').classList.remove('scale-95');
        }, 10);
    }

    function ocultarModal() {
        modalVictoria.classList.add('opacity-0');
        document.getElementById('modal-content').classList.add('scale-95');
        setTimeout(() => {
            modalVictoria.classList.add('hidden');
        }, 300);
    }

    iniciarJuego();
    btnReiniciar.addEventListener('click', iniciarJuego);
    btnModalReiniciar.addEventListener('click', iniciarJuego);
});