<?php
$verify_token = "your_verification_token";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['hub_verify_token'])) {
    // Facebook webhook verification
    if ($_GET['hub_verify_token'] === $verify_token) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        exit;
    } else {
        http_response_code(403);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON payload sent by Facebook
    $payload = file_get_contents('php://input');
    
    // Forward the payload to your Python app
    $ch = curl_init('http://localhost:5000/webhook'); // Python app URL
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload))
    );

    $response = curl_exec($ch); // Forward the request and capture the response
    curl_close($ch);

    // Log the response for debugging
    error_log("Forwarded to Python app: $response");

    // Respond to Facebook webhook
    http_response_code(200);
    echo json_encode(["status" => "processed"]);
    exit;
}

http_response_code(400); // Bad request if the method is unsupported
