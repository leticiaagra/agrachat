<?php
// Configuration
$verify_token = "agrachat_test";
$access_token = "IGQWRNWV84cnJQMU9DZAkVrYTFkWE9hRlZAXdFBSRGN6U2tGckdRdzNhODkxX1RVajRXcWpqVURaQk92Y0kwUldGYUt5MDhIa0ZA1X0EzZA3lNelJuSzJFY05yT2FZASmt0MVVNTEY2eUt2MTI3dFJtRzZAvLWliTmZAFN3cZD";
$log_file = "webhook_log.txt";

// Handle Facebook Webhook Verification (GET Request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $verify_token) {
        // Respond with the challenge token sent by Facebook
        echo $_GET['hub_challenge'];
        http_response_code(200); // Success
        exit;
    } else {
        http_response_code(403); // Forbidden if token mismatch
        exit;
    }
}

// Handle Webhook Payload (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST payload
    $payload = file_get_contents('php://input');
    
    // Log the payload for debugging
    file_put_contents($log_file, "Payload received:\n$payload\n\n", FILE_APPEND);
    
    // Decode the JSON payload
    $data = json_decode($payload, true);

    // Check for entries in the payload
    if (isset($data['entry'])) {
        file_put_contents($log_file, "OK1\n", FILE_APPEND);
        file_put_contents($log_file, "$data['entry']\n", FILE_APPEND);
        foreach ($data['entry'] as $entry) {
            if (isset($entry['changes'])) {
                file_put_contents($log_file, "OK2/\n", FILE_APPEND);
                foreach ($entry['changes'] as $change) {
                    // Handle Instagram Comments
                    if ($change['field'] === 'comments') {
                        file_put_contents($log_file, "OK3\n", FILE_APPEND);
                        $comment = $change['value']['text'];
                        $user_id = $change['value']['from']['id'];
                        if (stripos($comment, 'keyword') !== false) {
                            file_put_contents($log_file, "OK4\n", FILE_APPEND);
                            sendDM($user_id, "Here's your link: https://example.com");
                        }
                    }

                    // Handle Instagram Messages
                    if ($change['field'] === 'messages') {
                        $message = $change['value']['text'];
                        $sender_id = $change['value']['from']['id'];
                        if (stripos($message, 'keyword') !== false) {
                            sendDM($sender_id, "Thank you for your message. Here's your link: https://example.com");
                        }
                    }
                }
            }
        }
    }

    // Send a 200 OK response to acknowledge receipt
    http_response_code(200);
    echo json_encode(["status" => "processed"]);
    exit;
}

// If the request method is neither GET nor POST, return a 400 Bad Request
http_response_code(400);
echo "Bad Request";
exit;

// Function to Send Direct Messages
function sendDM($recipient_id, $message) {
    file_put_contents($log_file, "OK5\n", FILE_APPEND);
    global $access_token;
    $url = "https://graph.facebook.com/v21.0/me/messages";

    $data = [
        "recipient" => ["id" => $recipient_id],
        "message" => ["text" => $message],
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $access_token\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    file_put_contents("dm_log.txt", "DM sent to $recipient_id: $message\nResult: $result\n\n", FILE_APPEND);
}
?>
