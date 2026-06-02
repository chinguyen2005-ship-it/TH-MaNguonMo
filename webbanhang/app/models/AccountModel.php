<?php
class AccountModel {
    private $conn;
    private $table_name = "account";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tìm kiếm thông tin tài khoản bằng Username
    public function getAccountByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Đăng ký lưu tài khoản mới (Mã hóa mật khẩu bảo mật)
    public function save($username, $fullName, $password, $role = 'user') {
        if ($this->getAccountByUsername($username)) {
            return false; // Trả về false nếu username đã tồn tại
        }

        $query = "INSERT INTO " . $this->table_name . " (username, fullname, password, role) VALUES (:username, :fullname, :password, :role)";
        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu đầu vào chống tấn công XSS
        $username = htmlspecialchars(strip_tags($username));
        $fullName = htmlspecialchars(strip_tags($fullName));
        $password = password_hash($password, PASSWORD_BCRYPT); // Mã hóa một chiều Bcrypt
        $role = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullName);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);

        return $stmt->execute();
    }
}