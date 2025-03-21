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

    // Hiển thị thông tin sản phẩm nguyenkhanh changes
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
                try {
                    $data['image'] = new CURLFile($this->uploadImage($_FILES['image']));
                } catch (Exception $e) {
                    echo "Lỗi upload ảnh: " . $e->getMessage();
                    return;
                }
            }

            $response = $this->callApi('POST', $this->apiUrl, $data, true);

            if ($response && isset($response->message) && $response->message === 'Sản phẩm đã được tạo thành công') {
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
    $jwtToken = $_SESSION['jwtToken'] ?? '';
    $categories = $this->callApi('GET', 'http://localhost/blueskyweb/api/category', $jwtToken);

    if ($product) {
        $editId = $id; // Gán giá trị cho $editId
        include 'app/views/product/edit.php';
    } else {
        echo "Không thấy sản phẩm.";
    }
}


    // Xử lý cập nhật sản phẩm (Gọi API)
    public function update()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = $_POST['id'] ?? null;
        // Debug: Kiểm tra ID có được gửi đi không
        error_log("ID gửi đi từ ProductController: " . $id);

        if (!$id) {
            echo "Lỗi: Không tìm thấy ID sản phẩm!";
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? '',
            'category_id' => $_POST['category_id'] ?? null,
            'image' => $_POST['current_image'] ?? '' // Giữ ảnh cũ nếu không có ảnh mới
        ];

        // Xử lý upload ảnh nếu có
        if (!empty($_FILES['image']['name'])) {
            try {
                $data['image'] = new CURLFile($this->uploadImage($_FILES['image']));
            } catch (Exception $e) {
                echo "Lỗi upload ảnh: " . $e->getMessage();
                return;
            }
        }

        // Gọi API cập nhật
        $response = $this->callApi('POST', "{$this->apiUrl}/$id", $data, true);

        if ($response && isset($response->message) && $response->message === 'Sản phẩm đã được cập nhật') {
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

        if ($response && isset($response->message) && $response->message === 'Sản phẩm đã bị xóa') {
            header('Location: /blueskyweb/Product');
            exit();
        } else {
            echo "Xóa sản phẩm thất bại!";
        }
    }

    // Hàm gọi API chung
    private function callApi($method, $url, $data = [], $isMultipart = false)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method
        ];

        if (!empty($data) && ($method === 'POST' || $method === 'PUT')) {
            if ($isMultipart) {
                $options[CURLOPT_HTTPHEADER] = ['Content-Type: multipart/form-data'];
                $options[CURLOPT_POSTFIELDS] = $data;
            } else {
                $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    // Hàm upload hình ảnh
    private function uploadImage($file)
    {
        $target_dir = __DIR__ . "/../../uploads/";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $fileName = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $fileName;
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
