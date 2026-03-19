<?php
// acciones/crear_seguimiento.php
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['paciente', 'estudiante'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_caso_padre = $_POST['id_caso_padre'];
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $id_paciente = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($descripcion)) {
        
        // 1. Buscamos al doctor del caso original
        $sql_padre = "SELECT id_doctor_asignado FROM casos WHERE id = :id_padre AND id_paciente = :id_paciente";
        $stmt_padre = $pdo->prepare($sql_padre);
        $stmt_padre->execute(['id_padre' => $id_caso_padre, 'id_paciente' => $id_paciente]);
        $padre = $stmt_padre->fetch(PDO::FETCH_ASSOC);

        if ($padre) {
            $id_doctor_asignado = $padre['id_doctor_asignado']; 

            // 2. Insertamos el nuevo caso (ya sin la columna archivo_ruta, porque la borramos de esta tabla)
            $sql_insert = "INSERT INTO casos (id_paciente, id_doctor_asignado, titulo, descripcion, id_caso_padre) 
                           VALUES (:id_paciente, :id_doctor, :titulo, :descripcion, :id_padre)";
            $stmt_insert = $pdo->prepare($sql_insert);
            
            if ($stmt_insert->execute([
                'id_paciente' => $id_paciente,
                'id_doctor' => $id_doctor_asignado,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'id_padre' => $id_caso_padre
            ])) {
                
                // 3. Obtenemos el ID del caso de seguimiento recién creado
                $id_caso_nuevo = $pdo->lastInsertId();

                // 4. LÓGICA DE SUBIDA MÚLTIPLE
                if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
                    $directorio_destino = '../uploads/';
                    $formatos_permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
                    
                    $total_archivos = count($_FILES['archivos']['name']);
                    
                    for ($i = 0; $i < $total_archivos; $i++) {
                        if ($_FILES['archivos']['error'][$i] == UPLOAD_ERR_OK) {
                            
                            $nombre_original = basename($_FILES['archivos']['name'][$i]);
                            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                            
                            if (in_array($extension, $formatos_permitidos)) {
                                $nombre_seguro = time() . '_seg_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $nombre_original);
                                $ruta_absoluta = $directorio_destino . $nombre_seguro;
                                
                                if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $ruta_absoluta)) {
                                    $ruta_db = 'uploads/' . $nombre_seguro; 
                                    
                                    // Guardamos en adjuntos vinculando al ID del ticket de seguimiento
                                    $sql_adjunto = "INSERT INTO adjuntos (id_caso, ruta_archivo, nombre_original) 
                                                    VALUES (:id_caso, :ruta, :nombre_orig)";
                                    $stmt_adj = $pdo->prepare($sql_adjunto);
                                    $stmt_adj->execute([
                                        'id_caso' => $id_caso_nuevo,
                                        'ruta' => $ruta_db,
                                        'nombre_orig' => $nombre_original
                                    ]);
                                }
                            }
                        }
                    }
                }

                $vista_retorno = ($_SESSION['rol'] == 'paciente') ? 'panel_paciente.php' : 'panel_estudiante.php';
                header("Location: ../vistas/" . $vista_retorno . "?exito=seguimiento");
                exit;
            }
        }
    }
}

header("Location: ../index.php");
exit;
?>