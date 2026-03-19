<?php
// acciones/procesar_resolucion.php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_caso = $_POST['id_caso'];
    $diagnostico_final = trim($_POST['diagnostico_final']);
    $enviar_trivia = isset($_POST['enviar_trivia']) ? 1 : 0;
    $id_doctor = $_SESSION['usuario_id'];

    if (!empty($diagnostico_final)) {
        
        // 1. Guardar la nota de cierre en la bitácora
        $mensaje_final = "DIAGNÓSTICO DE CIERRE:\n" . $diagnostico_final;
        $sql_nota = "INSERT INTO actualizaciones_casos (id_caso, id_usuario, mensaje) VALUES (:id_caso, :id_usuario, :mensaje)";
        $stmt_nota = $pdo->prepare($sql_nota);
        $stmt_nota->execute(['id_caso' => $id_caso, 'id_usuario' => $id_doctor, 'mensaje' => $mensaje_final]);

        // 2. Actualizar el caso con el diagnóstico y la bandera de trivia
        $sql_caso = "UPDATE casos 
                     SET estado = 'resuelto', apto_para_trivia = :trivia, diagnostico_final = :diag 
                     WHERE id = :id_caso AND id_doctor_asignado = :id_doctor";
        $stmt_caso = $pdo->prepare($sql_caso);
        $stmt_caso->execute([
            'trivia' => $enviar_trivia, 
            'diag' => $diagnostico_final,
            'id_caso' => $id_caso, 
            'id_doctor' => $id_doctor
        ]);

        header("Location: ../vistas/panel_doctor.php?exito=resuelto");
        exit;
    }
}

header("Location: ../vistas/panel_doctor.php");
exit;
?>