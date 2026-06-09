document.addEventListener("DOMContentLoaded", async () => {
    const diccionario = new GestorDiccionario();
    await diccionario.inicializar();
    const tableroEl = document.getElementById('tablero-crucigrama');

    let gridData = [], inputs = [], wordList = [], currentDir = 'H';
    let pistaElements = [];

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
        renderizarTableroRecortado(); // Cambiamos al nuevo renderizado
    }

    function generarCrucigrama() {
        let poolBruto = diccionario.obtenerPalabrasAleatorias(20);
        let pool = poolBruto.filter(p => p.palabra.length <= 15).map(p => ({ ...p, palabra: p.palabra.toUpperCase().trim() }));
        pool.sort((a, b) => b.palabra.length - a.palabra.length);

        if (pool.length === 0) return;

        let placedCount = 0;
        let MAX_WORDS = 8;

        let primera = pool.shift();
        placeWord(primera, 7, Math.floor((15 - primera.palabra.length) / 2), true);
        placedCount++;

        let i = 0;
        while (i < pool.length && placedCount < MAX_WORDS) {
            let wordObj = pool[i];
            let word = wordObj.palabra;
            let placed = false;

            for (let r = 0; r < 15; r++) {
                for (let c = 0; c < 15; c++) {
                    for (let j = 0; j < word.length; j++) {
                        if (gridData[r][c].char === word[j]) {
                            if (canPlace(word, r, c - j, true)) { placeWord(wordObj, r, c - j, true); placed = true; break; }
                            if (canPlace(word, r - j, c, false)) { placeWord(wordObj, r - j, c, false); placed = true; break; }
                        }
                    }
                    if (placed) break;
                }
                if (placed) break;
            }

            if (placed) {
                placedCount++;
                pool.splice(i, 1); 
            } else {
                i++; 
            }
        }

        i = 0;
        while (i < pool.length && placedCount < MAX_WORDS) {
            let wordObj = pool[i];
            let word = wordObj.palabra;
            let placed = false;

            for (let r = 0; r < 15; r += 2) {
                for (let c = 0; c < 15; c += 2) {
                    if (c + word.length <= 15 && canPlace(word, r, c, true)) {
                        placeWord(wordObj, r, c, true); placed = true; break;
                    }
                }
                if (placed) break;
            }
            if (placed) placedCount++;
            i++;
        }
    }

    function canPlace(word, r, c, horiz) {
        if (horiz && (c < 0 || c + word.length > 15)) return false;
        if (!horiz && (r < 0 || r + word.length > 15)) return false;

        for (let i = 0; i < word.length; i++) {
            let currentR = horiz ? r : r + i;
            let currentC = horiz ? c + i : c;
            let cell = gridData[currentR][currentC];

            if (cell.char !== null) {
                if (cell.char !== word[i]) return false;
            } else {
                if (horiz) {
                    if (currentR > 0 && gridData[currentR - 1][currentC].char !== null) return false;
                    if (currentR < 14 && gridData[currentR + 1][currentC].char !== null) return false;
                } else {
                    if (currentC > 0 && gridData[currentR][currentC - 1].char !== null) return false;
                    if (currentC < 14 && gridData[currentR][currentC + 1].char !== null) return false;
                }
            }
        }

        if (horiz) {
            if (c > 0 && gridData[r][c - 1].char !== null) return false;
            if (c + word.length < 15 && gridData[r][c + word.length].char !== null) return false;
        } else {
            if (r > 0 && gridData[r - 1][c].char !== null) return false;
            if (r + word.length < 15 && gridData[r + word.length][c].char !== null) return false;
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

    // --- NUEVO SISTEMA DE RENDERIZADO: BOUNDING BOX ---
    function renderizarTableroRecortado() {
        // 1. Encontrar los bordes exactos del diseño generado
        let minR = 15, maxR = -1, minC = 15, maxC = -1;
        let hasWords = false;

        for (let r = 0; r < 15; r++) {
            for (let c = 0; c < 15; c++) {
                if (gridData[r][c].char) {
                    hasWords = true;
                    if (r < minR) minR = r;
                    if (r > maxR) maxR = r;
                    if (c < minC) minC = c;
                    if (c > maxC) maxC = c;
                }
            }
        }

        if (!hasWords) return;

        let totalCols = maxC - minC + 1;
        tableroEl.style.gridTemplateColumns = `repeat(${totalCols}, minmax(0, 1fr))`;

        let startNumbers = Array(15).fill(null).map(() => Array(15).fill(null));
        wordList.forEach((w) => {
            let sr = w.r, sc = w.c;
            if (!startNumbers[sr][sc]) startNumbers[sr][sc] = w.id + 1;
            else startNumbers[sr][sc] = Math.min(startNumbers[sr][sc], w.id + 1);
        });

        // 2. Iterar SOLO sobre el rectángulo útil
        for (let r = minR; r <= maxR; r++) {
            for (let c = minC; c <= maxC; c++) {
                let cell = document.createElement('div');
                cell.className = 'relative aspect-square'; // aspect-square asegura que las cajas siempre sean cuadradas sin importar la pantalla

                if (gridData[r][c].char) {
                    let input = document.createElement('input');
                    input.maxLength = 1;
                    input.className = 'celda-crucigrama celda-activa w-full h-full';
                    // Mantienen sus coordenadas reales del modelo
                    input.dataset.hId = gridData[r][c].hId !== null ? gridData[r][c].hId : '';
                    input.dataset.vId = gridData[r][c].vId !== null ? gridData[r][c].vId : '';
                    input.dataset.row = r;
                    input.dataset.col = c;

                    if (startNumbers[r][c]) {
                        let numSpan = document.createElement('span');
                        numSpan.className = 'absolute top-0 left-0 text-[10px] sm:text-[12px] font-bold leading-none m-0.5 sm:m-1 celda-numero shadow-sm';
                        numSpan.textContent = startNumbers[r][c];
                        cell.appendChild(numSpan);
                    }

                    // Eventos
                    input.oninput = (e) => {
                        e.target.value = e.target.value.toUpperCase();
                        if (e.target.value !== '') avanzarFoco(r, c);
                        verificarEstado();
                    };

                    input.onkeydown = (e) => {
                        if (e.key === 'Backspace' && e.target.value === '') {
                            e.preventDefault();
                            retrocederFoco(r, c);
                        }
                    };

                    input.onclick = (e) => {
                        let hasH = e.target.dataset.hId !== '';
                        let hasV = e.target.dataset.vId !== '';
                        
                        if (hasH && hasV) {
                            currentDir = (currentDir === 'H') ? 'V' : 'H';
                        } else if (hasH) {
                            currentDir = 'H';
                        } else if (hasV) {
                            currentDir = 'V';
                        }
                        resaltarPalabraActual(r, c);
                    };

                    input.onfocus = (e) => resaltarPalabraActual(r, c);

                    inputs[r][c] = input;
                    cell.appendChild(input);
                } else {
                    let vacio = document.createElement('div');
                    vacio.className = 'celda-vacia w-full h-full';
                    cell.appendChild(vacio);
                }
                tableroEl.appendChild(cell);
            }
        }

        // Pistas
        wordList.forEach((w, i) => {
            let target = w.horiz ? document.getElementById('pistas-horizontales') : document.getElementById('pistas-verticales');
            let li = document.createElement('li');
            li.className = 'mb-2 transition-all duration-300';
            li.innerHTML = `<strong>${i + 1}.</strong> ${w.pista}`;
            li.dataset.wordId = i;
            target.appendChild(li);
            pistaElements.push(li);
        });
    }

    function avanzarFoco(r, c) {
        let nr = r + (currentDir === 'V' ? 1 : 0);
        let nc = c + (currentDir === 'H' ? 1 : 0);
        
        if (nr >= 0 && nr < 15 && nc >= 0 && nc < 15) {
            let nextInput = inputs[nr][nc];
            let inputActual = inputs[r][c];

            if (nextInput) {
                let belongsToSameWord = false;
                if (currentDir === 'H' && inputActual.dataset.hId !== '' && nextInput.dataset.hId === inputActual.dataset.hId) belongsToSameWord = true;
                if (currentDir === 'V' && inputActual.dataset.vId !== '' && nextInput.dataset.vId === inputActual.dataset.vId) belongsToSameWord = true;

                if (belongsToSameWord) {
                    nextInput.focus();
                    nextInput.select();
                }
            }
        }
    }

    function retrocederFoco(r, c) {
        let nr = r + (currentDir === 'V' ? -1 : 0);
        let nc = c + (currentDir === 'H' ? -1 : 0);
        
        if (nr >= 0 && nr < 15 && nc >= 0 && nc < 15) {
            let prevInput = inputs[nr][nc];
            let inputActual = inputs[r][c];

            if (prevInput) {
                let belongsToSameWord = false;
                if (currentDir === 'H' && inputActual.dataset.hId !== '' && prevInput.dataset.hId === inputActual.dataset.hId) belongsToSameWord = true;
                if (currentDir === 'V' && inputActual.dataset.vId !== '' && prevInput.dataset.vId === inputActual.dataset.vId) belongsToSameWord = true;

                if (belongsToSameWord) {
                    prevInput.focus();
                    prevInput.select();
                }
            }
        }
    }

    function resaltarPalabraActual(r, c) {
        let input = inputs[r][c];
        if (!input) return;
        
        let wordId = currentDir === 'H' ? input.dataset.hId : input.dataset.vId;
        
        if (wordId === '') {
            currentDir = (currentDir === 'H') ? 'V' : 'H';
            wordId = currentDir === 'H' ? input.dataset.hId : input.dataset.vId;
        }

        inputs.flat().forEach(i => i?.classList.remove('celda-resaltada'));
        if (wordId !== '') {
            inputs.flat().forEach(i => {
                if (i && ((currentDir === 'H' && i.dataset.hId === wordId) || (currentDir === 'V' && i.dataset.vId === wordId))) {
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

        if (todasCompletas && wordList.length > 0) {
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