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
    public function getOrdersByUserId($userId)
{
    $query = "SELECT o.*, a.fullname AS user_name 
              FROM " . $this->orderTable . " o 
              LEFT JOIN users a ON o.user_id = a.id
              WHERE o.user_id = :user_id
              ORDER BY o.created_at DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['user_id' => $userId]);
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
    public function cancelOrderByUser($orderId, $userId)
{
    // Kiểm tra xem đơn có tồn tại và thuộc về user không + đang pending
    $query = "SELECT * FROM orders WHERE id = :id AND user_id = :user_id AND status = 'pending'";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $update = $this->conn->prepare("UPDATE orders SET status = 'canceled' WHERE id = :id");
        return $update->execute(['id' => $orderId]);
    }

    return false;
}
public function getOrderDetailsById($orderId, $userId = null)
{
    $query = "SELECT o.*, od.product_id, od.quantity, od.price, 
                 p.name AS product_name, p.image,
                 u.fullname, u.email, u.phone AS user_phone
          FROM orders o
          JOIN order_details od ON o.id = od.order_id
          JOIN product p ON od.product_id = p.id
          JOIN users u ON o.user_id = u.id
          WHERE o.id = :order_id";


    if ($userId !== null) {
        $query .= " AND o.user_id = :user_id";
    }

    $stmt = $this->conn->prepare($query);

    $params = ['order_id' => $orderId];
    if ($userId !== null) $params['user_id'] = $userId;

    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>