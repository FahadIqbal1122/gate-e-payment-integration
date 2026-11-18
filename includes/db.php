<?php

function db():PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $config = require __DIR__ . '/../config/config.php';

    $dsn = 'mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'] . ';port=' . $config['port'] . ';charset=' . $config['charset'];

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    } catch (PDOException $e) {
        error_log('DB connection failed: '. $e->getMessage());
        http_response_code(500);
        exit('Database connection failed');
    }

    return $pdo;
}

