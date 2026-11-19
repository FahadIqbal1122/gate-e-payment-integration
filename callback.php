<?php

require_once __DIR__ . '/includes/payment.php';
require_once __DIR__ . '/includes/gatee.php';

$error = null;
$payment = null;
$transactions = [];

$gatewayId = $_GET['payment_id'] ?? '';
$paymentCode = $_GET['payment_code'] ?? '';

try {
    
    $payment = find_payment_by_code($paymentCode);
    if (!$payment) {
        throw new Exception('Payment not found');
    }

    if ($payment['gateway_payment_id'] !== $gatewayId) {
        throw new Exception('Payment ID does not match');
    }

    $response = gatee_get_payment($gatewayId, $paymentCode);

    foreach ($response['data']['transaction'] ?? [] as $tr) {
        save_transaction($payment['id'], $tr);
    }

    update_payment_status($payment['id'], $response['data']['payment']['status']);

    $payment = find_payment_by_id($payment['id']);
    $transactions = payment_transactions($payment['id']);

} catch (Exception $e) {
    error_log('callback failed: ' . $e->getMessage());
    $error = 'We could not verify this payment.';
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Payment Details</title>
        <script>
            if (window.top !== window.self) {
                window.top.location.href = window.self.location.href;
            }
        </script>
    </head>

    <body>
    <p><a href="index.php">New payment</a> | <a href="payments.php">View payments</a></p>

    <?php if ($error): ?>
        <h1>Error</h1>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>

    <?php else: ?>
        <?php if ($payment['status'] === 'paid'): ?>
            <h1 style="color:green">Payment successful</h1>
        <?php else: ?>
            <h1 style="color:red">Payment failed</h1>
        <?php endif; ?>

        <h2>Payment details</h2>
        <table border="1" cellpadding="6">
            <tr><th>Order code</th>     <td><?= htmlspecialchars($payment['gateway_payment_code']) ?></td></tr>
            <tr><th>Gate-E ID</th>      <td><?= htmlspecialchars($payment['gateway_payment_id']) ?></td></tr>
            <tr><th>Amount</th>         <td><?= number_format($payment['amount'], 3) ?> <?= htmlspecialchars($payment['currency']) ?></td></tr>
            <tr><th>Status</th>         <td><?= htmlspecialchars($payment['status']) ?></td></tr>
            <tr><th>Customer</th>       <td><?= htmlspecialchars($payment['name']) ?></td></tr>
            <tr><th>Email</th>          <td><?= htmlspecialchars($payment['email']) ?></td></tr>
            <tr><th>Mobile</th>         <td><?= htmlspecialchars($payment['mobile']) ?></td></tr>
            <tr><th>Created</th>        <td><?= htmlspecialchars($payment['created_at']) ?></td></tr>
        </table>

        <h2>Transactions</h2>
        <table border="1" cellpadding="6">
            <tr>
                <th>Reference</th><th>Status</th><th>Message</th><th>Card</th>
                <th>RRN</th><th>Auth code</th><th>Date</th>
            </tr>
            <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['reference_number'] ?? '-') ?></td>
                <td><?= htmlspecialchars($t['status']) ?></td>
                <td><?= htmlspecialchars($t['response_message'] ?? '-') ?></td>
                <td>
                    <?= $t['card_last_digits']
                        ? htmlspecialchars($t['card_first_digits'] . '******' . $t['card_last_digits'])
                        : '-' ?>
                </td>
                <td><?= htmlspecialchars($t['rrn'] ?? '-') ?></td>
                <td><?= htmlspecialchars($t['authorization_code'] ?? '-') ?></td>
                <td><?= htmlspecialchars($t['payment_date'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>