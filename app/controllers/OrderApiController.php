<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/utils/JWTHandler.php';

class OrderApiController
{
    private $orderModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
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
                $decoded = $this->jwtHandler->decode($jwt);
                if ($decoded && $decoded['role'] === 'admin') {
                    return $decoded;
                }
            }
        }
        return null;
    }

    // Lấy danh sách đơn hàng
    public function index()
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');
        $orders = $this->orderModel->getAllOrders();
        echo json_encode($orders);
    }
}