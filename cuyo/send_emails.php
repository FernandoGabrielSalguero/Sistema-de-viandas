<?php
include '../includes/load_env.php';

// Cargar variables del archivo .env
loadEnv(__DIR__ . '/../.env');

// Función para enviar correo electrónico usando SMTP
function enviarCorreo($to, $subject, $message) {
    $headers = "From: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Reply-To: " . getenv('SMTP_USERNAME') . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Configuración del transporte SMTP
    $params = [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'auth' => true,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
    ];

    // Usar la función mail() de PHP
    ini_set('SMTP', $params['host']);
    ini_set('smtp_port', $params['port']);
    ini_set('sendmail_from', $params['username']);

    return mail($to, $subject, $message, $headers);
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['emails']) && isset($input['detalle'])) {
    $emails = $input['emails'];
    $detalle_pedidos = $input['detalle'];
    $fecha = date('Y-m-d'); // Esto debe corresponder a la fecha del pedido

    $asunto = "Detalle de Pedido de Viandas - Cuyo Placa";
    $mensaje = "Estimado usuario,\n\nSe ha registrado el siguiente pedido de viandas para la fecha $fecha:\n\n$detalle_pedidos\n\nSaludos cordiales.";

    foreach ($emails as $email) {
        enviarCorreo($email, $asunto, $mensaje);
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
