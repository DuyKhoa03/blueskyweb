<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/AccountModel.php';

$db = (new Database())->getConnection();
$orderModel = new OrderModel($db);
$cartModel = new CartModel($db);
$accountModel = new AccountModel($db);

if (isset($_GET['extraData'])) {
    parse_str($_GET['extraData'], $extraData);

    $userId = $extraData['userId'] ?? null;
    $address = $extraData['address'] ?? '';
    $total = $extraData['total'] ?? 0;

    if ($userId) {
        $user = $accountModel->getAccountById($userId);
        $cartItems = $cartModel->getCartByUser($userId);

        // Nếu thanh toán thành công
        if ($_GET['resultCode'] == 0 && !empty($cartItems)) {
            $orderId = $orderModel->createOrder($userId, $user['phone'], $address, $total);

            foreach ($cartItems as $item) {
                $orderModel->addOrderDetails($orderId, $item->product_id, $item->quantity, $item->price);
            }
        }

        // Dù thành công hay thất bại, vẫn clear giỏ
        $cartModel->clearCart($userId);
    }
}

// Chuyển về trang sản phẩm sau 2 giây
header("refresh:2;url=/blueskyweb/product");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán</title>
</head>
<body>
    <h2 style="color:<?= $_GET['resultCode'] == 0 ? 'green' : 'red' ?>">
        <?= $_GET['resultCode'] == 0 ? 'Thanh toán thành công!' : 'Thanh toán thất bại hoặc bị hủy.' ?>
    </h2>
    <p>Bạn sẽ được chuyển về trang sản phẩm trong giây lát...</p>
</body>
</html>
