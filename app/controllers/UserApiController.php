<?php
require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/utils/JWTHandler.php';

class UserApiController
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
                $decoded = $this->jwtHandler->decode($jwt);
                if ($decoded && $decoded['role'] === 'admin') {
                    return $decoded;
                }
            }
        }
        return null;
    }

    // Lấy danh sách người dùng
    public function index()
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');
        $users = $this->accountModel->getAllAccounts();
        echo json_encode($users);
    }
    public function show($id)
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');
        $user = $this->accountModel->getAccountById($id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    } 
    // public function store()
    // {
    //     if (!$this->authenticate()) {
    //         http_response_code(401);
    //         echo json_encode(['message' => 'Unauthorized']);
    //         return;
    //     }

    //     header('Content-Type: application/json');
    //     $data = json_decode(file_get_contents("php://input"), true);
    //     $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    //     $result = $this->accountModel->createAccount($data);
    //     if ($result) {
    //         echo json_encode(['message' => 'User created successfully']);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode(['message' => 'Failed to create user']);
    //     }
    // }
    public function update($id)
    {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->accountModel->updateUserById($id, $data['fullname'], $data['email'], $data['phone']);
        if ($result) {
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update user']);
        }
    }
}