<?php
require_once('app/config/database.php');
require_once('app/models/AccountModel.php');
require_once('app/utils/JWTHandler.php');

class AccountController
{
    private $accountModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    function register()
    {
        include_once 'app/views/account/register.php';
    }

    public function login()
    {
        include_once 'app/views/account/login.php';
    }

    public function save()
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirmpassword'] ?? '';

    $errors = [];

    // Kiểm tra username
    if (empty($username)) {
        $errors[] = "Vui lòng nhập username!";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username chỉ chứa chữ, số, dấu gạch dưới (3-20 ký tự)!";
    }

    // Kiểm tra email hợp lệ
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ!";
    }

    // Kiểm tra mật khẩu
    if (empty($password)) {
        $errors[] = "Vui lòng nhập mật khẩu!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự!";
    }

    // Kiểm tra xác nhận mật khẩu
    if ($password !== $confirmPassword) {
        $errors[] = "Mật khẩu và xác nhận mật khẩu không khớp!";
    }

    // Kiểm tra username và email đã tồn tại chưa
    if ($this->accountModel->getAccountByUsername($username)) {
        $errors[] = "Tài khoản này đã tồn tại!";
    }
    if ($this->accountModel->getAccountByEmail($email)) {
        $errors[] = "Email này đã được đăng ký!";
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['message' => 'error', 'errors' => $errors]);
        exit();
    }

    // Mã hóa mật khẩu trước khi lưu vào database
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Lưu tài khoản vào database (bỏ `fullname`)
    $result = $this->accountModel->save($username, $email, $hashedPassword);

    if ($result) {
        echo json_encode(['message' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Lỗi hệ thống, vui lòng thử lại sau.']);
    }
}


    function logout()
    {
        session_start();
        session_destroy(); // Xóa toàn bộ session

        header('Location: /blueskyweb/product');
        exit();
    }

    public function checkLogin()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $loginInput = trim($data['username_or_email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Vui lòng nhập email/username và mật khẩu']);
            exit();
        }

        // Kiểm tra tài khoản theo email hoặc username
        $user = filter_var($loginInput, FILTER_VALIDATE_EMAIL)
            ? $this->accountModel->getAccountByEmail($loginInput)
            : $this->accountModel->getAccountByUsername($loginInput);

        if ($user && password_verify($password, $user->password)) {
            $token = $this->jwtHandler->encode([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Tài khoản hoặc mật khẩu không đúng']);
        }
    }
}
?>
