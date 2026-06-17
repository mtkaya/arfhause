<?php
require_once 'phpmailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Form verileri
    $name = isset($_POST["name"]) ? strip_tags(trim($_POST["name"])) : '';
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : '';
    $project_type = isset($_POST["project_type"]) ? strip_tags(trim($_POST["project_type"])) : 'Belirtilmedi';
    $message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : '';

    // Doğrulama
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(["success" => false, "message" => "Lütfen tüm alanları doldurun."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Geçersiz email adresi."]);
        exit;
    }

    $mail = new PHPMailer();

    // SMTP Ayarları - Natro Kurumsal E-posta
    $mail->isSMTP();
    $mail->Host = 'srvm04.kurumsaleposta.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hello@arfhause.com';
    $mail->Password = '.t8cg5X.X@8cO5_O';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    // Gönderici ve Alıcı
    $mail->setFrom('hello@arfhause.com', 'Arfhause Website');
    $mail->addAddress('hello@arfhause.com');
    $mail->addReplyTo($email, $name);

    // Mail içeriği
    $mail->isHTML(false);
    $mail->Subject = "Yeni Iletisim Formu: " . $name;

    $body = "Yeni bir iletisim formu gonderildi:\n\n";
    $body .= "Isim: $name\n";
    $body .= "Email: $email\n";
    $body .= "Proje Tipi: $project_type\n\n";
    $body .= "Mesaj:\n$message\n";

    $mail->Body = $body;

    // Mail gönder
    if ($mail->send()) {
        echo json_encode(["success" => true, "message" => "Mesajiniz gonderildi!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Mail gonderilemedi: " . $mail->getErrorInfo()]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Gecersiz istek."]);
}
?>
