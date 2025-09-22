<?php
// initialize_payment.php
header('Content-Type: application/json');

$env = parse_ini_file(__DIR__ . '/.env');

$body = json_decode(file_get_contents('php://input'), true);

$email    = $body['email'] ?? '';
$name     = $body['name'] ?? '';
$phone    = $body['phone'] ?? '';
$amount   = $body['amount'] ?? 0;
$currency = $body['currency'] ?? 'NGN';

$amountInSmallest = (int)($amount * 100);

$payload = [
  'email' => $email,
  'amount' => $amountInSmallest,
  'currency' => $currency,
  'callback_url' => 'http://localhost:8000/donation_callback.php',
  'metadata' => [
    'name' => $name,
    'phone' => $phone
  ]
];

$ch = curl_init("https://api.paystack.co/transaction/initialize");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer " . $env['PAYSTACK_SECRET_KEY'],
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        'status' => false,
        'message' => 'cURL error: ' . curl_error($ch)
    ]);
} else {
    echo $response;
}

curl_close($ch);
