<?php
// Configuration
$verify_token = "agrachat_test";
$access_token = "EAApOL0kRobMBOwI9qGBBg1s5YyXltA6pEaQeEXZAA8D53muBJYIC8whrDQbt0Ta99RAZCUeOpIzMzVpz3UvgZBsxgQJNmQEttrzJqJvNH4yFeOIggwnN505H4UoOq0uD30Q6rRV1j22ZBEdOeV5V09QnGKvsxKGYBt0CnPE8RvP2jAbgxMuv5AtaYphkBtDpLV7ZAV5ZAlGFHUmczjuAZDZD";
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
        foreach ($data['entry'] as $entry) {
            if (isset($entry['changes'])) {
                foreach ($entry['changes'] as $change) {
                    // Handle Instagram Comments
                    if ($change['field'] === 'comments') {
                        $comment = $change['value']['text'];
                        $user_id = $change['value']['from']['id'];
                        if (stripos($comment, 'keyword') !== false) {
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
