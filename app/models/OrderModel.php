<?php
require_once 'app/config/database.php';

class OrderModel
{
    private $conn;
    private $orderTable = "orders";
    private $orderDetailsTable = "order_details";

    public function __construct($db)
    {
        $this->conn = $db;
    }
    // Lấy tất cả đơn hàng, bao gồm tên người dùng và trạng thái
    public function getAllOrders()
    {
        $query = "SELECT o.*, a.fullname AS user_name 
                  FROM " . $this->orderTable . " o 
                  LEFT JOIN users a ON o.user_id = a.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function createOrder($userId, $phone, $address, $total)
    {
        $query = "INSERT INTO " . $this->orderTable . " (user_id, phone, address, created_at, total_amount) 
                  VALUES (:user_id, :phone, :address, NOW(), :total)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $userId, 'phone' => $phone, 'address' => $address, 'total' => $total]);
        return $this->conn->lastInsertId();
    }

    public function addOrderDetails($orderId, $productId, $quantity, $price)
    {
        $query = "INSERT INTO " . $this->orderDetailsTable . " (order_id, product_id, quantity, price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['order_id' => $orderId, 'product_id' => $productId, 'quantity' => $quantity, 'price' => $price]);
    }
}
?>