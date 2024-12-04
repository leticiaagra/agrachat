<?php
$VERIFY_TOKEN = "agrachat_test";

// Handle verification (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_GET['hub_verify_token'] === $VERIFY_TOKEN) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        exit;
    } else {
        http_response_code(403); // Forbidden
        echo "Forbidden";
        exit;
    }
}

// Handle incoming webhook events (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Log the event for debugging
    file_put_contents("webhook_log.txt", print_r($data, true), FILE_APPEND);

    // Respond to Facebook
    echo "OK";
    http_response_code(200);
    exit;
}
?>
