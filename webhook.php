<?php
// Your verification token
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

    // Log the event for debugging (optional)
    file_put_contents("webhook_log.txt", print_r($data, true), FILE_APPEND);

    // Forward the data to your Python script (optional)
    $url = "http://localhost:5000/process_webhook"; // Update to your Python server endpoint
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Respond with a success message
    echo "OK";
    http_response_code(200);
    exit;
}
?>
