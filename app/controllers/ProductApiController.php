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

public function update($id)
{
    header('Content-Type: application/json');

    // Kiểm tra nếu request không phải là PUT
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        exit();
    }

    // Lấy dữ liệu từ `$_POST`
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $newImagePath = null;

    // Debug dữ liệu nhận được
    error_log("Updating product ID: $id, Name: $name, Description: $description, Price: $price, Category: $category_id");
    echo "<script>console.log('Updating product ID: $id, Name: $name, Description: $description, Price: $price, Category: $category_id');</script>";

    // Kiểm tra `category_id` có hợp lệ không
    if (empty($category_id) || !is_numeric($category_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Danh mục không hợp lệ']);
        exit();
    }

    // Kiểm tra sản phẩm có tồn tại không
    $product = $this->productModel->getProductById($id);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['message' => 'Sản phẩm không tồn tại']);
        return;
    }

    // Xử lý upload ảnh mới nếu có
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/";

        // Kiểm tra và tạo thư mục nếu chưa có
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $newImagePath = $targetFilePath;

            // Xóa ảnh cũ nếu có
            if (!empty($product->image) && file_exists($product->image)) {
                unlink($product->image);
            }

            error_log("New image uploaded: " . $newImagePath);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Lỗi khi tải ảnh lên']);
            exit();
        }
    }

    // Cập nhật sản phẩm (truyền thêm `$newImagePath` nếu có ảnh mới)
    $result = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $newImagePath);

    if ($result) {
        http_response_code(200);
        echo json_encode(['message' => 'Sản phẩm đã được cập nhật thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Cập nhật sản phẩm thất bại']);
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
