<?php
// acciones/crear_caso.php
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['paciente', 'estudiante'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $id_paciente = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($descripcion)) {
        
        // 1. Primero insertamos el caso principal (sin archivos)
        $sql = "INSERT INTO casos (id_paciente, titulo, descripcion) VALUES (:id_paciente, :titulo, :descripcion)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute(['id_paciente' => $id_paciente, 'titulo' => $titulo, 'descripcion' => $descripcion])) {
            
            // 2. Obtenemos el ID exacto del caso que la base de datos acaba de generar
            $id_caso_nuevo = $pdo->lastInsertId();

            // 3. LÓGICA DE SUBIDA MÚLTIPLE DE ARCHIVOS
            // Verificamos si enviaron archivos y si el primer elemento no está vacío
            if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
                $directorio_destino = '../uploads/';
                $formatos_permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
                
                // Contamos cuántos archivos enviaron y hacemos un ciclo
                $total_archivos = count($_FILES['archivos']['name']);
                
                for ($i = 0; $i < $total_archivos; $i++) {
                    if ($_FILES['archivos']['error'][$i] == UPLOAD_ERR_OK) {
                        
                        $nombre_original = basename($_FILES['archivos']['name'][$i]);
                        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                        
                        if (in_array($extension, $formatos_permitidos)) {
                            // Agregamos un número aleatorio rand() para evitar choques de nombres si suben 2 fotos llamadas "img1.jpg"
                            $nombre_seguro = time() . '_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $nombre_original);
                            $ruta_absoluta = $directorio_destino . $nombre_seguro;
                            
                            if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $ruta_absoluta)) {
                                $ruta_db = 'uploads/' . $nombre_seguro; 
                                
                                // Insertamos el archivo en nuestra nueva tabla relacional
                                $sql_adjunto = "INSERT INTO adjuntos (id_caso, ruta_archivo, nombre_original) 
                                                VALUES (:id_caso, :ruta, :nombre_orig)";
                                $stmt_adj = $pdo->prepare($sql_adjunto);
                                $stmt_adj->execute([
                                    'id_caso' => $id_caso_nuevo,
                                    'ruta' => $ruta_db,
                                    'nombre_orig' => $nombre_original // Guardamos el nombre original para que el botón se vea bonito
                                ]);
                            }
                        }
                    }
                }
            }
            // --- FIN LÓGICA DE SUBIDA MÚLTIPLE ---

            $vista_retorno = ($_SESSION['rol'] == 'paciente') ? 'panel_paciente.php' : 'panel_estudiante.php';
            header("Location: ../vistas/" . $vista_retorno . "?exito=1");
            exit;
        }
    }
}

header("Location: ../index.php");
exit;
?>