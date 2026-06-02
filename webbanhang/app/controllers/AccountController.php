<?php
require_once(BASE_PATH . '/app/config/database.php');
require_once(BASE_PATH . '/app/models/AccountModel.php');

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

    // 3. Xử lý lưu thông tin Đăng ký đơn hàng tài khoản
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $fullName = $_POST['fullname'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $role = $_POST['role'] ?? 'user';

            $errors = [];
            if (empty($username)) $errors['username'] = "Vui lòng nhập username!";
            if (empty($fullName)) $errors['fullname'] = "Vui lòng nhập fullname!";
            if (empty($password)) $errors['password'] = "Vui lòng nhập password!";
            if ($password != $confirmPassword) $errors['confirmPass'] = "Mật khẩu và xác nhận mật khẩu chưa khớp!";
            if (!in_array($role, ['admin', 'user'])) $role = 'user';

            if ($this->accountModel->getAccountByUsername($username)) {
                $errors['account'] = "Tài khoản này đã được đăng ký trên hệ thống!";
            }

            if (count($errors) > 0) {
                include_once BASE_PATH . '/app/views/account/register.php';
            } else {
                $result = $this->accountModel->save($username, $fullName, $password, $role);
                if ($result) {
                    header('Location: /Account/login');
                    exit;
                }
            }
        }
    }

    // 4. Xử lý kiểm tra Đăng nhập
    public function checkLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $account = $this->accountModel->getAccountByUsername($username);

            // Xác thực mật khẩu băm bảo mật
            if ($account && password_verify($password, $account->password)) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['username'] = $account->username;
                $_SESSION['fullname'] = $account->fullname;
                $_SESSION['phone'] = property_exists($account, 'phone') ? $account->phone : '';
                $_SESSION['role'] = $account->role;
                $_SESSION['user_role'] = $account->role;

                header('Location: /Product');
                exit;
            } else {
                $error = $account ? "Mật khẩu nhập chưa chính xác!" : "Không tìm thấy tài khoản người dùng!";
                include_once BASE_PATH . '/app/views/account/login.php';
                exit;
            }
        }
    }

    // 5. Hiển thị trang Hồ sơ người dùng
    public function profile()
    {
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

    // 6. Thao tác Đăng xuất tài khoản
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['username']);
        unset($_SESSION['fullname']);
        unset($_SESSION['phone']);
        unset($_SESSION['role']);
        unset($_SESSION['user_role']);
        header('Location: /Product');
        exit;
    }
}