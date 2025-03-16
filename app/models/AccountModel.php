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

    // Lưu tài khoản mới vào database
    public function save($username, $email, $password, $role = 'user')
{
    $query = "INSERT INTO " . $this->table_name . " (username, password, role, email) 
              VALUES (:username, :password, :role, :email)";

    $stmt = $this->conn->prepare($query);

    // Làm sạch dữ liệu để tránh lỗi bảo mật
    $username = htmlspecialchars(strip_tags($username));
    $email = htmlspecialchars(strip_tags($email));

    // Gán dữ liệu vào câu lệnh SQL
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);

    // Thực thi câu lệnh
    return $stmt->execute();
}

}
?>
