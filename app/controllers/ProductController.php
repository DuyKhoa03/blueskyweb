<?php
class ProductController
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://localhost/blueskyweb/api/product'; // Cập nhật URL API nếu cần
    }

    // Lấy danh sách sản phẩm từ API
    public function index()
    {
        $products = $this->callApi('GET', $this->apiUrl);
        include 'app/views/product/list.php';
    }

    // Hiển thị thông tin sản phẩm
    public function show($id)
    {
        $product = $this->callApi('GET', "{$this->apiUrl}/$id");
        if ($product) {
            include 'app/views/product/show.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    // Hiển thị form thêm sản phẩm
    public function add()
    {
        $categories = $this->callApi('GET', 'http://localhost/blueskyweb/api/category');
        include 'app/views/product/add.php';
    }

    // Xử lý thêm sản phẩm mới (Gọi API)
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? '',
                'category_id' => $_POST['category_id'] ?? null
            ];

            // Xử lý upload hình ảnh
            if (!empty($_FILES['image']['name'])) {
                $data['image'] = $this->uploadImage($_FILES['image']);
            }

            $response = $this->callApi('POST', $this->apiUrl, $data);

            if ($response && isset($response->message) && $response->message === 'Product created successfully') {
                header('Location: /blueskyweb/Product');
                exit();
            } else {
                echo "Thêm sản phẩm thất bại!";
            }
        }
    }

    // Hiển thị form cập nhật sản phẩm
    public function edit($id)
    {
        $product = $this->callApi('GET', "{$this->apiUrl}/$id");
        $categories = $this->callApi('GET', 'http://localhost/blueskyweb/api/category');

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    // Xử lý cập nhật sản phẩm (Gọi API)
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'image' => $_POST['existing_image'] ?? ''
            ];

            // Xử lý upload hình ảnh mới nếu có
            if (!empty($_FILES['image']['name'])) {
                $data['image'] = $this->uploadImage($_FILES['image']);
            }

            $response = $this->callApi('PUT', "{$this->apiUrl}/$id", $data);

            if ($response && isset($response->message) && $response->message === 'Product updated successfully') {
                header('Location: /blueskyweb/Product');
                exit();
            } else {
                echo "Cập nhật sản phẩm thất bại!";
            }
        }
    }

    // Xóa sản phẩm (Gọi API)
    public function delete($id)
    {
        $response = $this->callApi('DELETE', "{$this->apiUrl}/$id");

        if ($response && isset($response->message) && $response->message === 'Product deleted successfully') {
            header('Location: /blueskyweb/Product');
            exit();
        } else {
            echo "Xóa sản phẩm thất bại!";
        }
    }

    // Hàm gọi API chung
    private function callApi($method, $url, $data = [])
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method
        ];

        if (!empty($data) && ($method === 'POST' || $method === 'PUT')) {
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    // Hàm upload hình ảnh
    private function uploadImage($file)
    {
        $target_dir = "uploads/";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra file có phải là hình ảnh không
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception("File không phải là hình ảnh.");
        }

        // Kiểm tra kích thước file (10MB)
        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hình ảnh có kích thước quá lớn.");
        }

        // Chỉ chấp nhận các định dạng JPG, JPEG, PNG, GIF
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            throw new Exception("Chỉ chấp nhận định dạng JPG, JPEG, PNG, GIF.");
        }

        // Lưu file
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Có lỗi xảy ra khi tải lên hình ảnh.");
        }

        return $target_file;
    }
}
?>
