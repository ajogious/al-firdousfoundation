<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

$env = parse_ini_file(__DIR__ . '/.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'];
        $mail->Password   = $env['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $env['SMTP_PORT'];

        $mail->setFrom($env['SMTP_USER'], 'Website Contact');
        $mail->addAddress('alfirdoushumanitarian@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = $subject ?: 'New Contact Form Message';
        $mail->Body    = "
            <strong>Name:</strong> $name <br>
            <strong>Email:</strong> $email <br><br>
            <strong>Message:</strong><br>$message
        ";
        $mail->AltBody = "Name: $name\nEmail: $email\n\n$message";

        $mail->send();
        header("Location: contact.php?status=success");
        exit;
    } catch (Exception $e) {
        header("Location: contact.php?status=error");
        exit;
    }
}
