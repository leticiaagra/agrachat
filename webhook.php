<?php
// Enable error logging
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

$VERIFY_TOKEN = "agrachat_test";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_GET['hub_verify_token'] === $VERIFY_TOKEN) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        exit;
    } else {
        http_response_code(403);
        echo "Forbidden";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Log headers and method type
    file_put_contents("webhook_log.txt", print_r($_SERVER, true), FILE_APPEND);
    file_put_contents("webhook_log.txt", "Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);


    // Acknowledge receipt
    echo "OK";
    http_response_code(200);
    exit;
}
?>
