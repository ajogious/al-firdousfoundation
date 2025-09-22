<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** @noinspection PhpIncludeInspection */
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

// --- Load .env ---
$env = parse_ini_file(__DIR__ . '/.env');

/** @var string|null $reference */
$reference = $_GET['reference'] ?? null;

if (!$reference) {
    die("No transaction reference supplied");
}

$ch = curl_init("https://api.paystack.co/transaction/verify/" . urlencode($reference));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer " . $env['PAYSTACK_SECRET_KEY']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    error_log("Paystack cURL error: " . curl_error($ch));
    header("Location: /donation-failed.html");
    exit;
}

curl_close($ch);
$result = json_decode($response, true);

if ($result && $result['status'] && $result['data']['status'] === 'success') {
    $data       = $result['data'];
    $donorEmail = $data['customer']['email'] ?? '';
    $amount     = $data['amount'] / 100;
    $currency   = $data['currency'] ?? 'NGN';
    $name       = $data['metadata']['name'] ?? 'Donor';
    $phone      = $data['metadata']['phone'] ?? '';

    // ✅ Step 2: Redirect user immediately
    header("Location: /donation-success.html");
    ignore_user_abort(true);
    flush();

    // ✅ Step 3: Send emails in background
    /**
     * @var array $env
     * @var string $donorEmail
     * @var string $name
     * @var float|int $amount
     * @var string $currency
     * @var string $phone
     * @var string $reference
     */
    register_shutdown_function(function () use ($env, $donorEmail, $name, $amount, $currency, $phone, $reference) {
        // --- Donor email ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $env['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $env['SMTP_USER'];
            $mail->Password   = $env['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->Timeout    = 15;

            $mail->setFrom($env['SMTP_USER'], 'Your Org Name');
            if (filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($donorEmail, $name);
            }
            $mail->isHTML(true);
            $mail->Subject = "Thank you for your donation";
            $mail->Body    = "<p>Dear {$name},</p>
                <p>Thank you for donating <strong>{$currency} {$amount}</strong>.</p>
                <p>We appreciate your support.</p>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Donor email failed: {$mail->ErrorInfo}");
        }

        // --- Admin email ---
        $adminMail = new PHPMailer(true);
        try {
            $adminMail->isSMTP();
            $adminMail->Host       = $env['SMTP_HOST'];
            $adminMail->SMTPAuth   = true;
            $adminMail->Username   = $env['SMTP_USER'];
            $adminMail->Password   = $env['SMTP_PASS'];
            $adminMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $adminMail->Port       = 587;
            $adminMail->Timeout    = 15;

            $adminMail->setFrom($env['SMTP_USER'], 'Donation System');
            if (!empty($env['ADMIN_EMAIL'])) {
                $adminMail->addAddress($env['ADMIN_EMAIL']);
            }
            $adminMail->isHTML(true);
            $adminMail->Subject = "New Donation Received";
            $adminMail->Body    = "<p><strong>{$name}</strong> donated <strong>{$currency} {$amount}</strong>.</p>
                <p>Email: {$donorEmail}<br>Phone: {$phone}<br>Reference: {$reference}</p>";

            $adminMail->send();
        } catch (Exception $e) {
            error_log("Admin email failed: {$adminMail->ErrorInfo}");
        }
    });

    exit;

} else {
    header("Location: /donation-failed.html");
    exit;
}
