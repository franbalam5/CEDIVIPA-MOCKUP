<?php
// acciones/agregar_actualizacion.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_caso = $_POST['id_caso'];
    $mensaje = trim($_POST['mensaje']);
    $id_usuario = $_SESSION['usuario_id'];

    if (!empty($mensaje)) {
        
        // 1. Insertamos primero el mensaje en la bitácora
        $sql = "INSERT INTO actualizaciones_casos (id_caso, id_usuario, mensaje) 
                VALUES (:id_caso, :id_usuario, :mensaje)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([
            'id_caso' => $id_caso, 
            'id_usuario' => $id_usuario, 
            'mensaje' => $mensaje
        ])) {
            
            // 2. Obtenemos el ID de la actualización recién creada
            $id_actualizacion_nueva = $pdo->lastInsertId();

            // 3. LÓGICA DE SUBIDA MÚLTIPLE
            if (isset($_FILES['archivos']) && !empty($_FILES['archivos']['name'][0])) {
                $directorio_destino = '../uploads/';
                $formatos_permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
                
                $total_archivos = count($_FILES['archivos']['name']);
                
                for ($i = 0; $i < $total_archivos; $i++) {
                    if ($_FILES['archivos']['error'][$i] == UPLOAD_ERR_OK) {
                        
                        $nombre_original = basename($_FILES['archivos']['name'][$i]);
                        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                        
                        if (in_array($extension, $formatos_permitidos)) {
                            $nombre_seguro = time() . '_act_' . rand(100, 999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $nombre_original);
                            $ruta_absoluta = $directorio_destino . $nombre_seguro;
                            
                            if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $ruta_absoluta)) {
                                $ruta_db = 'uploads/' . $nombre_seguro; 
                                
                                // Insertamos relacionando tanto el id_caso como el id_actualizacion
                                $sql_adjunto = "INSERT INTO adjuntos (id_caso, id_actualizacion, ruta_archivo, nombre_original) 
                                                VALUES (:id_caso, :id_actualizacion, :ruta, :nombre_orig)";
                                $stmt_adj = $pdo->prepare($sql_adjunto);
                                $stmt_adj->execute([
                                    'id_caso' => $id_caso,
                                    'id_actualizacion' => $id_actualizacion_nueva,
                                    'ruta' => $ruta_db,
                                    'nombre_orig' => $nombre_original
                                ]);
                            }
                        }
                    }
                }
            }

            // Redirigimos de vuelta al expediente
            header("Location: ../vistas/expediente.php?id=" . $id_caso);
            exit;
        }
    }
}

header("Location: ../index.php");
exit;
?>