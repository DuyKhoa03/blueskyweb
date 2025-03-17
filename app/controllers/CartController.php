<?php
class CartController
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://localhost/blueskyweb/api/cart';
    }

    // Hiển thị giỏ hàng
    public function index()
    {
        $cart = $this->callApi('GET', "{$this->apiUrl}/{$_SESSION['user_id']}");
        include 'app/views/cart/list.php';
    }

    // Gọi API để thêm sản phẩm vào giỏ
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'product_id' => $_POST['product_id'],
                'quantity' => $_POST['quantity'] ?? 1
            ];

            $this->callApi('POST', "{$this->apiUrl}/add", $data);
            header('Location: /blueskyweb/Cart');
            exit();
        }
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($cartId)
    {
        $this->callApi('DELETE', "{$this->apiUrl}/remove/$cartId");
        header('Location: /blueskyweb/Cart');
        exit();
    }

    // Hàm gọi API chung
    private function callApi($method, $url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
}
?>
