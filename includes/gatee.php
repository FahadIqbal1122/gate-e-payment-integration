<?php

function gatee_request(string $resource, string $action, array $data = []): array
{
    $config = require __DIR__ . '/../config/config.php';

    $body = array_merge([
        'api' => [
            'user' => $config['api_user'],
            'key' => $config['api_key'],
            'version' => '01',
            'action' => $action,
            'resource' => $resource,
            'response' => [
                'type' => 'json'
            ]
        ]
    ], $data);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $config['api_link'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($curl);

    if ($raw === false){
        $error = curl_error($curl);
        curl_close($curl);
        error_log("Gate-E connection error: $error");
        throw new Exception('Could not connect to the payment gateway.');
    }
    curl_close($curl);

    $response = json_decode($raw, true);

    if (!is_array($response)) {
        error_log("Gate-E invalid response: $raw");
        throw new Exception('Invalid response from the payment gateway.');
    }
    if (($response['status'] ?? 0) != 1) {
        error_log('Gate-E error: ' . $raw);
        throw new Exception($response['message'] ?: 'Gateway returned an error');
    }

    return $response;
}


function gatee_list_gateways(): array
{
    return gatee_request('gateway', 'list');
}

function gatee_create_payment(array $payment, array $details): array
{
    return gatee_request('payment', 'create', ['payment' => $payment, 'payment_details' => $details]);
}

function gatee_get_payment(string $id, string $code): array
{
    return gatee_request('payment', 'get', [
        
        'payment' => [
            'id' => $id,
            'code' => $code
        ]
    ]);
}