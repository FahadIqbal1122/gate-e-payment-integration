<?php

$productName = "Gaming PC";
$productPrice = 1000;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
</head>
<body>
    <h1>Checkout</h1>
    <h2><?php echo $productName; ?> - <?php echo number_format($productPrice, 3); ?> BHD</h2>
    <p><a href="payments.php">View Payments</a></p>

    <form id="checkout-form">
        <label for="name">Name</label>
        <input required type="text" id="name" name="name"  placeholder="Name">
        <label for="email">Email</label>
        <input required type="email" id="email" name="email"  placeholder="Email">
        <label for="mobile">Mobile</label>
        <input required type="tel" id="mobile" name="mobile"  placeholder="Mobile">
        <button id="submit-button" type="submit">Submit</button>
    </form>

    <div id="error-message" style="color: red; display: none;">
        Please fill in all fields correctly.
    </div>

    <div id="payment-container" style="display: none;">
    <iframe id="payment-iframe" src="" style="width: 100%; height: 600px;"></iframe>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/app.js"></script>
</body>
</html>