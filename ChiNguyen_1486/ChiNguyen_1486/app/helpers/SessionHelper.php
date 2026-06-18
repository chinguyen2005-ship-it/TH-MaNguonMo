<?php
class SessionHelper {
    // Khởi động session an toàn nếu chưa bật
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Kiểm tra người dùng hiện tại đã đăng nhập chưa
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['username']);
    }

    // Kiểm tra tài khoản hiện tại có phải là Admin không
    public static function isAdmin() {
        self::start();
        return isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Lấy vai trò (role) hiện tại, nếu chưa đăng nhập mặc định là khách (guest)
    public static function getRole() {
        self::start();
        return $_SESSION['role'] ?? 'guest';
    }
}