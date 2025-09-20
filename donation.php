<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

$env = parse_ini_file(__DIR__ . '/.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $amount   = $_POST['amount'] ?? '';
    $currency = $_POST['currency'] ?? '';
    $note     = $_POST['note'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'];
        $mail->Password   = $env['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $env['SMTP_PORT'];

        $mail->setFrom('info@alfirdousfoundation.org', 'Donation Form');
        $mail->addAddress('alfirdoushumanitarian@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = "Donation Confirmation from $name";
        $mail->Body    = "
            <h2>New Donation Confirmation</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Amount:</strong> $amount $currency</p>
            <p><strong>Note:</strong><br>$note</p>
        ";

        $mail->send();
        header("Location: donate-page.php?status=success");
        exit;
    } catch (Exception $e) {
        header("Location: donate-page.php?status=error");
        exit;
    }
}
