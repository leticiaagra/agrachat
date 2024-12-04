<?php
$log_file = "webhook_log.txt";
$access_token = "your_instagram_graph_api_access_token";

// Log payload for debugging
$payload = file_get_contents('php://input');
file_put_contents($log_file, "------\nPayload received:\n$payload\n\n", FILE_APPEND);

// Decode the payload
$data = json_decode($payload, true);

// Process Comments
if (isset($data['entry'])) {
    foreach ($data['entry'] as $entry) {
        if (isset($entry['changes'])) {
            foreach ($entry['changes'] as $change) {
                if ($change['field'] === 'comments') {
                    $comment = $change['value']['text'];
                    $user_id = $change['value']['from']['id'];
                    if (stripos($comment, 'keyword') !== false) {
                        sendDM($user_id, "Here's your link: https://leticiaagra.com.br);
                    }
                }
            }
        }
    }
}

// Function to Send Direct Messages
function sendDM($recipient_id, $message) {
    global $access_token;
    $url = "https://graph.facebook.com/v17.0/me/messages";

    $data = [
        "recipient" => ["id" => $recipient_id],
        "message" => ["text" => $message],
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer $access_token\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    file_put_contents("dm_log.txt", "DM sent to $recipient_id: $message\nResult: $result\n\n", FILE_APPEND);
}

http_response_code(200);
echo json_encode(["status" => "processed"]);
?>
