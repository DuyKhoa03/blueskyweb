<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');
require_once('app/utils/JWTHandler.php');

class ProductApiController
{
    private $productModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    // Kiểm tra xác thực bằng JWT
    private function authenticate()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? null;
            if ($jwt) {
                $decoded = $this->jwtHandler->decode($jwt);
                return $decoded ? true : false;
            }
        }
        return false;
    }

    // Lấy danh sách sản phẩm
    public function index()
{
    if ($this->authenticate()) {
        header('Content-Type: application/json');

        $categoryId = $_GET['category'] ?? null;
        $keyword = $_GET['keyword'] ?? null;

        if ($categoryId) {
            $products = $this->productModel->getProductsByCategory($categoryId);
        } elseif ($keyword !== null) {
            $products = $this->productModel->searchProducts($keyword);
        } else {
            $products = $this->productModel->getProducts();
        }

        echo json_encode($products);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
    }
}



    // Lấy thông tin sản phẩm theo ID
    public function show($id)
    {
        header('Content-Type: application/json');
        $product = $this->productModel->getProductById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Product not found']);
        }
    }

    public function store()
{
    header('Content-Type: application/json');

    // Debug log để kiểm tra dữ liệu nhận được
    error_log("Received POST: " . print_r($_POST, true));
    error_log("Received FILES: " . print_r($_FILES, true));

    // Đọc dữ liệu từ `$_POST`
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $imagePath = $_POST['image'] ?? null;
    
    // Kiểm tra danh mục có tồn tại không
    $categoryModel = new CategoryModel($this->db);
    if (!$categoryModel->getCategoryById($category_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Danh mục không hợp lệ', 'category_id' => $category_id]);
        exit();
    }

    // Xử lý upload ảnh
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $targetFilePath;
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Lỗi khi tải ảnh lên']);
            exit();
        }
    }

    // Thêm sản phẩm vào database
    $result = $this->productModel->addProduct($name, $description, $price, $category_id, $imagePath);

    if ($result) {
        http_response_code(201);
        echo json_encode(['message' => 'Sản phẩm đã được tạo thành công', 'image' => $imagePath]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Lỗi khi lưu sản phẩm']);
    }
}

public function update($id)
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && ($_POST['_method'] ?? '') !== 'PUT') {
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        return;
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode(['message' => 'Lỗi: ID sản phẩm không hợp lệ']);
        return;
    }

    // Nhận dữ liệu từ `$_POST`
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    // Lấy thông tin sản phẩm hiện tại
    $currentProduct = $this->productModel->getProductById($id);
    if (!$currentProduct) {
        http_response_code(404);
        echo json_encode(['message' => 'Sản phẩm không tồn tại']);
        return;
    }

    // Kiểm tra nếu có ảnh mới
    $imagePath = $currentProduct->image; // Giữ nguyên ảnh cũ mặc định

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $targetFilePath;

            // Xóa ảnh cũ nếu có
            if (!empty($currentProduct->image) && file_exists($currentProduct->image)) {
                unlink($currentProduct->image);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Lỗi khi tải ảnh lên']);
            return;
        }
    }

    // Cập nhật sản phẩm
    $result = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $imagePath);

    if ($result) {
        http_response_code(200);
        echo json_encode(['message' => 'Sản phẩm đã được cập nhật', 'image' => $imagePath]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Lỗi khi cập nhật sản phẩm']);
    }
}


public function destroy($id)
{
    header('Content-Type: application/json');

    error_log("Delete Request for ID: " . $id); // Debug

    // Kiểm tra sản phẩm có tồn tại không
    $product = $this->productModel->getProductById($id);
    if (!$product) {
        http_response_code(404);
        error_log("Product Not Found: " . $id);
        echo json_encode(['message' => 'Sản phẩm không tồn tại']);
        return;
    }

    // Xóa sản phẩm khỏi database
    $result = $this->productModel->deleteProduct($id);
    
    if ($result) {
        http_response_code(200); // Chắc chắn trả về 200 OK
        error_log("Product Deleted Successfully: " . $id);
        echo json_encode(['message' => 'Sản phẩm đã bị xóa']);
    } else {
        http_response_code(500); // Nếu thất bại thì trả 500
        error_log("Failed to Delete Product: " . $id);
        echo json_encode(['message' => 'Xóa sản phẩm thất bại']);
    }
}

}
?>
