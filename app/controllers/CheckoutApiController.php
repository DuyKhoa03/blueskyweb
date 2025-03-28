<?php
require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/utils/JWTHandler.php';

class CheckoutApiController
{
    private $cartModel;
    private $accountModel;
    private $orderModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->cartModel = new CartModel($this->db);
        $this->accountModel = new AccountModel($this->db);
        $this->orderModel = new OrderModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    // Xác thực JWT
    private function authenticate()
    {
        
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? null;
            if ($jwt) {
                return $this->jwtHandler->decode($jwt);
            }
        }
        return null;
    }

    // Thanh toán đơn hàng
    public function store()
{
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    $authUser = $this->authenticate();

    if (!$authUser) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
        return;
    }

    $userId = $authUser['id'];
    $address = $data['address'] ?? null;
    $totalAmount = $data['totalCartPrice'] ?? 0;
    $user = $this->accountModel->getAccountById($userId);

    if (!$address) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing address']);
        return;
    }

    // Lấy thông tin giỏ hàng
    $cartItems = $this->cartModel->getCartByUser($userId);
    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['message' => 'Cart is empty']);
        return;
    }

    // Tạo đơn hàng mới (trạng thái UNPAID)
    $orderId = $this->orderModel->createOrderWithStatus($userId, $user['phone'], $address, $totalAmount, 'unpaid');

    // Lưu chi tiết đơn hàng
    foreach ($cartItems as $item) {
        $this->orderModel->addOrderDetails(
            $orderId,
            $item->product_id,
            $item->quantity,
            $item->price
        );
    }

    echo json_encode([
        'message' => 'Order created',
        'order_id' => $orderId
    ]);
}
}
?>