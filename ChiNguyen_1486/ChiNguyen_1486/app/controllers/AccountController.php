<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/AccountModel.php');
require_once(BASE_PATH . '/app/helpers/EmailHelper.php');
require_once(BASE_PATH . '/app/helpers/SessionHelper.php');

class AccountController {
    private $accountModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
    }

    // 1. Gọi form Đăng ký
    public function register() {
        include_once BASE_PATH . '/app/views/account/register.php';
    }

    // 2. Gọi form Đăng nhập
    public function login() {
        include_once BASE_PATH . '/app/views/account/login.php';
    }

    // 3. Xử lý lưu thông tin Đăng ký tài khoản
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username'] ?? '');
            $fullName = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $role = $_POST['role'] ?? 'user';

            $errors = [];
            if (empty($username)) $errors['username'] = "Vui lòng nhập tên tài khoản!";
            if (empty($fullName)) $errors['fullname'] = "Vui lòng nhập họ và tên!";
            if (empty($email)) {
                $errors['email'] = "Vui lòng nhập email!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Địa chỉ email không đúng định dạng!";
            }
            if (empty($password)) $errors['password'] = "Vui lòng nhập mật khẩu!";
            if ($password != $confirmPassword) $errors['confirmPass'] = "Mật khẩu và xác nhận mật khẩu chưa khớp!";
            if (!in_array($role, ['admin', 'user'])) $role = 'user';

            if ($this->accountModel->getAccountByUsername($username)) {
                $errors['account'] = "Tài khoản này đã được đăng ký trên hệ thống!";
            }
            if ($this->accountModel->getAccountByEmail($email)) {
                $errors['email_exist'] = "Địa chỉ email này đã tồn tại trên hệ thống!";
            }

            if (count($errors) > 0) {
                include_once BASE_PATH . '/app/views/account/register.php';
            } else {
                // Đăng ký lưu tài khoản mới truyền đúng thứ tự: username, email, fullname, password, role
                $result = $this->accountModel->save($username, $email, $fullName, $password, $role);
                
                if ($result) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['register_success'] = "Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.";
                    header('Location: /Account/login');
                    exit;
                } else {
                    $errors['db'] = "Đã xảy ra lỗi khi tạo tài khoản. Vui lòng thử lại.";
                    include_once BASE_PATH . '/app/views/account/register.php';
                }
            }
        }
    }

    // 4. Xác thực tài khoản bằng Email Token
    public function verify() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            die("Mã xác thực không hợp lệ.");
        }

        $result = $this->accountModel->verifyEmail($token);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($result) {
            $_SESSION['register_success'] = "Tài khoản của bạn đã được xác thực thành công! Bạn có thể đăng nhập ngay.";
        } else {
            $_SESSION['register_error'] = "Liên kết kích hoạt không hợp lệ hoặc tài khoản đã được kích hoạt trước đó.";
        }
        header('Location: /Account/login');
        exit;
    }

    // 5. Xử lý kiểm tra Đăng nhập
    public function checkLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $account = $this->accountModel->getAccountByUsername($username);

            // Xác thực mật khẩu
            if ($account && password_verify($password, $account->password)) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $account->id;
                $_SESSION['username'] = $account->username;
                $_SESSION['fullname'] = $account->fullname;
                $_SESSION['email'] = $account->email;
                $_SESSION['role'] = $account->role;
                $_SESSION['user_role'] = $account->role;
                $_SESSION['avatar'] = $account->avatar;

                // Sinh mã token JWT và lưu vào Session để truyền cho kịch bản Ajax ở view
                require_once(BASE_PATH . '/app/helpers/JWTHelper.php');
                $_SESSION['token'] = JWTHelper::generateToken($account);

                // Xử lý Remember Me (Ghi nhớ đăng nhập)
                if ($remember) {
                    $rememberToken = bin2hex(random_bytes(16));
                    $this->accountModel->updateRememberToken($account->username, $rememberToken);
                    // Lưu Cookie 30 ngày
                    $cookieValue = base64_encode($account->username . ':' . $rememberToken);
                    setcookie('remember_me', $cookieValue, time() + 30 * 24 * 3600, '/');
                } else {
                    // Xóa cookie cũ nếu có
                    if (isset($_COOKIE['remember_me'])) {
                        setcookie('remember_me', '', time() - 3600, '/');
                    }
                }

                header('Location: /Product');
                exit;
            } else {
                $error = $account ? "Mật khẩu nhập chưa chính xác!" : "Không tìm thấy tài khoản người dùng!";
                include_once BASE_PATH . '/app/views/account/login.php';
                exit;
            }
        }
    }

    // 6. Hiển thị trang Hồ sơ người dùng
    public function profile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['username'])) {
            header('Location: /Account/login');
            exit;
        }

        $user = $this->accountModel->getAccountByUsername($_SESSION['username']);
        if (!$user) {
            header('Location: /Account/login');
            exit;
        }

        $pageTitle = 'Hồ sơ của tôi';
        include_once BASE_PATH . '/app/views/account/profile.php';
    }

    // 7. Xử lý Cập nhật thông tin Hồ sơ cá nhân
    public function updateProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['username'])) {
            header('Location: /Account/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_SESSION['username'];
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $errors = [];
            if (empty($fullname)) $errors['fullname'] = "Vui lòng điền Họ tên.";
            if (empty($email)) {
                $errors['email'] = "Vui lòng điền Email.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email không hợp lệ.";
            }

            // Kiểm tra email trùng với tài khoản khác
            $existingUser = $this->accountModel->getAccountByEmail($email);
            if ($existingUser && $existingUser->username !== $username) {
                $errors['email'] = "Email này đã được sử dụng bởi một tài khoản khác.";
            }

            // Xử lý upload ảnh đại diện
            $avatar = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $avatar = $this->uploadAvatar($_FILES['avatar']);
                } catch (Exception $e) {
                    $errors['avatar'] = $e->getMessage();
                }
            }

            if (count($errors) > 0) {
                $_SESSION['profile_errors'] = $errors;
                header('Location: /Account/profile');
                exit();
            }

            $success = $this->accountModel->updateProfile($username, $fullname, $email, $avatar);
            if ($success) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                if ($avatar !== null) {
                    $_SESSION['avatar'] = $avatar;
                }
                $_SESSION['profile_success'] = "Cập nhật thông tin cá nhân thành công!";
            } else {
                $_SESSION['profile_error'] = "Lỗi hệ thống khi cập nhật thông tin.";
            }
            
            header('Location: /Account/profile');
            exit();
        }
    }

    // 8. Xử lý Thay đổi Mật khẩu
    public function updatePassword() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['username'])) {
            header('Location: /Account/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_SESSION['username'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
                $_SESSION['password_error'] = "Vui lòng nhập đầy đủ thông tin mật khẩu.";
                header('Location: /Account/profile#password');
                exit();
            }

            $user = $this->accountModel->getAccountByUsername($username);
            if (!$user || !password_verify($currentPassword, $user->password)) {
                $_SESSION['password_error'] = "Mật khẩu hiện tại không chính xác.";
                header('Location: /Account/profile#password');
                exit();
            }

            if ($newPassword !== $confirmNewPassword) {
                $_SESSION['password_error'] = "Xác nhận mật khẩu mới chưa trùng khớp.";
                header('Location: /Account/profile#password');
                exit();
            }

            if (strlen($newPassword) < 6) {
                $_SESSION['password_error'] = "Mật khẩu mới phải từ 6 ký tự trở lên.";
                header('Location: /Account/profile#password');
                exit();
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $success = $this->accountModel->updatePassword($username, $newHash);
            if ($success) {
                $_SESSION['password_success'] = "Thay đổi mật khẩu thành công!";
            } else {
                $_SESSION['password_error'] = "Đã xảy ra lỗi hệ thống khi đổi mật khẩu.";
            }

            header('Location: /Account/profile#password');
            exit();
        }
    }

    // 9. Xử lý Quên mật khẩu
    public function forgotPassword() {
        include_once BASE_PATH . '/app/views/account/forgot_password.php';
    }

    // 10. Gửi liên kết đặt lại mật khẩu
    public function sendResetLink() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Địa chỉ Email không hợp lệ.";
                include_once BASE_PATH . '/app/views/account/forgot_password.php';
                exit();
            }

            $user = $this->accountModel->getAccountByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(16));
                $expiry = date('Y-m-d H:i:s', time() + 3600); // Hết hạn trong 1 giờ

                $this->accountModel->updateResetToken($email, $token, $expiry);
                
                // Tạo link đặt lại mật khẩu
                $resetLink = $this->getBaseUrl() . "/Account/resetPassword?token=" . $token;
                $subject = "Yeu cau dat lai mat khau tai Shop IT";
                $body = "Chào {$user->fullname},\n\nChúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\nVui lòng nhấp vào đường dẫn dưới đây để thay đổi mật khẩu (có hiệu lực trong 1 giờ):\n\n{$resetLink}\n\nNếu bạn không yêu cầu điều này, xin vui lòng bỏ qua email.\n\nTrân trọng,\nBan quản trị Shop IT.";
                
                EmailHelper::send($email, $subject, $body);
            }

            // Luôn hiển thị thông báo thành công vì lý do bảo mật bảo vệ tài khoản
            $success = "Nếu Email trên khớp với tài khoản trong hệ thống, hướng dẫn đặt lại mật khẩu đã được gửi thành công. Vui lòng kiểm tra email của bạn (hoặc kiểm tra file uploads/email_log.txt).";
            include_once BASE_PATH . '/app/views/account/forgot_password.php';
            exit();
        }
    }

    // 11. Trang đặt lại mật khẩu mới
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            die("Yêu cầu đặt lại mật khẩu không hợp lệ.");
        }

        $user = $this->accountModel->getUserByResetToken($token);
        if (!$user) {
            $error = "Đường dẫn khôi phục mật khẩu không hợp lệ hoặc đã hết hạn sử dụng. Vui lòng gửi lại yêu cầu mới.";
            include_once BASE_PATH . '/app/views/account/forgot_password.php';
            exit();
        }

        include_once BASE_PATH . '/app/views/account/reset_password.php';
    }

    // 12. Thực hiện đổi mật khẩu sau khi quên
    public function updatePasswordWithToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($token)) {
                die("Mã thông báo không hợp lệ.");
            }

            $user = $this->accountModel->getUserByResetToken($token);
            if (!$user) {
                $error = "Yêu cầu khôi phục đã hết hạn. Vui lòng làm lại từ đầu.";
                include_once BASE_PATH . '/app/views/account/forgot_password.php';
                exit();
            }

            $errors = [];
            if (empty($password)) $errors['password'] = "Vui lòng nhập mật khẩu mới.";
            if ($password !== $confirmPassword) $errors['confirm'] = "Xác nhận mật khẩu chưa khớp.";
            if (strlen($password) < 6) $errors['length'] = "Mật khẩu phải từ 6 ký tự trở lên.";

            if (count($errors) > 0) {
                include_once BASE_PATH . '/app/views/account/reset_password.php';
                exit();
            }

            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $success = $this->accountModel->updatePassword($user->username, $newHash);
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if ($success) {
                $_SESSION['register_success'] = "Đặt lại mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.";
                header('Location: /Account/login');
                exit();
            } else {
                $error = "Có lỗi xảy ra trong quá trình cập nhật. Vui lòng liên hệ hỗ trợ.";
                include_once BASE_PATH . '/app/views/account/reset_password.php';
                exit();
            }
        }
    }

    // 13. Thao tác Đăng xuất tài khoản
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = $_SESSION['username'] ?? '';
        if (!empty($username)) {
            $this->accountModel->updateRememberToken($username, null);
        }

        // Xóa Cookie Remember Me
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        unset($_SESSION['username']);
        unset($_SESSION['fullname']);
        unset($_SESSION['email']);
        unset($_SESSION['phone']);
        unset($_SESSION['role']);
        unset($_SESSION['user_role']);
        unset($_SESSION['avatar']);
        unset($_SESSION['token']);

        header('Location: /Account/login');
        exit;
    }

    // 14. [ADMIN] Quản lý thành viên
    public function users() {
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        $users = $this->accountModel->getAllUsers();
        $pageTitle = 'Quản lý người dùng';
        include_once BASE_PATH . '/app/views/account/users.php';
    }

    public function toggleLock($username) {
        $username = urldecode($username);
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        // Không cho phép Admin tự khóa tài khoản chính mình
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($username === $_SESSION['username']) {
            $_SESSION['admin_error'] = "Không thể tự khóa tài khoản của chính mình.";
            header('Location: /Account/users');
            exit();
        }

        $this->accountModel->toggleUserLock($username);
        header('Location: /Account/users');
        exit();
    }

    // 16. [ADMIN] Xóa tài khoản người dùng
    public function deleteUser($username) {
        $username = urldecode($username);
        if (!SessionHelper::isAdmin()) {
            header('Location: /Product');
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($username === $_SESSION['username']) {
            $_SESSION['admin_error'] = "Không thể tự xóa tài khoản của chính mình.";
            header('Location: /Account/users');
            exit();
        }

        $this->accountModel->deleteUser($username);
        header('Location: /Account/users');
        exit();
    }

    // Helper: Upload ảnh đại diện
    private function uploadAvatar($file) {
        $target_dir = "uploads/avatars/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            throw new Exception("Chỉ hỗ trợ file ảnh định dạng JPG, PNG, JPEG hoặc GIF.");
        }

        // Giới hạn dung lượng 2MB
        if ($file["size"] > 2 * 1024 * 1024) {
            throw new Exception("Dung lượng file tối đa là 2MB.");
        }

        $target_file = $target_dir . uniqid() . '.' . $imageFileType;
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Lỗi khi tải file lên máy chủ.");
        }
        return $target_file;
    }

    // Helper: Lấy địa chỉ Base URL động theo cấu hình Server hiện tại
    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $scriptDir = rtrim($scriptDir, '/');
        return $protocol . $host . $scriptDir;
    }
}