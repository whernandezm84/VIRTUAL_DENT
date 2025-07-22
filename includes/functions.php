<?php
// Función de auditoría
function audit_log($user_id, $action, $details = '') {
    $db = db_connect();
    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address)
                         VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR']
    ]);
}

// Generar PDF de reportes
function generate_pdf_report($data, $title) {
    require_once 'vendor/autoload.php';

    $pdf = new TCPDF();
    $pdf->SetTitle($title);
    $pdf->AddPage();

    $html = "<h1>$title</h1>";
    $html .= "<table>";
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            $html .= "<td>".htmlspecialchars($cell ?? '')."</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</table>";

    $pdf->writeHTML($html, true, false, true, false, '');
    return $pdf->Output('', 'S'); // Retorna el PDF como string
}

// Envío de notificaciones
function send_notification($user_id, $title, $message) {
    $db = db_connect();

    // Insertar en base de datos
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $message]);

    // Enviar por email si está configurado
    if (ENABLE_EMAIL_NOTIFICATIONS) {
        $user = $db->prepare("SELECT email FROM users WHERE id = ?")->fetch([$user_id]);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        // Configuración SMTP...
        $mail->addAddress($user['email']);
        $mail->Subject = $title;
        $mail->Body = $message;
        $mail->send();
    }
}
?>
