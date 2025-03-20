<?php
require_once 'app/config/database.php';

class CartModel
{
    private $conn;
    private $table_name = "cart";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addToCart($userId, $productId, $quantity)
{
    // Kiểm tra xem sản phẩm đã có trong giỏ chưa
    $query = "SELECT id, quantity FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Nếu đã tồn tại, cập nhật số lượng
        $newQuantity = $cartItem['quantity'] + $quantity;
        $updateQuery = "UPDATE " . $this->table_name . " SET quantity = :quantity WHERE id = :cart_id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute(['quantity' => $newQuantity, 'cart_id' => $cartItem['id']]);
    } else {
        // Nếu chưa có, thêm mới
        $insertQuery = "INSERT INTO " . $this->table_name . " (user_id, product_id, quantity, created_at) 
                        VALUES (:user_id, :product_id, :quantity, NOW())";
        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->execute(['user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity]);
    }

    return true;
}

    // Lấy giỏ hàng của user
    public function getCartByUser($userId)
    {
        $query = "SELECT c.id, c.product_id, p.name, p.price, p.image, c.quantity, (p.price * c.quantity) as total_price
                  FROM " . $this->table_name . " c
                  JOIN product p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function updateCart($cartId, $quantity)
    {
        $query = "UPDATE " . $this->table_name . " SET quantity = :quantity WHERE id = :cart_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['cart_id' => $cartId, 'quantity' => $quantity]);
    }

    // Xóa sản phẩm khỏi giỏ
    public function removeFromCart($cartId)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :cart_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['cart_id' => $cartId]);
    }

    // Xóa toàn bộ giỏ hàng của user
    public function clearCart($userId)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['user_id' => $userId]);
    }
}
?>
