ing<?php
// Configuration

$processed_comments_file = "processed_comments.txt";
$verify_token = "agrachat_test";
$access_token = "EAAM3VAqejpsBO8TLIX5D4uQNsjU9RNDyYFv506XW1CF0EIjZCx3LsSWUtjAKZBPIFo2HvDAknK2AcXtitonUGAPuVcNjtaRNZBJkasDFNmPBIpqwkGmMbTaSNPgHH9jw3mnN4ZAXeyU9G31JuvUnbn4XixHE4YtFhM9ZCOUa0zSM6MmpUlNbgFReBr5BgGnzinzsFLl2AiwHju5A7Eee6ojaY";
$log_file = "webhook_log.txt";

 // Function to Send Direct Messages
function sendDM($recipient_id, $message) {
    global $log_file;  // Add this line to access the global variable
    $access_token = "EAAM3VAqejpsBO8TLIX5D4uQNsjU9RNDyYFv506XW1CF0EIjZCx3LsSWUtjAKZBPIFo2HvDAknK2AcXtitonUGAPuVcNjtaRNZBJkasDFNmPBIpqwkGmMbTaSNPgHH9jw3mnN4ZAXeyU9G31JuvUnbn4XixHE4YtFhM9ZCOUa0zSM6MmpUlNbgFReBr5BgGnzinzsFLl2AiwHju5A7Eee6ojaY";
    file_put_contents($log_file, "OK5\n", FILE_APPEND);

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

    if ($result === false) {
        file_put_contents("dm_log.txt", "Failed to send DM. Error: " . error_get_last()['message'] . "\n", FILE_APPEND);
    } else {
        file_put_contents("dm_log.txt", "DM API Response: $result\n", FILE_APPEND);
    }
}



// Helper Functions for Deduplication
function hasBeenProcessed($comment_id) {
    global $processed_comments_file;
    $processed_ids = file_exists($processed_comments_file) ? file($processed_comments_file, FILE_IGNORE_NEW_LINES) : [];
    return in_array($comment_id, $processed_ids);
}

function markAsProcessed($comment_id) {
    global $processed_comments_file;
    file_put_contents($processed_comments_file, $comment_id . PHP_EOL, FILE_APPEND);
}

function replyToComment($comment_id, $reply_message) {
    global $access_token;
    $url = "https://graph.facebook.com/v17.0/$comment_id/replies";

    $data = [
        "message" => $reply_message,
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $access_token\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    // Log API request details
    file_put_contents("comment_reply_log.txt", "Reply Request Data: " . json_encode($data) . "\n", FILE_APPEND);

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        $error = error_get_last();
        file_put_contents("comment_reply_log.txt", "HTTP Error: {$error['message']}\n", FILE_APPEND);
    } else {
        file_put_contents("comment_reply_log.txt", "Reply API Response: $result\n", FILE_APPEND);
    }
}

// Webhook Verification (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $verify_token) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        file_put_contents($log_file, "OK0 - Webhook verified\n", FILE_APPEND);
        exit;
    } else {
        http_response_code(403);
        file_put_contents($log_file, "ERROR - Webhook verification failed\n", FILE_APPEND);
        exit;
    }
}

// Webhook Payload Handling (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = file_get_contents('php://input');
    file_put_contents($log_file, "Payload received:\n" . $payload . "\n\n", FILE_APPEND);

    $data = json_decode($payload, true);
    if (isset($data['entry'])) {
        file_put_contents($log_file, "OK1 - Entry found\n", FILE_APPEND);
        foreach ($data['entry'] as $entry) {
            // Handle Instagram Comments
            if (isset($entry['changes'])) {
                file_put_contents($log_file, "OK2 - Changes found\n", FILE_APPEND);
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'comments') {
                        file_put_contents($log_file, "OK3 - Processing comments\n", FILE_APPEND);
                        $comment_id = $change['value']['id'];
                        $comment = $change['value']['text'];
                        $user_id = $change['value']['from']['id'];

                        // Skip already processed comments
                        if (hasBeenProcessed($comment_id)) {
                            file_put_contents($log_file, "SKIP - Comment $comment_id already processed\n", FILE_APPEND);
                            continue;
                        }

                        // Process new comment
                        if (stripos($comment, 'keyword') !== false) {
                            file_put_contents($log_file, "OK4 - Comment keyword matched: $user_id\n", FILE_APPEND);
                            sendDM($user_id, "Testing API Comment Response");
                            file_put_contents($log_file, "OK5 - Comment DM sent to $user_id\n", FILE_APPEND);

                           // Reply to comment
                           replyToComment($comment_id, "Hey, I sent you a DM!");
                           file_put_contents($log_file, "OK6 - Reply sent to comment $comment_id\n", FILE_APPEND);

                            // Mark as processed
                            markAsProcessed($comment_id);
                        }
                    }
                }
            }

            // Handle Instagram Messages
            if (isset($entry['messaging'])) {
                file_put_contents($log_file, "OK2 - Messaging found\n", FILE_APPEND);
                foreach ($entry['messaging'] as $message_event) {
                    $message = $message_event['message']['text'] ?? null; // Ensure message text exists
                    $sender_id = $message_event['sender']['id'] ?? null; // Ensure sender ID exists

                    if ($message && $sender_id) {
                        file_put_contents($log_file, "OK3 - Processing message: $message by $sender_id\n", FILE_APPEND);
                        if (stripos($message, 'keyword') !== false) {
                            file_put_contents($log_file, "OK4 - Message keyword matched: $sender_id\n", FILE_APPEND);
                            sendDM($sender_id, "Testing API DM Response");
                            file_put_contents($log_file, "OK5 - Message DM sent to $sender_id\n", FILE_APPEND);
                        }
                    }
                }
            }
        }
    }
    http_response_code(200);
    echo json_encode(["status" => "processed"]);
    exit;
}

   

?>
