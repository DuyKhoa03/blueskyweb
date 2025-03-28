<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Load Composer autoload

use Dotenv\Dotenv;

class EmailService {
    private $mail;

    public function __construct() {
        // Load biến môi trường từ file .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP() {
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['MAIL_USERNAME'];
        $this->mail->Password   = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port       = 587;

        $this->mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $this->mail->isHTML(true);
    }

    public function sendResetCode($toEmail, $code) {
        try {
            $this->mail->addAddress($toEmail);
            $this->mail->Subject = 'Mã khôi phục mật khẩu';
            $this->mail->Body    = "<p>Xin chào,</p>
                <p>Bạn vừa yêu cầu khôi phục mật khẩu. Mã xác nhận của bạn là:</p>
                <h2 style='color:blue;'>$code</h2>
                <p>Mã này sẽ hết hạn sau 5 phút.</p>
                <p>Trân trọng,<br>BlueSky Shop</p>";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Gửi email thất bại: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
