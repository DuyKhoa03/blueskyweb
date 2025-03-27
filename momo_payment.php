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
$notifyUrl = "http://localhost/blueskyweb/momo_notify.php"; // Chỉ cần tồn tại là được

// Lấy dữ liệu từ form checkout gửi qua
$address = $_POST['address'] ?? '';
$totalAmount = $_POST['totalCartPrice'] ?? 0;

// Lấy thông tin user từ JWT
$jwt = $_SESSION['jwtToken'] ?? '';
$jwtHandler = new JWTHandler();
$userData = $jwtHandler->decode($jwt);
$userId = $userData['id'] ?? null;

if (!$userId || !$totalAmount || !$address) {
    die("Thiếu thông tin thanh toán!");
}

// Tạo orderId & requestId
$orderId = time() . "";
$requestId = time() . "";

// Tạo extraData
$extraDataArr = [
    'userId' => $userId,
    'address' => $address,
    'total' => $totalAmount
];
$extraData = urlencode(http_build_query($extraDataArr));


// ⚠️ Tạo raw string để ký – đúng thứ tự MoMo yêu cầu
$rawHash = "accessKey=$accessKey"
    . "&amount=$totalAmount"
    . "&extraData=$extraData"
    . "&ipnUrl=$notifyUrl"
    . "&orderId=$orderId"
    . "&orderInfo=$orderInfo"
    . "&partnerCode=$partnerCode"
    . "&redirectUrl=$returnUrl"
    . "&requestId=$requestId"
    . "&requestType=captureWallet";

// Ký dữ liệu
$signature = hash_hmac("sha256", $rawHash, $secretKey);

// Dữ liệu gửi đến MoMo
$rawData = [
    'partnerCode' => $partnerCode,
    'accessKey' => $accessKey,
    'requestId' => $requestId,
    'amount' => $totalAmount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $returnUrl,
    'ipnUrl' => $notifyUrl,
    'extraData' => $extraData, // CHÍNH LÀ BẢN encode
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

// Redirect tới trang MoMo nếu thành công
if (isset($response['payUrl'])) {
    header('Location: ' . $response['payUrl']);
    exit();
} else {
    echo "Không thể kết nối MoMo. Vui lòng thử lại.";
    echo "<pre>";
    print_r($response);
}
