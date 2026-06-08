document.addEventListener("DOMContentLoaded", async () => {
    const diccionario = new GestorDiccionario();
    await diccionario.inicializar();
    const tableroEl = document.getElementById('tablero-crucigrama');

    let gridData = [], inputs = [], wordList = [], currentDir = 'H';
    let pistaElements = [];

    // Modal
    const modalVictoria = document.getElementById('modal-victoria');
    const modalContenido = document.getElementById('modal-contenido');
    const btnModalReiniciar = document.getElementById('btn-modal-reiniciar');
    const btnReiniciar = document.getElementById('btn-reiniciar');

    function iniciarJuego() {
        tableroEl.innerHTML = '';
        document.getElementById('pistas-horizontales').innerHTML = '';
        document.getElementById('pistas-verticales').innerHTML = '';
        ocultarVictoria();

        gridData = Array(15).fill(null).map(() => Array(15).fill(null).map(() => ({ char: null, hId: -1, vId: -1 })));
        inputs = Array(15).fill(null).map(() => Array(15).fill(null));
        wordList = [];
        pistaElements = [];

        generarCrucigrama();
        renderizarTablero();
    }

    function generarCrucigrama() {
        let pool = diccionario.obtenerPalabrasAleatorias(8);
        pool.sort((a, b) => b.palabra.length - a.palabra.length);

        pool.forEach((wordObj, index) => {
            let word = wordObj.palabra.toUpperCase();
            let placed = false;

            if (index === 0) {
                placeWord(wordObj, 7, Math.floor((15 - word.length) / 2), true);
                return;
            }

            for (let r = 0; r < 15; r++) {
                for (let c = 0; c < 15; c++) {
                    for (let i = 0; i < word.length; i++) {
                        if (gridData[r][c].char === word[i]) {
                            if (canPlace(word, r, c - i, true)) { placeWord(wordObj, r, c - i, true); placed = true; break; }
                            if (canPlace(word, r - i, c, false)) { placeWord(wordObj, r - i, c, false); placed = true; break; }
                        }
                    }
                    if (placed) break;
                }
                if (placed) break;
            }
        });
    }

    function canPlace(word, r, c, horiz) {
        if (horiz && (c < 0 || c + word.length > 15)) return false;
        if (!horiz && (r < 0 || r + word.length > 15)) return false;
        for (let i = 0; i < word.length; i++) {
            let cell = horiz ? gridData[r][c + i] : gridData[r + i][c];
            if (cell.char !== null && cell.char !== word[i]) return false;
        }
        return true;
    }

    function placeWord(wordObj, r, c, horiz) {
        let word = wordObj.palabra.toUpperCase();
        let id = wordList.length;
        for (let i = 0; i < word.length; i++) {
            let cell = horiz ? gridData[r][c + i] : gridData[r + i][c];
            cell.char = word[i];
            if (horiz) cell.hId = id;
            else cell.vId = id;
        }
        wordList.push({ ...wordObj, r, c, horiz, id });
    }

    function renderizarTablero() {
        tableroEl.style.gridTemplateColumns = `repeat(15, minmax(0, 1fr))`;

        let startNumbers = Array(15).fill(null).map(() => Array(15).fill(null));
        wordList.forEach((w) => {
            let sr = w.r, sc = w.c;
            if (!startNumbers[sr][sc]) startNumbers[sr][sc] = w.id + 1;
            else startNumbers[sr][sc] = Math.min(startNumbers[sr][sc], w.id + 1);
        });

        for (let r = 0; r < 15; r++) {
            for (let c = 0; c < 15; c++) {
                let cell = document.createElement('div');
                cell.className = 'relative';

                if (gridData[r][c].char) {
                    let input = document.createElement('input');
                    input.maxLength = 1;
                    input.className = 'celda-crucigrama celda-activa w-full h-8 sm:h-12';
                    input.dataset.hId = gridData[r][c].hId !== null ? gridData[r][c].hId : '';
                    input.dataset.vId = gridData[r][c].vId !== null ? gridData[r][c].vId : '';
                    input.dataset.row = r;
                    input.dataset.col = c;

                    // Numerito con la clase que evita interceptar clics
                    if (startNumbers[r][c]) {
                        let numSpan = document.createElement('span');
                        numSpan.className = 'absolute top-0 left-0 text-[10px] font-bold text-blue-800 leading-none p-0.5 celda-numero';
                        numSpan.textContent = startNumbers[r][c];
                        cell.appendChild(numSpan);
                    }

                    // --- Manejo de escritura (hacia adelante) ---
                    input.oninput = (e) => {
                        e.target.value = e.target.value.toUpperCase();
                        if (e.target.value) {
                            let row = parseInt(e.target.dataset.row);
                            let col = parseInt(e.target.dataset.col);
                            avanzarASiguienteCeldaVacia(row, col);
                        } else {
                            // Si la celda quedó vacía (p.ej., por suprimir), retrocede
                            let row = parseInt(e.target.dataset.row);
                            let col = parseInt(e.target.dataset.col);
                            retrocederCeldaAnterior(row, col);
                        }
                        verificarEstado();
                    };

                    // --- Manejo de Backspace (tecla) ---
                    input.onkeydown = (e) => {
                        if (e.key === 'Backspace') {
                            let row = parseInt(e.target.dataset.row);
                            let col = parseInt(e.target.dataset.col);
                            // Si ya está vacía, antes de que actúe el navegador, nos movemos atrás
                            if (e.target.value === '') {
                                e.preventDefault();
                                retrocederCeldaAnterior(row, col);
                            }
                            // Si tiene letra, se borrará y luego el oninput se encargará de retroceder
                        }
                        // Flechas: podrías añadir navegación con teclas si quieres
                    };

                    input.onclick = (e) => {
                        currentDir = (currentDir === 'H') ? 'V' : 'H';
                        let row = parseInt(e.target.dataset.row);
                        let col = parseInt(e.target.dataset.col);
                        resaltarPalabraActual(row, col);
                    };

                    inputs[r][c] = input;
                    cell.appendChild(input);
                }
                tableroEl.appendChild(cell);
            }
        }

        // Pistas
        wordList.forEach((w, i) => {
            let target = w.horiz
                ? document.getElementById('pistas-horizontales')
                : document.getElementById('pistas-verticales');
            let li = document.createElement('li');
            li.className = 'mb-2';
            li.innerHTML = `<strong>${i + 1}.</strong> ${w.pista}`;
            li.dataset.wordId = i;
            target.appendChild(li);
            pistaElements.push(li);
        });
    }

    // Avanza dentro de la misma palabra hasta encontrar una celda vacía
    function avanzarASiguienteCeldaVacia(r, c) {
        let paso = 1;
        let inputActual = inputs[r][c];
        while (true) {
            let nr = r + (currentDir === 'V' ? paso : 0);
            let nc = c + (currentDir === 'H' ? paso : 0);
            if (nr < 0 || nr >= 15 || nc < 0 || nc >= 15) break;
            let nextInput = inputs[nr]?.[nc];
            if (!nextInput) break;

            // ¿Misma palabra?
            let mismoId = false;
            if (currentDir === 'H' && inputActual.dataset.hId && nextInput.dataset.hId === inputActual.dataset.hId) {
                mismoId = true;
            } else if (currentDir === 'V' && inputActual.dataset.vId && nextInput.dataset.vId === inputActual.dataset.vId) {
                mismoId = true;
            }
            if (!mismoId) break;

            if (nextInput.value === '') {
                nextInput.focus();
                break;
            }
            paso++;
        }
    }

    // Retrocede dentro de la misma palabra, borra la letra que encuentre
    function retrocederCeldaAnterior(r, c) {
        let paso = 1;
        let inputActual = inputs[r][c];
        while (true) {
            let nr = r + (currentDir === 'V' ? -paso : 0);
            let nc = c + (currentDir === 'H' ? -paso : 0);
            if (nr < 0 || nr >= 15 || nc < 0 || nc >= 15) break;
            let prevInput = inputs[nr]?.[nc];
            if (!prevInput) break;

            let mismoId = false;
            if (currentDir === 'H' && inputActual.dataset.hId && prevInput.dataset.hId === inputActual.dataset.hId) {
                mismoId = true;
            } else if (currentDir === 'V' && inputActual.dataset.vId && prevInput.dataset.vId === inputActual.dataset.vId) {
                mismoId = true;
            }
            if (!mismoId) break;

            // Si la celda anterior tiene algo, la borramos y movemos el foco
            if (prevInput.value !== '') {
                prevInput.value = '';
                prevInput.focus();
                break;
            }
            // Si está vacía, seguimos hacia atrás por si hay más celdas vacías
            paso++;
        }
    }

    function resaltarPalabraActual(r, c) {
        let input = inputs[r][c];
        if (!input) return;
        let wordId = currentDir === 'H' ? input.dataset.hId : input.dataset.vId;
        inputs.flat().forEach(i => i?.classList.remove('celda-resaltada'));
        if (wordId) {
            inputs.flat().forEach(i => {
                if (i && ((currentDir === 'H' && i.dataset.hId === wordId) ||
                          (currentDir === 'V' && i.dataset.vId === wordId))) {
                    i.classList.add('celda-resaltada');
                }
            });
        }
    }

    function verificarEstado() {
        let todasCompletas = true;

        wordList.forEach(w => {
            let completa = true;
            for (let i = 0; i < w.palabra.length; i++) {
                let r = w.horiz ? w.r : w.r + i;
                let c = w.horiz ? w.c + i : w.c;
                if (!inputs[r][c] || inputs[r][c].value.toUpperCase() !== w.palabra[i]) {
                    completa = false;
                    break;
                }
            }

            if (completa) {
                for (let i = 0; i < w.palabra.length; i++) {
                    let r = w.horiz ? w.r : w.r + i;
                    let c = w.horiz ? w.c + i : w.c;
                    inputs[r][c]?.classList.add('celda-correcta');
                }
                let pistaLi = pistaElements[w.id];
                if (pistaLi && !pistaLi.classList.contains('pista-tachada')) {
                    pistaLi.classList.add('pista-tachada');
                }
            } else {
                todasCompletas = false;
            }
        });

        if (todasCompletas) {
            mostrarVictoria();
        }
    }

    function mostrarVictoria() {
        modalVictoria.classList.remove('hidden');
        setTimeout(() => {
            modalVictoria.classList.remove('opacity-0');
            modalContenido.classList.remove('scale-95');
            modalContenido.classList.add('scale-100');
        }, 10);
    }

    function ocultarVictoria() {
        modalVictoria.classList.add('hidden', 'opacity-0');
        modalContenido.classList.add('scale-95');
        modalContenido.classList.remove('scale-100');
    }

    btnReiniciar.onclick = iniciarJuego;
    btnModalReiniciar.onclick = iniciarJuego;

    iniciarJuego();
});