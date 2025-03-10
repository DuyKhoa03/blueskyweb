<?php
class CategoryController
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://localhost/blueskyweb/api/category'; // Đổi URL nếu cần
    }

    // Lấy danh sách danh mục từ API
    public function index()
    {
        $categories = $this->callApi('GET', $this->apiUrl);
        include 'app/views/category/index.php';
    }

    // Hiển thị form thêm danh mục
    public function add()
    {
        include 'app/views/category/add.php';
    }

    // Xử lý thêm danh mục mới (Gọi API)
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            $response = $this->callApi('POST', $this->apiUrl, $data);
            
            if ($response && isset($response->message) && $response->message === 'Category created successfully') {
                header('Location: /blueskyweb/Category');
                exit();
            } else {
                echo "Thêm danh mục thất bại!";
            }
        }
    }

    // Hiển thị form chỉnh sửa danh mục
    public function edit($id)
{
    $category = $this->callApi('GET', "http://localhost/blueskyweb/api/category/$id");

    if (!$category) {
        die("Danh mục không tồn tại!");
    }

    include 'app/views/category/edit.php';
}


    // Xử lý cập nhật danh mục (Gọi API)
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            $response = $this->callApi('PUT', "{$this->apiUrl}/$id", $data);
            
            if ($response && isset($response->message) && $response->message === 'Category updated successfully') {
                header('Location: /blueskyweb/Category');
                exit();
            } else {
                echo "Cập nhật danh mục thất bại!";
            }
        }
    }

    // Xử lý xóa danh mục (Gọi API)
    public function delete($id)
    {
        $response = $this->callApi('DELETE', "{$this->apiUrl}/$id");
        
        if ($response && isset($response->message) && $response->message === 'Category deleted successfully') {
            header('Location: /blueskyweb/Category');
            exit();
        } else {
            echo "Xóa danh mục thất bại!";
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
}
