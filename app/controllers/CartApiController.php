<?php
require_once 'app/models/CartModel.php';
require_once 'app/utils/JWTHandler.php';

class CartApiController
{
    private $cartModel;
    private $jwtHandler;

    public function __construct()
    {
        $this->cartModel = new CartModel((new Database())->getConnection());
        $this->jwtHandler = new JWTHandler();
    }

    // Kiểm tra xác thực người dùng bằng JWT
    private function authenticate()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? null;
            if ($jwt) {
                $decoded = $this->jwtHandler->decode($jwt);
                return $decoded ? $decoded['id'] : null;
            }
        }
        return null;
    }

    // Lấy giỏ hàng của user
    public function index()
    {
        header('Content-Type: application/json');
        $user_id = $this->authenticate();
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $cartItems = $this->cartModel->getCartItems($user_id);
        echo json_encode($cartItems);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function store()
    {
        header('Content-Type: application/json');
        $user_id = $this->authenticate();
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['message' => 'Thiếu product_id']);
            return;
        }

        if ($this->cartModel->addToCart($user_id, $product_id, $quantity)) {
            http_response_code(201);
            echo json_encode(['message' => 'Đã thêm vào giỏ hàng']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi khi thêm vào giỏ hàng']);
        }
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function update($id)
    {
        header('Content-Type: application/json');
        $user_id = $this->authenticate();
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $quantity = $data['quantity'] ?? null;

        if (!$quantity || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Số lượng không hợp lệ']);
            return;
        }

        if ($this->cartModel->updateCartItem($id, $quantity)) {
            http_response_code(200);
            echo json_encode(['message' => 'Cập nhật thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi khi cập nhật']);
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function destroy($id)
    {
        header('Content-Type: application/json');
        $user_id = $this->authenticate();
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        if ($this->cartModel->removeCartItem($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi khi xóa sản phẩm']);
        }
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        header('Content-Type: application/json');
        $user_id = $this->authenticate();
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        if ($this->cartModel->clearCart($user_id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Đã xóa toàn bộ giỏ hàng']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Lỗi khi xóa giỏ hàng']);
        }
    }
}
?>
