<?php

require_once __DIR__ . '/db.php';

function create_payment(int $customer_id, float $amount, string $currency, string $code): int
{
    $stmt = db()->prepare(
        'INSERT INTO payments (customer_id, amount, currency, gateway_payment_code)
        VALUES (?, ?, ?, ?)'
    );
    
    $stmt->execute([$customer_id, $amount, $currency, $code]);
    
    return (int) db()->lastInsertId();
}

function set_payment_gateway(int $id, string $gatewayId, string $url): void
{
    $stmt = db()->prepare(
        'UPDATE payments SET gateway_payment_id = ?, payment_url = ? WHERE id = ?'
    );
    
    $stmt->execute([$gatewayId, $url, $id]);
}

function find_payment_by_code(string $code): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM payments WHERE gateway_payment_code = ?'
    );
    
    $stmt->execute([$code]);
    
    return $stmt->fetch() ?: null;
}

function update_payment_status(int $id, string $status): void
{
    $stmt = db()->prepare(
        'UPDATE payments SET status = ? WHERE id = ?'
    );
    
    $stmt->execute([$status, $id]);
}

function save_transaction (int $paymentId, array $tr): void
{
    $stmt = db()->prepare(
        'INSERT INTO transactions
        (payment_id, gateway_transaction_id, reference_number, status, response_code, response_message, card_first_digits, card_last_digits, rrn, authorization_code, amount, currency, payment_date, raw_response)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        status = VALUES(status),
        response_code = VALUES(response_code),
        response_message = VALUES(response_message),
        raw_response = VALUES(raw_response)'
    );
    
    $stmt->execute([
        $paymentId,
        $tr['id'] ?? null,
        $tr['reference_number'] ?? null,
        $tr['status'] ?? null,
        $tr['response_code'] ?? null,
        $tr['response_message'] ?? null,
        $tr['card']['first_digits'] ?? null,
        $tr['card']['last_digits'] ?? null,
        $tr['rrn'] ?? null,
        $tr['authorization_code'] ?? null,
        $tr['amount'] ?? null,
        $tr['currency'] ?? null,
        $tr['payment_date'] ?? null,
        json_encode($tr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ]);
}

function payment_transactions(int $paymentId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM transactions WHERE payment_id = ?'
    );
    
    $stmt->execute([$paymentId]);
    
    return $stmt->fetchAll() ?: [];
}

function all_payments(): array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name, c.email, c.mobile
FROM payments p
JOIN customers c ON c.id = p.customer_id
ORDER BY p.id DESC'
    );
    
    $stmt->execute();
    
    return $stmt->fetchAll() ?: [];
}

function find_payment_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name, c.email, c.mobile
FROM payments p
JOIN customers c ON c.id = p.customer_id
WHERE p.id = ?'
    );
    
    $stmt->execute([$id]);
    
    return $stmt->fetch() ?: null;
}
