<?php
require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/utils/JWTHandler.php';

class CartController
{
    private $cartModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->cartModel = new CartModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    // Hiển thị giỏ hàng
    public function index()
    {
        // Khởi động session nếu chưa có
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra token trong session
        if (!isset($_SESSION['jwtToken'])) {
            header('Location: /blueskyweb/account/login');
            exit();
        }

        $token = $_SESSION['jwtToken'];

        // Giải mã token để lấy userId
        try {
            $tokenData = $this->jwtHandler->decode($token);
            $userId = $tokenData['id'];
        } catch (Exception $e) {
            // Token không hợp lệ hoặc hết hạn
            unset($_SESSION['jwtToken']);
            header('Location: /blueskyweb/account/login');
            exit();
        }

        // Load view giỏ hàng
        include_once 'app/views/cart/index.php';
    }
    
    // Thêm sản phẩm vào giỏ hàng (gọi API từ view)
    public function add()
    {
        // Không cần xử lý trực tiếp ở đây, view sẽ gọi API /api/cart/store
        $this->index(); // Tạm thời hiển thị lại giỏ hàng
    }

    // Cập nhật giỏ hàng (gọi API từ view)
    public function update()
    {
        // Không cần xử lý trực tiếp, view sẽ gọi API /api/cart/update
        $this->index(); // Tạm thời hiển thị lại giỏ hàng
    }

    // Xóa sản phẩm khỏi giỏ hàng (gọi API từ view)
    public function remove()
    {
        // Không cần xử lý trực tiếp, view sẽ gọi API /api/cart/destroy
        $this->index(); // Tạm thời hiển thị lại giỏ hàng
    }
}
?>