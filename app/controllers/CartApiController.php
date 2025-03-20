<?php
require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/utils/JWTHandler.php';

class CartApiController
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

    // Lấy giỏ hàng của user
    public function show($userId)
    {
        header('Content-Type: application/json');

        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $cart = $this->cartModel->getCartByUser($userId);
        echo json_encode($cart);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function store()
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $authUser = $this->authenticate();

        if (!$authUser) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $userId = $authUser['id'];
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$productId) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing product_id --- ' . $productId . ' --- ' . $quantity . ' --- ' . $userId]);
            return;
        }

        $this->cartModel->addToCart($userId, $productId, $quantity);
        echo json_encode(['message' => 'Added to cart']);
    }
    
    // Cập nhật giỏ hàng
    public function update($cartId)
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $quantity = $data['quantity'] ?? 1;

        if (!$cartId) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing cart ID']);
            return;
        }

        $this->cartModel->updateCart($cartId, $quantity);
        echo json_encode(['message' => 'Cart updated']);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function destroy($cartId)
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }
        header('Content-Type: application/json');
        $this->cartModel->removeFromCart($cartId);
        echo json_encode(['message' => 'Removed from cart']);
    }
}
?>
