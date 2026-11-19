<?php

require_once __DIR__ . '/../includes/customer.php';
require_once __DIR__ . '/../includes/payment.php';
require_once __DIR__ . '/../includes/gatee.php';

header('Content-Type: application/json');

$productName = 'Gaming PC';
$productPrice = 1000;

function respond(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Invalid request method'], 405);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');

if (empty($name) || empty($email) || empty($mobile)) {
    respond(['success' => false, 'message' => 'Missing required fields'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['success' => false, 'message' => 'Invalid email address'], 400);
}

if (!preg_match('/^\d{8}$/', $mobile)) {
    respond(['success' => false, 'message' => 'Invalid phone number'], 400);
}
 
try {
    $config = require __DIR__ . '/../config/config.php';

    $customerId = create_customer($name, $email, $mobile);
    $paymentCode = uniqid();
    $paymentId = create_payment($customerId, $productPrice, $config['currency'], $paymentCode);

    $response = gatee_create_payment(
        [
            'type' => 'pay',
            'code' => $paymentCode,
            'amount' => $productPrice,
            'currency' => $config['currency'],
            'language' => 'en',
            'description' => $productName,
            'gateway' => ['id' => $config['gateway_id']],
            'client' => [
                'name' => $name,
                'email' => $email,
                'mobile_code' => '973',
                'mobile' => $mobile,
            ],
            'notification' => [
                'email_send' => 0,
                'whatsapp_send' => 0,
                'sms_send' => 0,
            ],
            'settings' => [
                'redirect_url' => $config['base_url'] . 'callback.php',
                'callback_url' => $config['base_url'] . 'webhook.php',
            ],
        ],
        [
            [
                'name' => $productName,
                'amount' => $productPrice,
                'amount_unit' => $productPrice,
                'amount_subtotal' => $productPrice,
                'quantity' => 1,
            ]
        ]
    );
    
    $gatewayId = $response['data']['payment']['id'];
    $paymentUrl = $response['data']['payment']['link'];

    set_payment_gateway($paymentId, $gatewayId, $paymentUrl);
    respond(['success' => true, 'payment_url' => $paymentUrl], 200);
    
} catch (Exception $e) {
    error_log('create_payment failed: ' . $e->getMessage());
    respond(['success' => false, 'message' => 'Could not start the payment. Please try again.'], 500);
}

