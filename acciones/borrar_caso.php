<?php
// acciones/borrar_caso.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_caso'])) {
    $id_caso = $_POST['id_caso'];
    $id_usuario = $_SESSION['usuario_id'];
    $rol = $_SESSION['rol'];

    // 1. Verificamos que el caso exista y esté resuelto
    $sql_verificar = "SELECT * FROM casos WHERE id = :id_caso AND estado = 'resuelto'";
    $stmt_ver = $pdo->prepare($sql_verificar);
    $stmt_ver->execute(['id_caso' => $id_caso]);
    $caso = $stmt_ver->fetch(PDO::FETCH_ASSOC);

    $puede_borrar = false;

    // 2. Seguridad: Solo el paciente/estudiante dueño, o el doctor asignado pueden borrarlo
    if ($caso) {
        if (($rol == 'paciente' || $rol == 'estudiante') && $caso['id_paciente'] == $id_usuario) {
            $puede_borrar = true;
        } else if ($rol == 'doctor' && $caso['id_doctor_asignado'] == $id_usuario) {
            $puede_borrar = true;
        }
    }

    if ($puede_borrar) {
        // 3. ELIMINACIÓN FÍSICA DE ARCHIVOS PARA LIBERAR ESPACIO
        $sql_archivos = "SELECT ruta_archivo FROM adjuntos WHERE id_caso = :id_caso";
        $stmt_archivos = $pdo->prepare($sql_archivos);
        $stmt_archivos->execute(['id_caso' => $id_caso]);
        $archivos = $stmt_archivos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivos as $archivo) {
            // Construimos la ruta real hacia la carpeta
            $ruta_fisica = '../' . $archivo['ruta_archivo'];
            // Si el archivo existe en el disco duro, lo eliminamos con unlink()
            if (file_exists($ruta_fisica)) {
                unlink($ruta_fisica);
            }
        }

        // 4. ELIMINACIÓN EN CASCADA EN LA BASE DE DATOS
        // A) Borramos registros de adjuntos
        $pdo->prepare("DELETE FROM adjuntos WHERE id_caso = ?")->execute([$id_caso]);
        
        // B) Borramos si estaba en la trivia
        $pdo->prepare("DELETE FROM trivia WHERE id_caso = ?")->execute([$id_caso]);
        
        // C) Borramos la bitácora/chat
        $pdo->prepare("DELETE FROM actualizaciones_casos WHERE id_caso = ?")->execute([$id_caso]);

        // D) Si este caso era el "padre" de un seguimiento, le quitamos la etiqueta a los hijos para que la BD no marque error
        $pdo->prepare("UPDATE casos SET id_caso_padre = NULL WHERE id_caso_padre = ?")->execute([$id_caso]);

        // E) Finalmente, borramos el caso original
        $pdo->prepare("DELETE FROM casos WHERE id = ?")->execute([$id_caso]);

        // Redirigimos al usuario a su panel correspondiente con mensaje de éxito
        $vista_retorno = "panel_" . $rol . ".php";
        header("Location: ../vistas/" . $vista_retorno . "?exito=borrado");
        exit;
    }
}

// Si algo sale mal o intentan entrar por URL, los regresamos a su panel
$vista_retorno = isset($_SESSION['rol']) ? "panel_" . $_SESSION['rol'] . ".php" : "index.php";
header("Location: ../vistas/" . $vista_retorno);
exit;
?>