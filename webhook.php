<?php
$verify_token = "agrachat_test";
$log_file = "webhook_log.txt"; // Log file path

$log_file = "webhook_log.txt";
file_put_contents($log_file, "Request received:\n", FILE_APPEND);
file_put_contents($log_file, "Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents($log_file, "Headers:\n" . json_encode(getallheaders()) . "\n", FILE_APPEND);
file_put_contents($log_file, "Body:\n" . file_get_contents('php://input') . "\n\n", FILE_APPEND);

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

    // Log payload received from Facebook
    file_put_contents($log_file, "Payload received from Facebook:\n", FILE_APPEND);
    file_put_contents($log_file, $payload . "\n\n", FILE_APPEND);

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

    // Log the response from the Python app
    error_log("Response from Python app: " . $response);

    // Respond to Facebook webhook
    http_response_code(200);
    echo json_encode(["status" => "processed"]);
    exit;
}

http_response_code(400); // Bad request if the method is unsupported
?>
