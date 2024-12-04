<?php
// File: instagram_webhook.php

// Set the log file path
$logFile = 'instagram_webhook_log.txt';

// Facebook verification token
$verifyToken = 'agrachat_test';

// Handle GET requests (verification challenge)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verify the request
    if ($_GET['hub_verify_token'] === $verifyToken) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        exit;
    } else {
        echo 'Invalid verify token';
        http_response_code(403);
        exit;
    }
}

// Handle POST requests (payload reception)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST body
    $payload = file_get_contents('php://input');
    file_put_contents($logFile, file_get_contents('php://input') . "\n", FILE_APPEND);

    // Decode the JSON payload
    $data = json_decode($payload, true);

    // Append the payload to the log file
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Payload Received:\n", FILE_APPEND);
    file_put_contents($logFile, print_r($data, true), FILE_APPEND);
    file_put_contents($logFile, "\n--------------------\n", FILE_APPEND);

    // Respond with a 200 status to acknowledge receipt
    http_response_code(200);
    echo 'Payload received';
    exit;
}

// If no valid request method
http_response_code(405);
echo 'Method Not Allowed';
exit;
