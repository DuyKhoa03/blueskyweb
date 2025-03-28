<?php
session_start();
require_once 'app/utils/JWTHandler.php';

// Thông tin MoMo Sandbox
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = "MOMO";
$accessKey = "F8BBA842ECF85";
$secretKey = "K951B6PE1waDMi640xX08PD3vg6EkVlz";
$orderInfo = "Thanh toán đơn hàng tại BlueSkyWeb";
$returnUrl = "http://localhost/blueskyweb/momo_return.php";
$notifyUrl = "http://localhost/blueskyweb/momo_notify.php"; // chỉ cần tồn tại

// Nhận dữ liệu từ form checkout
$address = $_POST['address'] ?? '';
$totalAmount = $_POST['totalCartPrice'] ?? 0;
$originalOrderId = $_POST['orderId'] ?? null;

// Lấy user từ session JWT
$jwt = $_SESSION['jwtToken'] ?? '';
$jwtHandler = new JWTHandler();
$userData = $jwtHandler->decode($jwt);
$userId = $userData['id'] ?? null;

if (!$userId || !$totalAmount || !$address || !$originalOrderId) {
    die("Thiếu thông tin thanh toán!");
}

// Tạo orderId mới gửi MoMo (unique)
$momoOrderId = "ORDER_" . $originalOrderId . "_" . time();
$requestId = time() . "";

// Extra data (dùng để xác nhận lại sau thanh toán)
$extraDataArr = [
    'orderId' => $originalOrderId
];
$extraData = urlencode(http_build_query($extraDataArr));

// Tạo chuỗi ký (raw hash)
$rawHash = "accessKey=$accessKey"
    . "&amount=$totalAmount"
    . "&extraData=$extraData"
    . "&ipnUrl=$notifyUrl"
    . "&orderId=$momoOrderId"
    . "&orderInfo=$orderInfo"
    . "&partnerCode=$partnerCode"
    . "&redirectUrl=$returnUrl"
    . "&requestId=$requestId"
    . "&requestType=captureWallet";

// Tạo chữ ký
$signature = hash_hmac("sha256", $rawHash, $secretKey);

// Dữ liệu gửi tới MoMo
$rawData = [
    'partnerCode' => $partnerCode,
    'accessKey' => $accessKey,
    'requestId' => $requestId,
    'amount' => $totalAmount,
    'orderId' => $momoOrderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $returnUrl,
    'ipnUrl' => $notifyUrl,
    'extraData' => $extraData,
    'requestType' => 'captureWallet',
    'signature' => $signature
];

// Gửi request
$data_string = json_encode($rawData);
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);

// Redirect nếu thành công
if (isset($response['payUrl'])) {
    header('Location: ' . $response['payUrl']);
    exit();
} else {
    echo "Không thể kết nối MoMo. Vui lòng thử lại.";
    echo "<pre>";
    print_r($response);
}
