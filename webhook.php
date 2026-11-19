<?php

require_once __DIR__ . '/includes/payment.php';
require_once __DIR__ . '/includes/gatee.php';

header('Content-Type: application/json');

function respond(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$input = array_merge($_GET, $_POST);
$json = json_decode(file_get_contents('php://input') ?: '', true);
if (is_array($json)) {
    $input = array_merge($input, $json, is_array($json['payment'] ?? null) ? $json['payment'] : []);
}

$gatewayId   = (string) ($input['payment_id'] ?? $input['id'] ?? '');
$paymentCode = (string) ($input['payment_code'] ?? $input['code'] ?? '');

if ($gatewayId === '' || $paymentCode === '') {
    respond(['success' => false, 'message' => 'Missing payment id or code'], 400);
}

try {
    $payment = find_payment_by_code($paymentCode);
    if (!$payment || $payment['gateway_payment_id'] !== $gatewayId) {
        respond(['success' => false, 'message' => 'Payment not found'], 404);
    }

    $response = gatee_get_payment($gatewayId, $paymentCode);

    foreach ($response['data']['transaction'] ?? [] as $tr) {
        save_transaction($payment['id'], $tr);
    }

    update_payment_status($payment['id'], $response['data']['payment']['status']);

    respond(['success' => true]);
} catch (Exception $e) {
    error_log('webhook failed: ' . $e->getMessage());
    respond(['success' => false, 'message' => 'Webhook processing failed'], 500);
}
