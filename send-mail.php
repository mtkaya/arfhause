<?php
require_once 'phpmailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

// Yapilandirmayi repo disindaki config.php'den yukle (sifre git'e girmez).
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Sunucu yapilandirmasi eksik."]);
    exit;
}
$config = require $configFile;

header('Content-Type: application/json');
$allowedOrigin = $config['ALLOWED_ORIGIN'] ?? '';
if ($allowedOrigin !== '') {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Gecersiz istek."]);
    exit;
}

// Spam korumasi: honeypot. Gercek kullanici bu gizli alani gormez/doldurmaz;
// botlar doldurur. Doluysa botu sessizce "basarili" kabul edip cikiyoruz
// (boylece bot honeypot'u fark edip tekrar denemez, mail de atilmaz).
if (!empty($_POST["website"])) {
    echo json_encode(["success" => true, "message" => "Mesajiniz gonderildi!"]);
    exit;
}

// Form verileri (alan adi hem "project-type" hem "project_type" kabul edilir)
$name    = isset($_POST["name"]) ? strip_tags(trim($_POST["name"])) : '';
$email   = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
$projectType = $_POST["project-type"] ?? $_POST["project_type"] ?? '';
$projectType = $projectType !== '' ? strip_tags(trim($projectType)) : 'Belirtilmedi';
$message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : '';

// Dogrulama
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(["success" => false, "message" => "Lutfen tum alanlari doldurun."]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Gecersiz email adresi."]);
    exit;
}

$mail = new PHPMailer();

// SMTP Ayarlari - config.php'den
$mail->isSMTP();
$mail->Host       = $config['SMTP_HOST'];
$mail->SMTPAuth   = true;
$mail->Username   = $config['SMTP_USER'];
$mail->Password   = $config['SMTP_PASS'];
$mail->SMTPSecure = $config['SMTP_SECURE'];
$mail->Port       = $config['SMTP_PORT'];
$mail->CharSet    = 'UTF-8';

// Gonderici ve Alici
$mail->setFrom($config['MAIL_FROM'], 'Arfhause Website');
$mail->addAddress($config['MAIL_TO']);
$mail->addReplyTo($email, $name);

// Mail icerigi
$mail->isHTML(false);
$mail->Subject = "Yeni Iletisim Formu: " . $name;

$body  = "Yeni bir iletisim formu gonderildi:\n\n";
$body .= "Isim: $name\n";
$body .= "Email: $email\n";
$body .= "Proje Tipi: $projectType\n\n";
$body .= "Mesaj:\n$message\n";

$mail->Body = $body;

if ($mail->send()) {
    echo json_encode(["success" => true, "message" => "Mesajiniz gonderildi!"]);
} else {
    echo json_encode(["success" => false, "message" => "Mail gonderilemedi."]);
}
