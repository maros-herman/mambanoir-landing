<?php
// send-email.php
// Skript na spracovanie waitlist formulára z index.html
// Používa PHPMailer + SMTP
//
// DÔLEŽITÉ: Tento súbor vyžaduje priečinok "PHPMailer/src/" s tromi súbormi
// (Exception.php, PHPMailer.php, SMTP.php) nahratý v rovnakom priečinku ako tento skript.

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Povolia sa iba POST požiadavky
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metóda nie je povolená.']);
    exit;
}

// Načítanie dát z formulára
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validácia emailu
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná emailová adresa.']);
    exit;
}

// --- SMTP NASTAVENIA (Webhouse) ---
$smtpHost = 'mail.webhouse.sk';
$smtpUser = 'info@mambanoir.sk';
$smtpPass = 'h%oYEALJ@QD9*3D';
$smtpPort = 587;
// -----------------------------------

$mail = new PHPMailer(true);

try {
    // Nastavenia servera
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtpPort;
    $mail->CharSet    = 'UTF-8';

    // Odosielateľ a príjemca
    $mail->setFrom($smtpUser, 'Mambanoir Waitlist');
    $mail->addAddress('info@mambanoir.sk');
    $mail->addReplyTo($email);

    // Obsah emailu
    $mail->Subject = 'Nový záujemca - Waitlist Mambanoir';
    $mail->Body    = "Nová registrácia na waitlist.\n\nEmail: {$email}\nDátum: " . date('d.m.Y H:i:s');

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email bol úspešne odoslaný.']);
} catch (Exception $e) {
    http_response_code(500);
    // Chyba sa zapíše do log súboru na serveri (neposiela sa návštevníkovi)
    @error_log(date('Y-m-d H:i:s') . ' - ' . $mail->ErrorInfo . PHP_EOL, 3, __DIR__ . '/mail-errors.log');
    echo json_encode(['success' => false, 'message' => 'Email sa nepodarilo odoslať.']);
}
