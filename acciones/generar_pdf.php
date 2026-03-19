<?php
// acciones/generar_pdf.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/db.php';
// Requerimos el autoloader de Composer para usar Dompdf
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die("ID de caso no especificado.");
}

$id_caso = $_GET['id'];
$id_usuario = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];

// 1. Consultar el caso y los nombres de los involucrados
$sql_caso = "SELECT c.*, p.nombre AS nombre_paciente, d.nombre AS nombre_doctor 
             FROM casos c 
             JOIN usuarios p ON c.id_paciente = p.id 
             LEFT JOIN usuarios d ON c.id_doctor_asignado = d.id 
             WHERE c.id = :id_caso";
$stmt_caso = $pdo->prepare($sql_caso);
$stmt_caso->execute(['id_caso' => $id_caso]);
$caso = $stmt_caso->fetch(PDO::FETCH_ASSOC);

if (!$caso) die("Caso no encontrado.");

// 2. Seguridad: Validar que tenga permiso de ver este PDF
if (($rol == 'paciente' || $rol == 'estudiante') && $caso['id_paciente'] != $id_usuario) {
    die("Acceso denegado. Este expediente no te pertenece.");
}
if ($rol == 'doctor' && $caso['id_doctor_asignado'] != $id_usuario) {
    die("Acceso denegado. No eres el profesional a cargo de este caso.");
}

// 3. Consultar la bitácora completa
$sql_bitacora = "SELECT a.*, u.nombre AS autor, u.rol 
                 FROM actualizaciones_casos a 
                 JOIN usuarios u ON a.id_usuario = u.id 
                 WHERE a.id_caso = :id_caso 
                 ORDER BY a.fecha_actualizacion ASC";
$stmt_bitacora = $pdo->prepare($sql_bitacora);
$stmt_bitacora->execute(['id_caso' => $id_caso]);
$actualizaciones = $stmt_bitacora->fetchAll(PDO::FETCH_ASSOC);

// 4. Construir el diseño del PDF usando HTML y CSS
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", sans-serif; color: #334155; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0f766e; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #0f766e; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0 0; font-size: 14px; color: #64748b; }
        .datos-generales { background-color: #f8fafc; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .datos-generales table { width: 100%; font-size: 14px; }
        .datos-generales td { padding: 4px 0; }
        .titulo-seccion { font-size: 16px; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; }
        .descripcion { background-color: #f1f5f9; padding: 15px; border-radius: 5px; font-size: 14px; }
        .mensaje { border-left: 4px solid #94a3b8; padding: 10px 15px; margin-bottom: 15px; font-size: 13px; }
        .mensaje.doctor { border-left-color: #3b82f6; background-color: #eff6ff; }
        .mensaje.paciente { border-left-color: #14b8a6; background-color: #f0fdfa; }
        .meta-mensaje { font-size: 11px; color: #64748b; margin-bottom: 5px; }
        .meta-mensaje strong { color: #334155; font-size: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Expediente Clínico Odontológico</h1>
        <p>Documento generado el ' . date('d/m/Y H:i') . '</p>
    </div>

    <div class="datos-generales">
        <table>
            <tr>
                <td><strong>Folio del Caso:</strong> #' . str_pad($caso['id'], 5, "0", STR_PAD_LEFT) . '</td>
                <td><strong>Estado:</strong> ' . strtoupper(str_replace('_', ' ', $caso['estado'])) . '</td>
            </tr>
            <tr>
                <td><strong>Paciente:</strong> ' . htmlspecialchars($caso['nombre_paciente']) . '</td>
                <td><strong>Profesional:</strong> ' . ($caso['nombre_doctor'] ? htmlspecialchars($caso['nombre_doctor']) : 'Pendiente de asignación') . '</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Fecha de Apertura:</strong> ' . date('d/m/Y H:i', strtotime($caso['fecha_creacion'])) . '</td>
            </tr>
        </table>
    </div>

    <h2 class="titulo-seccion">Motivo de Consulta: ' . htmlspecialchars($caso['titulo']) . '</h2>
    <div class="descripcion">
        ' . nl2br(htmlspecialchars($caso['descripcion'])) . '
    </div>

    <h2 class="titulo-seccion">Historial y Bitácora Médica</h2>';

if (count($actualizaciones) > 0) {
    foreach ($actualizaciones as $nota) {
        $clase = ($nota['rol'] == 'doctor') ? 'doctor' : 'paciente';
        $icono = ($nota['rol'] == 'doctor') ? '👨‍⚕️' : '👤';
        
        $html .= '
        <div class="mensaje ' . $clase . '">
            <div class="meta-mensaje">
                ' . $icono . ' <strong>' . htmlspecialchars($nota['autor']) . '</strong> (' . ucfirst($nota['rol']) . ') - ' . date('d/m/Y H:i', strtotime($nota['fecha_actualizacion'])) . '
            </div>
            <div>' . nl2br(htmlspecialchars($nota['mensaje'])) . '</div>
        </div>';
    }
} else {
    $html .= '<p style="font-size: 13px; color: #64748b; font-style: italic;">No existen notas de seguimiento en este caso.</p>';
}

$html .= '
</body>
</html>';

// 5. Configurar e invocar Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); 
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

// (Opcional) Configurar tamaño de papel y orientación
$dompdf->setPaper('A4', 'portrait');

// Renderizar HTML como PDF
$dompdf->render();

// Enviar el PDF al navegador para forzar la descarga
$nombre_archivo = "Expediente_" . str_pad($caso['id'], 5, "0", STR_PAD_LEFT) . "_" . date('Ymd') . ".pdf";
$dompdf->stream($nombre_archivo, array("Attachment" => true));
?>