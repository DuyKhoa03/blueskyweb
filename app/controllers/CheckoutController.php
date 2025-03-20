<?php
require_once 'app/utils/JWTHandler.php';

class CheckoutController {
    public function index() {
        // Bắt đầu session nếu chưa có
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra token đăng nhập
        if (!isset($_SESSION['jwtToken'])) {
            header('Location: /blueskyweb/account/login');
            exit();
        }

        $jwtHandler = new JWTHandler();
        $token = $_SESSION['jwtToken'];
        $userId = null;

        try {
            $tokenData = $jwtHandler->decode($token);
            $userId = $tokenData['id'] ?? null;
        } catch (Exception $e) {
            unset($_SESSION['jwtToken']);
            header('Location: /blueskyweb/account/login');
            exit();
        }

        // Nếu không có userId, quay lại đăng nhập
        if (!$userId) {
            header('Location: /blueskyweb/account/login');
            exit();
        }

        // Load view checkout
        include 'app/views/checkout/checkout.php';
    }
}
?>
