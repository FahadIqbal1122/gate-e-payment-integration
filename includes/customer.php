<?php

require_once __DIR__ . '/db.php';

function create_customer(string $name, string $email, string $mobile): int
{
    $stmt = db()->prepare(
        'INSERT INTO customers (name, email, mobile) VALUES (?, ?, ?)'
    );
    
    $stmt->execute([$name, $email, $mobile]);
    
    return (int) db()->lastInsertId();
}
