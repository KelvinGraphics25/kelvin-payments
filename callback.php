<?php
// Callback handler for PesaPal payment confirmation

// Log the raw callback data for debugging (optional but recommended)
file_put_contents('callback-log.txt', json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'request' => $_REQUEST,
    'input' => json_decode(file_get_contents('php://input'), true)
], JSON_PRETTY_PRINT), FILE_APPEND);

// Set HTTP response to acknowledge receipt
http_response_code(200);
echo "✅ Callback received.";

// Optionally process the data if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['order_tracking_id'])) {
        $trackingId = $input['order_tracking_id'];

        // (Optional) Query PesaPal for the transaction status here using the tracking ID
        // You'd authenticate again and make a GET request to:
        // https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus?orderTrackingId=TRACKING_ID

        // You could also log or update a database here if needed
    }
}
?>
