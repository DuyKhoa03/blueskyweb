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
    public function getOrdersByUser()
{
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $arr = explode(" ", $authHeader);
        $jwt = $arr[1] ?? null;
        if ($jwt) {
            $decoded = $this->jwtHandler->decode($jwt);
            if ($decoded) {
                $userId = $decoded['id'];
                $orders = $this->orderModel->getOrdersByUserId($userId);
                header('Content-Type: application/json');
                echo json_encode($orders);
                return;
            }
        }
    }

    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
}
public function cancel($id)
{
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $arr = explode(" ", $authHeader);
        $jwt = $arr[1] ?? null;
        if ($jwt) {
            $decoded = $this->jwtHandler->decode($jwt);
            if ($decoded) {
                $userId = $decoded['id'];

                // Gọi model để xử lý huỷ
                $result = $this->orderModel->cancelOrderByUser($id, $userId);
                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Đã huỷ đơn hàng']);
                } else {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Không thể huỷ đơn hàng này']);
                }
                return;
            }
        }
    }

    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
}
public function update($id)
{
    header('Content-Type: application/json');
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        return;
    }

    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1] ?? null;

    if (!$jwt || !$this->jwtHandler->decode($jwt)['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền']);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $status = $data['status'] ?? '';

    $allowed = ['pending', 'processing', 'completed', 'canceled'];
    if (!in_array($status, $allowed)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Trạng thái không hợp lệ']);
        return;
    }

    $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
    if ($stmt->execute(['status' => $status, 'id' => $id])) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Cập nhật thất bại']);
    }
}

}