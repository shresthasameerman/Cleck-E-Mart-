<?php
// These helper functions handle sending automated emails (like order receipts or password resets) using PHPMailer.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Helper function to send an email using PHPMailer and Gmail SMTP.
 *
 * @param string $toAddress The recipient's email address.
 * @param string $subject The email subject.
 * @param string $htmlBody The HTML content of the email.
 * @param string $altBody The plain-text fallback content.
 * @return bool True on success, false on failure.
 */
function send_email(string $toAddress, string $subject, string $htmlBody, string $altBody = ''): bool
{
    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // TODO: Replace with your actual Gmail address and 16-character App Password
        $mail->Username   = 'shresthasameerman@gmail.com'; 
        $mail->Password   = 'gwya euim abce cqba'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Recipients ---
        $mail->setFrom('shresthasameerman@gmail.com', 'Cleck E-Mart');
        $mail->addAddress($toAddress);

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody === '' ? strip_tags($htmlBody) : $altBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
