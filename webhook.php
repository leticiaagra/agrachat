<?php
$verify_token = "your_verification_token";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['hub_verify_token'])) {
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
    $payload = file_get_contents('php://input'); // Get the JSON payload from Facebook
    
    // Forward to Python app
    $ch = curl_init('http://localhost:5000/webhook'); // Assuming your Python app runs on port 5000
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload))
    );
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Log the response from Python app (optional)
    error_log($response);

    http_response_code(200); // Acknowledge receipt to Facebook
    exit;
}

http_response_code(400); // Bad request
?>
