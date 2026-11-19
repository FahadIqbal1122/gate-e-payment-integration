<?php
// api/payment_details.php
// Returns one payment's details from the DATABASE and from GATE-E, as JSON.
require_once __DIR__ . '/../includes/payment.php';
require_once __DIR__ . '/../includes/gatee.php';

header('Content-Type: application/json');

function respond(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    respond(['success' => false, 'message' => 'Invalid payment ID'], 400);
}

$payment = find_payment_by_id($id);
if (!$payment) {
    respond(['success' => false, 'message' => 'Payment not found'], 404);
}
$transactions = payment_transactions($id);

$gateway      = null;
$gatewayError = null;
if ($payment['gateway_payment_id']) {
    try {
        $res = gatee_get_payment($payment['gateway_payment_id'], $payment['gateway_payment_code']);
        $gp  = $res['data']['payment'];
        $gateway = [
            'status'           => $gp['status'],
            'amount'           => $gp['amount'],
            'amount_paid'      => $gp['amount_paid'],
            'amount_remaining' => $gp['amount_remaining'],
            'currency'         => $gp['currency'],
            'transactions'     => count($res['data']['transaction'] ?? []),
        ];
    } catch (Exception $e) {
        error_log('payment_details gateway call failed: ' . $e->getMessage());
        $gatewayError = 'Could not reach the payment gateway.';
    }
}

respond([
    'success'       => true,
    'payment'       => $payment,
    'transactions'  => $transactions,
    'gateway'       => $gateway,
    'gateway_error' => $gatewayError,
]);