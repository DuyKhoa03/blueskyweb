<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/CartModel.php';
file_put_contents("momo_return_debug.txt", json_encode([
    'GET' => $_GET,
    'extraDataParsed' => isset($_GET['extraData']) ? urldecode($_GET['extraData']) : null
], JSON_PRETTY_PRINT));

$db = (new Database())->getConnection();
$orderModel = new OrderModel($db);
$cartModel = new CartModel($db);

$orderId = null;

// Giải mã extraData
if (isset($_GET['extraData'])) {
    $orderId = null;

if (isset($_GET['extraData'])) {
    $extraRaw = urldecode($_GET['extraData']);
    parse_str($extraRaw, $extraData);
    $orderId = $extraData['orderId'] ?? null;
}

}

$success = false;

if ($_GET['resultCode'] == 0 && $orderId) {
    // Cập nhật đơn hàng thành PAID
    $stmt = $db->prepare("UPDATE orders SET status = 'paid' WHERE id = :id");
    $stmt->execute(['id' => $orderId]);

    // Lấy user_id từ đơn hàng để xóa giỏ hàng
    $stmt2 = $db->prepare("SELECT user_id FROM orders WHERE id = :id");
    $stmt2->execute(['id' => $orderId]);
    $order = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($order && isset($order['user_id'])) {
        $cartModel->clearCart($order['user_id']);
    }

    $success = true;
}

// Chuyển về trang sản phẩm sau 2s
header("refresh:2;url=/blueskyweb/product");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán</title>
</head>
<body>
<h2 style="color:<?= ($_GET['resultCode'] ?? -1) == 0 ? 'green' : 'red' ?>">
    <?= ($_GET['resultCode'] ?? -1) == 0 ? 'Thanh toán thành công!' : 'Thanh toán thất bại hoặc bị hủy.' ?>
</h2>

    <p>Bạn sẽ được chuyển về trang sản phẩm trong giây lát...</p>
</body>
</html>
