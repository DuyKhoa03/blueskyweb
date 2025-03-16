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
            $products = $this->productModel->getProducts();
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
    $imagePath = null;

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

    // Cập nhật sản phẩm theo ID (hỗ trợ cập nhật ảnh)
    public function update($id)
    {
        header('Content-Type: application/json');

        // Kiểm tra nếu có file ảnh mới được tải lên
        $newImagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $newImagePath = $targetFilePath;
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Lỗi khi tải ảnh lên']);
                return;
            }
        }

        // Lấy dữ liệu JSON từ request
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';
        $category_id = $data['category_id'] ?? null;

        // Kiểm tra danh mục có tồn tại không
        $categoryModel = new CategoryModel($this->db);
        if (!$categoryModel->getCategoryById($category_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Danh mục không hợp lệ']);
            return;
        }

        // Cập nhật sản phẩm trong database
        $result = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $newImagePath);

        if ($result) {
            echo json_encode(['message' => 'Sản phẩm đã được cập nhật thành công']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Cập nhật sản phẩm thất bại']);
        }
    }

    // Xóa sản phẩm theo ID (xóa cả ảnh)
    public function destroy($id)
    {
        header('Content-Type: application/json');

        // Lấy thông tin sản phẩm để xóa ảnh
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['message' => 'Sản phẩm không tồn tại']);
            return;
        }

        // Xóa ảnh nếu có
        if (!empty($product->image)) {
            $imagePath = __DIR__ . "/../../" . $product->image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Xóa sản phẩm khỏi database
        $result = $this->productModel->deleteProduct($id);

        if ($result) {
            echo json_encode(['message' => 'Sản phẩm đã bị xóa']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Xóa sản phẩm thất bại']);
        }
    }
}
?>
