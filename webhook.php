<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

define('VERIFY_TOKEN', 'agrachat_test');

// Define the route for the webhook
Route::match(['get', 'post'], '/webhook', function (Request $request) {
    if ($request->isMethod('get')) {
        // Verify the webhook
        $verifyToken = $request->query('hub_verify_token');
        if ($verifyToken === VERIFY_TOKEN) {
            return response($request->query('hub_challenge'), 200);
        }
        return response('Forbidden', 403);
    } elseif ($request->isMethod('post')) {
        // Handle incoming webhook events
        $data = $request->json()->all();
        error_log('Webhook received: ' . json_encode($data));
        handleWebhookEvent($data); // Custom function to process the event
        return response('OK', 200);
    }
});

/**
 * Process the incoming webhook data.
 *
 * @param array $data
 */
function handleWebhookEvent(array $data)
{
    if (isset($data['entry']) && is_array($data['entry'])) {
        foreach ($data['entry'] as $entry) {
            // Handle messaging events
            if (isset($entry['messaging']) && is_array($entry['messaging'])) {
                foreach ($entry['messaging'] as $message) {
                    $senderId = $message['sender']['id'] ?? null;
                    $messageText = $message['message']['text'] ?? null;
                    if ($messageText) {
                        error_log("Received message from {$senderId}: {$messageText}");
                    }
                }
            }
            // Handle changes events
            if (isset($entry['changes']) && is_array($entry['changes'])) {
                foreach ($entry['changes'] as $change) {
                    if (isset($change['field']) && $change['field'] === 'comments') {
                        $comment = $change['value'];
                        error_log("New comment: " . json_encode($comment));
                    }
                }
            }
        }
    }
}
