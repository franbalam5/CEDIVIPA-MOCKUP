class GestorDiccionario {
    constructor() {
        // Ajusta esta ruta dependiendo de dónde mandes a llamar el script
        this.rutaDiccionario = '../config/diccionario.json'; 
        this.terminos = [];
    }

    // 1. Cargar el JSON de forma asíncrona
    async inicializar() {
        try {
            const respuesta = await fetch(this.rutaDiccionario);
            
            if (!respuesta.ok) {
                throw new Error(`HTTP error! status: ${respuesta.status}`);
            }
            
            const datos = await respuesta.json();
            this.terminos = datos.terminos;
            console.log(`Diccionario listo: ${this.terminos.length} términos cargados.`);
            return true;
            
        } catch (error) {
            console.error("Hubo un problema al cargar el diccionario:", error);
            return false;
        }
    }

    // 2. Obtener un número específico de palabras al azar (ideal para sopa de letras)
    obtenerPalabrasAleatorias(cantidad) {
        // Clonamos el arreglo para no modificar el original
        const copiaTerminos = [...this.terminos];
        
        // Algoritmo de Fisher-Yates para mezclar el arreglo
        for (let i = copiaTerminos.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copiaTerminos[i], copiaTerminos[j]] = [copiaTerminos[j], copiaTerminos[i]];
        }
        
        // Devolvemos solo la cantidad solicitada
        return copiaTerminos.slice(0, cantidad);
    }

    // 3. Filtrar palabras por tamaño exacto o límite (vital para crucigramas)
    obtenerPalabrasPorLongitud(longitudMaxima, longitudMinima = 2) {
        return this.terminos.filter(t => 
            t.longitud >= longitudMinima && t.longitud <= longitudMaxima
        );
    }

    // 4. Buscar una palabra específica para validar respuestas
    validarPalabra(palabraBuscada) {
        return this.terminos.find(t => 
            t.palabra.toUpperCase() === palabraBuscada.toUpperCase()
        );
    }
}