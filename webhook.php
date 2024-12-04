<?php
// Configuration

$verify_token = "agrachat_test";
$access_token = "EAAM3VAqejpsBO2e7PgdJCMbHAuj9Y3ilzbKrcUCTg2TKuZA9xiqqpm9WBQPHIAxzpGDV8lBqFc8TMRcbLufzgGLfoh4tmzNzhw7NGpzcPSsPpuN5AfDGYqwjRCI8EVzmEZAIPIHDoChcs5P6F6qjoCqr88tRiZAxtyv5kiicQLj2g84wohlPgzAFkaPtbWIPNHrH7bC9iTKBCNyBRhLlQorTwZDZD";
$log_file = "webhook_log.txt";

 // Function to Send Direct Messages
function sendDM($recipient_id, $message) {
    file_put_contents($log_file, "OK5\n", FILE_APPEND);
    $access_token = "EAAM3VAqejpsBO2e7PgdJCMbHAuj9Y3ilzbKrcUCTg2TKuZA9xiqqpm9WBQPHIAxzpGDV8lBqFc8TMRcbLufzgGLfoh4tmzNzhw7NGpzcPSsPpuN5AfDGYqwjRCI8EVzmEZAIPIHDoChcs5P6F6qjoCqr88tRiZAxtyv5kiicQLj2g84wohlPgzAFkaPtbWIPNHrH7bC9iTKBCNyBRhLlQorTwZDZD";
    $url = "https://graph.facebook.com/v21.0/350830891436655/messages";

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



// Handle Webhook Payload (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST payload
    file_put_contents($log_file, "ANTES\n", FILE_APPEND);
    sendDM("6449027881841171", "Here's your link: https://example.com");
    file_put_contents($log_file, "DEPOIS\n", FILE_APPEND);
    $payload = file_get_contents('php://input');
    
    // Log the payload for debugging
    file_put_contents($log_file, "Payload received:\n$payload\n\n", FILE_APPEND);
    
    // Decode the JSON payload
    $data = json_decode($payload, true);

    // Check for entries in the payload
    if (isset($data['entry'])) {
        file_put_contents($log_file, "OK1\n", FILE_APPEND);
        foreach ($data['entry'] as $entry) {
            if (isset($entry['changes'])) {
                file_put_contents($log_file, "OK2\n", FILE_APPEND);
                foreach ($entry['changes'] as $change) {
                    // Handle Instagram Comments
                    if ($change['field'] === 'comments') {
                        file_put_contents($log_file, "OK3\n", FILE_APPEND);
                        $comment = $change['value']['text'];
                        $user_id = $change['value']['from']['id'];
                        if (stripos($comment, 'keyword') !== false) {
                            file_put_contents($log_file, "$user_id\n", FILE_APPEND);
                            sendDM($user_id, "Here's your link: https://example.com");
                            file_put_contents($user_id, "OK6\n", FILE_APPEND);
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

   

?>
