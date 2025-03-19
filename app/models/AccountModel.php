<?php
class AccountModel
{
    private $conn;
    private $table_name = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tài khoản theo username
    public function getAccountByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Lấy tài khoản theo email
    public function getAccountByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    public function getAccountById($id)
{
    $query = "SELECT id, username, fullname, email, phone FROM users WHERE id = :id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // Lấy tài khoản theo số điện thoại
    public function getAccountByPhone($phone)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    public function updateUserById($id, $fullname, $email, $phone)
{
    $query = "UPDATE users SET fullname = :fullname, email = :email, phone = :phone WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}


    // Lưu tài khoản mới vào database
    public function save($username, $fullname, $email, $phone, $password, $role = 'user')
    {
        $query = "INSERT INTO " . $this->table_name . " (username, fullname, password, role, email, phone) 
                  VALUES (:username, :fullname, :password, :role, :email, :phone)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu để tránh lỗi bảo mật
        $username = htmlspecialchars(strip_tags($username));
        $fullname = htmlspecialchars(strip_tags($fullname));
        $email = htmlspecialchars(strip_tags($email));
        $phone = htmlspecialchars(strip_tags($phone));

        // Gán dữ liệu vào câu lệnh SQL
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        // Thực thi câu lệnh
        if (!$stmt->execute()) {
            error_log("Lỗi khi đăng ký tài khoản: " . implode(" | ", $stmt->errorInfo()));
            return false;
        }
        return true;
    }
}
?>
