<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // đường dẫn này đúng nếu bạn dùng Composer

class EmailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP() {
        // Cấu hình SMTP của Gmail
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'ticau0000@gmail.com';        // 💡 Thay bằng Gmail của bạn
        $this->mail->Password   = '...';          // 💡 Mật khẩu ứng dụng (App Password)
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port       = 587;

        $this->mail->setFrom('ticau0000@gmail.com', 'BlueSkyWeb'); // 💡 Tên hiển thị
        $this->mail->isHTML(true); // Gửi HTML
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
