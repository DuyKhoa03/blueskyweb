<?php 
 
require_once 'vendor/autoload.php'; 
use \Firebase\JWT\JWT;use \Firebase\JWT\Key; 
 
class JWTHandler 
{ 
    private $secret_key; 
 
    public function __construct() 
    { 
        $this->secret_key = "HUTECH"; // Thay thế bằng khóa bí mật của bạn 
    } 
 
    // Tạo JWT 
    public function encode($data) 
    { 
        $issuedAt = time(); 
        $expirationTime = $issuedAt + 86400;  // jwt valid for 1 hour from the issued time 
        $payload = array( 
            'iat' => $issuedAt, 
            'exp' => $expirationTime, 
            'data' => $data 
        ); 
 
        return JWT::encode($payload, $this->secret_key, 'HS256'); 
    } 
 
    // Giải mã JWT 
    // Giải mã JWT
public function decode($jwt)
{
    try {
        $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
        return (array) $decoded->data;
    } catch (\Firebase\JWT\ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token đã hết hạn, vui lòng đăng nhập lại']);
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token không hợp lệ']);
        exit();
    }
}

} 
?>