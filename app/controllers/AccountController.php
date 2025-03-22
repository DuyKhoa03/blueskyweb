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
    // Xác thực JWT
    private function authenticate()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1] ?? null;
            if ($jwt) {
                return $this->jwtHandler->decode($jwt);
            }
        }
        return null;
    }
    function register()
    {
        include_once 'app/views/account/register.php';
    }

    public function login()
    {
        include_once 'app/views/account/login.php';
    }
    public function profile()
    {
        include_once 'app/views/account/profile.php';
    }
    public function getUserById()
{
    header('Content-Type: application/json; charset=UTF-8');

    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        echo json_encode(['error' => 'Không có token, vui lòng đăng nhập!']);
        http_response_code(401);
        exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $tokenData = $this->jwtHandler->decode($token);
    $userId = $tokenData['id'];

    $user = $this->accountModel->getAccountById($userId);

    if ($user) {
        echo json_encode(['status' => 'success', 'user' => $user], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Không tìm thấy user']);
        http_response_code(404);
    }
    exit();
}

public function updateUser()
{

    header('Content-Type: application/json; charset=UTF-8');
    if (!$this->authenticate()) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
        return;
    }
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        echo json_encode(['error' => 'Không có token, vui lòng đăng nhập!']);
        http_response_code(401);
        exit();
    }

    // Lấy và giải mã token
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $tokenData = $this->jwtHandler->decode($token);
    $userId = $tokenData['id'];
    $userRole = $tokenData['role']; // Lấy role của người dùng (user hoặc admin)

    // Kiểm tra quyền: chỉ cho phép cập nhật thông tin của user nếu user là chính họ hoặc là admin
    if ($userRole !== 'admin' && $userId !== $userId) {
        http_response_code(403);
        echo json_encode(['message' => 'Forbidden: Bạn không có quyền cập nhật thông tin của người khác.']);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    
    $result = $this->accountModel->updateUserById($userId, $fullname, $email, $phone);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['error' => 'Cập nhật thất bại']);
        http_response_code(500);
    }
    exit();
}


    function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $fullName = trim($data['fullname'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirmpassword'] ?? '';
            $errors = [];

            // Kiểm tra username
            if (empty($username)) {
                $errors['username'] = "Vui lòng nhập username!";
            } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                $errors['username'] = "Username chỉ chứa chữ, số, dấu gạch dưới (3-20 ký tự)!";
            }

            // Kiểm tra email hợp lệ
            if (empty($email)) {
                $errors['email'] = "Vui lòng nhập email!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email không hợp lệ!";
            }
            // Kiểm tra số điện thoại
            if (empty($phone)) {
                $errors['phone'] = "Vui lòng nhập số điện thoại!";
            } elseif (!preg_match('/^0[0-9]{9}$/', $phone)) {
                $errors['phone'] = "Số điện thoại không hợp lệ!";
            }
            // Kiểm tra tên đầy đủ
            if (empty($fullName)) {
                $errors['fullname'] = "Vui lòng nhập họ và tên!";
            }

            // Kiểm tra mật khẩu
            if (empty($password)) {
                $errors['password'] = "Vui lòng nhập mật khẩu!";
            } elseif (strlen($password) < 6) {
                $errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự!";
            }

            // Kiểm tra xác nhận mật khẩu
            if ($password !== $confirmPassword) {
                $errors['confirmPass'] = "Mật khẩu và xác nhận mật khẩu không khớp!";
            }

            // Kiểm tra username, phone và email đã tồn tại chưa
            $accountByUsername = $this->accountModel->getAccountByUsername($username);
            $accountByEmail = $this->accountModel->getAccountByEmail($email);

            if ($accountByUsername) {
                $errors['username'] = "Tài khoản này đã tồn tại!";
            }
            if ($accountByEmail) {
                $errors['email'] = "Email này đã được đăng ký!";
            }
            if ($this->accountModel->getAccountByPhone($phone)) {
                $errors['phone'] = "Số điện thoại này đã được đăng ký!";
            }
            
            if (!empty($errors)) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['status' => 'error', 'errors' => $errors], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                // Mã hóa mật khẩu trước khi lưu vào database
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                // Lưu tài khoản vào database
                $result = $this->accountModel->save($username, $fullName, $email, $phone, $hashedPassword);

                if ($result) {
                    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['message' => 'success'], JSON_UNESCAPED_UNICODE);
    exit();
                }
            }
        }
    }

    public function logout()
    {
        unset($_SESSION['jwtToken']);
        session_destroy();
        // Xóa cookie
        setcookie('jwtToken', '', time() - 3600, '/'); // Đặt thời gian sống về quá khứ để xóa cookie
        header('Location: /blueskyweb/account/login');
        exit();
    }

    public function checkLogin()
{
    header('Content-Type: application/json');
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
            'email' => $user->email,
            'role' => $user->role
        ]);

        // Khởi động session nếu chưa khởi động
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Lưu token vào session
        $_SESSION['jwtToken'] = $token;
        // Lưu token vào cookie với thời gian sống 7 ngày (604800 giây)
        setcookie('jwtToken', $token, time() + 604800, '/', '', false, true); // httponly = true để tăng bảo mật
        // Trả về phản hồi thành công
        echo json_encode(['message' => 'Đăng nhập thành công']);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Tài khoản hoặc mật khẩu không đúng']);
    }
}
}
?>
