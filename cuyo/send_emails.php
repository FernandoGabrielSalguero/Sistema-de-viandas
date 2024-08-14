<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['emails']) && isset($input['detalle'])) {
    $emails = $input['emails'];
    $detalle_pedidos = $input['detalle'];
    $fecha = date('Y-m-d'); // Esto debe corresponder a la fecha del pedido

    $asunto = "Detalle de Pedido de Viandas - Cuyo Placa";
    $mensaje = "Estimado usuario,\n\nSe ha registrado el siguiente pedido de viandas para la fecha $fecha:\n\n$detalle_pedidos\n\nSaludos cordiales.";
    $headers = "From: no-reply@cuyoplaca.com";

    foreach ($emails as $email) {
        mail($email, $asunto, $mensaje, $headers);
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
}
