<?php
class CartModel
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Thêm sản phẩm vào giỏ hàng (nếu có rồi thì tăng số lượng)
    public function addToCart($user_id, $product_id, $quantity)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Nếu sản phẩm đã có, tăng số lượng
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Nếu chưa có, thêm mới
            $stmt = $this->conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            return $stmt->execute([$user_id, $product_id, $quantity]);
        }
    }

    // Lấy danh sách sản phẩm trong giỏ hàng
    public function getCartItems($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT cart.id, cart.quantity, product.name, product.price, product.image 
            FROM cart 
            JOIN product ON cart.product_id = product.id 
            WHERE cart.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cập nhật số lượng sản phẩm
    public function updateCartItem($cart_id, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $cart_id]);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeCartItem($cart_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE id = ?");
        return $stmt->execute([$cart_id]);
    }

    // Xóa toàn bộ giỏ hàng (sau khi đặt hàng)
    public function clearCart($user_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }
}
?>
