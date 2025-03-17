<?php
class CartController
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://localhost/blueskyweb/api/cart'; // Địa chỉ API giỏ hàng
    }

    // 📌 Lấy danh sách sản phẩm trong giỏ hàng (gọi API)
    public function index()
    {
        session_start();
        if (!isset($_SESSION['token'])) {
            echo "Bạn cần đăng nhập để xem giỏ hàng.";
            return;
        }

        $cartItems = $this->callApi('GET', $this->apiUrl, $_SESSION['token']);
        include 'app/views/cart/index.php';
    }

    // 📌 Thêm sản phẩm vào giỏ hàng
    public function add()
    {
        session_start();
        if (!isset($_SESSION['token'])) {
            echo "Bạn cần đăng nhập để thêm sản phẩm.";
            return;
        }

        $data = [
            'product_id' => $_POST['product_id'] ?? null,
            'quantity' => $_POST['quantity'] ?? 1
        ];

        $response = $this->callApi('POST', $this->apiUrl, $_SESSION['token'], $data);
        
        if ($response && isset($response['message'])) {
            header("Location: /cart");
        } else {
            echo "Lỗi khi thêm vào giỏ hàng.";
        }
    }

    // 📌 Cập nhật số lượng sản phẩm
    public function update()
    {
        session_start();
        if (!isset($_SESSION['token'])) {
            echo "Bạn cần đăng nhập để cập nhật giỏ hàng.";
            return;
        }

        $cart_id = $_POST['cart_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;

        $response = $this->callApi('PUT', "{$this->apiUrl}/$cart_id", $_SESSION['token'], ['quantity' => $quantity]);

        if ($response && isset($response['message'])) {
            header("Location: /cart");
        } else {
            echo "Lỗi khi cập nhật giỏ hàng.";
        }
    }

    // 📌 Xóa sản phẩm khỏi giỏ hàng
    public function delete($id)
    {
        session_start();
        if (!isset($_SESSION['token'])) {
            echo "Bạn cần đăng nhập để xóa sản phẩm.";
            return;
        }

        $response = $this->callApi('DELETE', "{$this->apiUrl}/$id", $_SESSION['token']);

        if ($response && isset($response['message'])) {
            header("Location: /cart");
        } else {
            echo "Lỗi khi xóa sản phẩm.";
        }
    }

    // 📌 Xóa toàn bộ giỏ hàng
    public function clear()
    {
        session_start();
        if (!isset($_SESSION['token'])) {
            echo "Bạn cần đăng nhập để xóa giỏ hàng.";
            return;
        }

        $response = $this->callApi('DELETE', $this->apiUrl, $_SESSION['token']);

        if ($response && isset($response['message'])) {
            header("Location: /cart");
        } else {
            echo "Lỗi khi xóa giỏ hàng.";
        }
    }

    // 📌 Gọi API chung với cURL
    private function callApi($method, $url, $token, $data = [])
    {
        $ch = curl_init();
        $headers = [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
?>
