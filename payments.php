<?php
// payments.php – list of all payments + "View" modal
require_once __DIR__ . '/includes/payment.php';

$payments = all_payments();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payments</title>
    <style>
        #modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); }
        #modal { background:#fff; max-width:700px; margin:40px auto; padding:20px; max-height:85vh; overflow:auto; }
    </style>
</head>
<body>
    <p><a href="index.php">New payment</a></p>
    <h1>Payments</h1>

    <table border="1" cellpadding="6">
        <tr>
            <th>#</th><th>Order code</th><th>Customer</th><th>Email</th>
            <th>Amount</th><th>Status</th><th>Created</th><th></th>
        </tr>
        <?php if (!$payments): ?>
            <tr><td colspan="8">No payments yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($payments as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><?= htmlspecialchars($p['gateway_payment_code']) ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td><?= number_format($p['amount'], 3) ?> <?= htmlspecialchars($p['currency']) ?></td>
            <td><?= htmlspecialchars($p['status']) ?></td>
            <td><?= htmlspecialchars($p['created_at']) ?></td>
            <td><button class="view-btn" data-id="<?= (int) $p['id'] ?>">View</button></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div id="modal-overlay">
        <div id="modal">
            <button id="modal-close" style="float:right">Close</button>
            <h2>Payment details</h2>
            <div id="modal-error" style="color:red"></div>

            <h3>From database</h3>
            <table border="1" cellpadding="6" id="db-table"></table>

            <h3>From Gate-E (live)</h3>
            <table border="1" cellpadding="6" id="gateway-table"></table>

            <h3>Transactions</h3>
            <table border="1" cellpadding="6" id="tx-table"></table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/payments.js"></script>
</body>
</html>