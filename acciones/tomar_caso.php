<?php
// acciones/tomar_caso.php
session_start();

// Validamos que solo un doctor logueado pueda ejecutar esta acción
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';

if (isset($_GET['id'])) {
    $id_caso = $_GET['id'];
    $id_doctor = $_SESSION['usuario_id'];

    // Preparamos la actualización protegiendo el caso de asignaciones dobles
    $sql = "UPDATE casos 
            SET id_doctor_asignado = :id_doctor, estado = 'en_tratamiento' 
            WHERE id = :id_caso AND id_doctor_asignado IS NULL";
    
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['id_doctor' => $id_doctor, 'id_caso' => $id_caso])) {
        // rowCount() nos dice cuántas filas se modificaron realmente
        if ($stmt->rowCount() > 0) {
            // El doctor tomó el caso con éxito
            header("Location: ../vistas/panel_doctor.php?exito=asignado");
            exit;
        } else {
            // El caso ya no estaba disponible (alguien más lo tomó o no existe)
            header("Location: ../vistas/panel_doctor.php?error=no_disponible");
            exit;
        }
    }
}

// Si entran sin un ID válido, los regresamos a su panel
header("Location: ../vistas/panel_doctor.php");
exit;
?>