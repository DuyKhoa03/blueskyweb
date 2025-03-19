<?php
class ProductModel
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy danh sách sản phẩm
    public function getProducts()
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN category c ON p.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy thông tin sản phẩm theo ID
    public function getProductById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Thêm sản phẩm mới
    public function addProduct($name, $description, $price, $category_id, $imagePath)
    {
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }
        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }
        if (empty($imagePath)) {
            $errors['image'] = 'Vui lòng tải lên hình ảnh sản phẩm';
        }
        if (!empty($errors)) {
            return $errors;
        }

        // SQL query
        $query = "INSERT INTO " . $this->table_name . " (name, description, price, image, category_id) 
                  VALUES (:name, :description, :price, :image, :category_id)";
        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));

        // Gán dữ liệu vào câu lệnh SQL
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':category_id', $category_id);

        return $stmt->execute();
    }

    // Cập nhật sản phẩm
    public function updateProduct($id, $name, $description, $price, $category_id, $newImagePath)
{
    // Kiểm tra sản phẩm có tồn tại không
    $currentProduct = $this->getProductById($id);
    if (!$currentProduct) {
        return false;
    }
    // SQL query cập nhật
    $query = "UPDATE " . $this->table_name . " 
              SET name = :name, description = :description, price = :price, image = :image, category_id = :category_id 
              WHERE id = :id";  // QUAN TRỌNG: Điều kiện WHERE id = ?

    $stmt = $this->conn->prepare($query);

    // Gán giá trị vào SQL
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image', $newImagePath);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
}



    // Xóa sản phẩm
    public function deleteProduct($id)
    {
        // Lấy thông tin sản phẩm trước khi xóa
        $product = $this->getProductById($id);
        if (!$product) {
            return false;
        }

        // Xóa ảnh sản phẩm nếu có
        if (!empty($product->image)) {
            $imagePath = __DIR__ . "/../../uploads/" . basename($product->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Xóa sản phẩm khỏi database
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
