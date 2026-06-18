<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/AccountModel.php');
require_once(BASE_PATH . '/app/helpers/JWTHelper.php');

class AccountApiController
{
    private $accountModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    // POST /api/account/login
    public function login()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Đọc dữ liệu JSON payload gửi kèm
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            // Hỗ trợ thêm trường hợp gửi bằng POST thông thường (form-data hoặc x-www-form-urlencoded)
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
        } else {
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
        }

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false, 
                "message" => "Vui lòng nhập đầy đủ tên đăng nhập (hoặc email) và mật khẩu."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Tìm kiếm tài khoản theo username hoặc email
        $account = $this->accountModel->getAccountByUsername($username);
        if (!$account) {
            $account = $this->accountModel->getAccountByEmail($username);
        }

        if (!$account) {
            http_response_code(401);
            echo json_encode([
                "status" => false, 
                "message" => "Tên đăng nhập hoặc mật khẩu không chính xác."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra xem tài khoản có đang bị khóa lockout hay không
        if ($account->lockout_until && strtotime($account->lockout_until) > time()) {
            $remaining = strtotime($account->lockout_until) - time();
            $minutes = ceil($remaining / 60);
            http_response_code(403);
            echo json_encode([
                "status" => false,
                "message" => "Tài khoản đang bị khóa tạm thời do nhập sai quá 5 lần. Vui lòng quay lại sau {$minutes} phút."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra mật khẩu
        if (password_verify($password, $account->password)) {
            // Reset lại số lần đăng nhập sai
            $this->accountModel->resetLoginAttempts($account->username);

            // Thiết lập Session phục vụ hiển thị trên giao diện Web MVC
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $account->id;
            $_SESSION['username'] = $account->username;
            $_SESSION['fullname'] = $account->fullname;
            $_SESSION['email'] = $account->email;
            $_SESSION['role'] = $account->role;
            $_SESSION['user_role'] = $account->role;

            // Sinh mã token JWT
            $token = JWTHelper::generateToken($account);
            $_SESSION['token'] = $token;

            // Tạo và lưu Refresh Token (hiệu lực 7 ngày)
            $refreshToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 7 * 24 * 3600);
            $this->accountModel->updateRefreshToken($account->username, $refreshToken, $expiry);

            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Login successful",
                "token" => $token,
                "refresh_token" => $refreshToken
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Đăng nhập thất bại -> tăng số lần thử
            $lockoutData = $this->accountModel->incrementLoginAttempts($account->username);
            $attempts = $lockoutData['attempts'] ?? 0;
            
            if ($attempts >= 5) {
                http_response_code(403);
                echo json_encode([
                    "status" => false, 
                    "message" => "Tài khoản đã bị khóa tạm thời 15 phút do nhập sai mật khẩu quá 5 lần."
                ], JSON_UNESCAPED_UNICODE);
            } else {
                $remainingAttempts = 5 - $attempts;
                http_response_code(401);
                echo json_encode([
                    "status" => false, 
                    "message" => "Tên đăng nhập hoặc mật khẩu không chính xác. Bạn còn {$remainingAttempts} lần thử trước khi tài khoản bị khóa."
                ], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    // POST /api/account/refresh
    public function refresh()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Đọc dữ liệu JSON payload gửi kèm
        $input = json_decode(file_get_contents('php://input'), true);
        $refreshToken = trim($input['refresh_token'] ?? $_POST['refresh_token'] ?? '');

        if (empty($refreshToken)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Thiếu mã Refresh Token."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Tìm kiếm tài khoản bằng Refresh Token
        $account = $this->accountModel->getAccountByRefreshToken($refreshToken);

        if (!$account || empty($account->refresh_token_expiry) || strtotime($account->refresh_token_expiry) < time()) {
            http_response_code(401);
            echo json_encode([
                "status" => false,
                "message" => "Refresh Token không hợp lệ hoặc đã hết hạn sử dụng."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Tạo Access Token mới
        $newAccessToken = JWTHelper::generateToken($account);

        // Sinh Refresh Token mới (tăng tính bảo mật bằng cơ chế xoay vòng Refresh Token)
        $newRefreshToken = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 7 * 24 * 3600);
        $this->accountModel->updateRefreshToken($account->username, $newRefreshToken, $expiry);

        http_response_code(200);
        echo json_encode([
            "status" => true,
            "token" => $newAccessToken,
            "refresh_token" => $newRefreshToken
        ], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/account/register
    public function register()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Đọc dữ liệu JSON payload gửi kèm
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $fullname = trim($input['fullname'] ?? '');
        $password = $input['password'] ?? '';

        // Validate các trường không được bỏ trống
        if (empty($username) || empty($email) || empty($fullname) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Tất cả các trường username, email, fullname, password không được để trống."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate đúng định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Email không đúng định dạng."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra trùng lặp username
        if ($this->accountModel->getAccountByUsername($username)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Tên đăng nhập (username) đã tồn tại trên hệ thống."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra trùng lặp email
        if ($this->accountModel->getAccountByEmail($email)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Địa chỉ email đã được đăng ký bởi tài khoản khác."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Gọi model lưu tài khoản mới (bên trong Model tự động băm mật khẩu với cost = 12)
        $success = $this->accountModel->save($username, $email, $fullname, $password, 'user');

        if ($success) {
            http_response_code(201);
            echo json_encode([
                "status" => true,
                "message" => "Đăng ký tài khoản thành công."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi hệ thống! Không thể tạo tài khoản vào lúc này."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // POST /api/account/login-api (Alias cho login)
    public function apiLogin()
    {
        return $this->login();
    }

    // Mapping cho GET / PUT /api/account/profile
    public function profile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            return $this->updateProfile();
        }
        return $this->getProfile();
    }

    // GET /api/account/profile
    public function getProfile()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Xác thực quyền: giải mã JWT Token gửi kèm trong Header
        $userData = AuthMiddleware::isAuthenticated();
        $username = $userData['username'] ?? '';

        $account = $this->accountModel->getAccountByUsername($username);
        if (!$account) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Tài khoản không tồn tại hoặc đã bị xóa."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Trả về thông tin cá nhân của người dùng hiện tại
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "data" => [
                "fullname" => $account->fullname,
                "email" => $account->email,
                "role" => $account->role,
                "created_at" => $account->created_at
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // PUT /api/account/profile
    public function updateProfile()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Xác thực quyền
        $userData = AuthMiddleware::isAuthenticated();
        $username = $userData['username'] ?? '';

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $fullname = trim($input['fullname'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($fullname) || empty($email)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Họ tên và Email không được để trống."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Định dạng email không hợp lệ."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra xem email cập nhật có bị trùng lặp với người khác không
        $existingEmail = $this->accountModel->getAccountByEmail($email);
        if ($existingEmail && $existingEmail->username !== $username) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Địa chỉ email đã được sử dụng bởi người dùng khác."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $success = $this->accountModel->updateProfile($username, $fullname, $email);
        if ($success) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Cập nhật thông tin hồ sơ cá nhân thành công."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi hệ thống! Cập nhật hồ sơ thất bại."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // PUT /api/account/change-password
    public function changePassword()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Xác thực quyền
        $userData = AuthMiddleware::isAuthenticated();
        $username = $userData['username'] ?? '';

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $old_password = $input['old_password'] ?? '';
        $new_password = $input['new_password'] ?? '';

        if (empty($old_password) || empty($new_password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Vui lòng nhập mật khẩu cũ và mật khẩu mới."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $account = $this->accountModel->getAccountByUsername($username);
        if (!$account) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Tài khoản không tồn tại."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Kiểm tra mật khẩu cũ
        if (!password_verify($old_password, $account->password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Mật khẩu cũ không chính xác."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Băm mật khẩu mới với cost = 12
        $hashedNewPassword = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

        $success = $this->accountModel->updatePassword($username, $hashedNewPassword);
        if ($success) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Đổi mật khẩu thành công."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi hệ thống! Cập nhật mật khẩu thất bại."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // POST /api/account/forgot-password
    public function forgotPassword()
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $email = trim($input['email'] ?? '');

        if (empty($email)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Vui lòng cung cấp địa chỉ email."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $account = $this->accountModel->getAccountByEmail($email);
        if (!$account) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi! Không tìm thấy tài khoản nào liên kết với email này."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Tạo mã OTP ngẫu nhiên gồm 6 chữ số
        $otp = strval(rand(100000, 999999));
        // Đặt hạn sử dụng là 15 phút tính từ hiện tại
        $expiry = date('Y-m-d H:i:s', time() + 15 * 60);

        // Lưu mã OTP vào DB (qua cột reset_token vừa tạo)
        $success = $this->accountModel->updateResetToken($email, $otp, $expiry);

        if ($success) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "Gửi mã OTP khôi phục mật khẩu thành công.",
                "data" => [
                    "email" => $email,
                    "otp_simulated" => $otp // Trả về OTP mô phỏng để dễ kiểm thử
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => false,
                "message" => "Lỗi hệ thống! Không thể tạo mã khôi phục mật khẩu lúc này."
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
