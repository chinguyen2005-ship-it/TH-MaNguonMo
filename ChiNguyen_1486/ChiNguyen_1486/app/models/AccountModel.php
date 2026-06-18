<?php
class AccountModel {
    private $conn;
    private $table_name = "account";

    public function __construct($db) {
        $this->conn = $db;
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        // Tự động kiểm tra và thêm các cột cần thiết cho Refresh Token và Brute Force Lockout
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN refresh_token VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN refresh_token_expiry DATETIME DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN login_attempts INT DEFAULT 0");
        } catch (Exception $e) {}
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN lockout_until DATETIME DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL");
        } catch (Exception $e) {}
    }

    // Tìm kiếm thông tin tài khoản bằng Username
    public function getAccountByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Tìm kiếm thông tin tài khoản bằng Email
    public function getAccountByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Đăng ký lưu tài khoản mới (Mã hóa mật khẩu bảo mật)
    // Cấu trúc INSERT truyền đúng thứ tự: username, email, fullname, password, role
    public function save($username, $email, $fullName, $password, $role = 'user') {
        if ($this->getAccountByUsername($username)) {
            return false; // Trả về false nếu username đã tồn tại
        }

        $query = "INSERT INTO " . $this->table_name . " (username, email, fullname, password, role) 
                  VALUES (:username, :email, :fullname, :password, :role)";
        
        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu đầu vào chống tấn công XSS
        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        $fullName = htmlspecialchars(strip_tags($fullName));
        $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]); // Mã hóa một chiều Bcrypt với cost 12
        $role = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fullname", $fullName);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);

        return $stmt->execute();
    }

    // Kích hoạt tài khoản qua Token Email (Bản rút gọn - luôn trả về true/false không lỗi)
    public function verifyEmail($token) {
        return false;
    }

    // Cập nhật Token ghi nhớ đăng nhập (Bản rút gọn - luôn trả về true không lỗi)
    public function updateRememberToken($username, $token) {
        return true;
    }

    // Kiểm tra Token ghi nhớ đăng nhập (Bản rút gọn - luôn trả về null không lỗi)
    public function getUserByRememberToken($username, $token) {
        return null;
    }

    // Thiết lập mã reset mật khẩu
    public function updateResetToken($email, $token, $expiry) {
        $query = "UPDATE " . $this->table_name . " 
                  SET reset_token = :token, reset_token_expiry = :expiry 
                  WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiry", $expiry);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    // Lấy user bằng Token reset mật khẩu (Bản rút gọn - luôn trả về null không lỗi)
    public function getUserByResetToken($token) {
        return null;
    }

    // Đổi mật khẩu của người dùng
    public function updatePassword($username, $hashedPassword) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    }

    // Cập nhật thông tin cá nhân (hỗ trợ avatar)
    public function updateProfile($username, $fullName, $email, $avatar = null) {
        if ($avatar !== null) {
            $query = "UPDATE " . $this->table_name . " SET fullname = :fullname, email = :email, avatar = :avatar WHERE username = :username";
        } else {
            $query = "UPDATE " . $this->table_name . " SET fullname = :fullname, email = :email WHERE username = :username";
        }
        $stmt = $this->conn->prepare($query);
        
        $fullName = htmlspecialchars(strip_tags($fullName));
        $email = htmlspecialchars(strip_tags($email));
        
        $stmt->bindParam(":fullname", $fullName);
        $stmt->bindParam(":email", $email);
        if ($avatar !== null) {
            $avatar = htmlspecialchars(strip_tags($avatar));
            $stmt->bindParam(":avatar", $avatar);
        }
        $stmt->bindParam(":username", $username);
        
        return $stmt->execute();
    }

    // Lấy tất cả người dùng (Admin)
    public function getAllUsers() {
        $query = "SELECT id, username, email, fullname, role, created_at FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Khóa / Mở khóa tài khoản (Admin) (Bản rút gọn - luôn trả về false không lỗi)
    public function toggleUserLock($username) {
        return false;
    }

    // Xóa tài khoản người dùng (Admin)
    public function deleteUser($username) {
        $query = "DELETE FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    }

    // Cập nhật Refresh Token
    public function updateRefreshToken($username, $token, $expiry) {
        $query = "UPDATE " . $this->table_name . " 
                  SET refresh_token = :refresh_token, refresh_token_expiry = :expiry 
                  WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":refresh_token", $token);
        $stmt->bindParam(":expiry", $expiry);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    }

    // Lấy thông tin tài khoản bằng Refresh Token
    public function getAccountByRefreshToken($token) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE refresh_token = :token LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Tăng số lần đăng nhập sai và đặt thời gian khóa nếu cần
    public function incrementLoginAttempts($username) {
        $account = $this->getAccountByUsername($username);
        if ($account) {
            $newAttempts = ($account->login_attempts ?? 0) + 1;
            $lockoutUntil = null;
            if ($newAttempts >= 5) {
                // Khóa trong 15 phút
                $lockoutUntil = date('Y-m-d H:i:s', time() + 15 * 60);
            }
            
            $query = "UPDATE " . $this->table_name . " 
                      SET login_attempts = :attempts, lockout_until = :lockout 
                      WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":attempts", $newAttempts, PDO::PARAM_INT);
            $stmt->bindValue(":lockout", $lockoutUntil);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            return [
                'attempts' => $newAttempts,
                'lockout_until' => $lockoutUntil
            ];
        }
        return null;
    }

    // Đăng nhập thành công, reset lại số lần đăng nhập sai
    public function resetLoginAttempts($username) {
        $query = "UPDATE " . $this->table_name . " 
                  SET login_attempts = 0, lockout_until = NULL 
                  WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    }
}