<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}
// Nạp helper JWTHelper phục vụ giải mã mã Token
require_once(BASE_PATH . '/app/helpers/JWTHelper.php');

use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthMiddleware
{
    /**
     * Xác thực Token JWT gửi kèm để đảm bảo người dùng đã đăng nhập (không phân biệt quyền)
     * @return array Dữ liệu người dùng giải mã được nếu hợp lệ
     */
    public static function isAuthenticated()
    {
        // Kịch bản 1: Kiểm tra thiếu Token trong Request Header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));

        if (empty($authHeader)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false, 
                "message" => "Access Denied. API đã bị chặn khi không đăng nhập tài khoản."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Tách lấy mã Token thực tế từ chuỗi 'Bearer <Token_JWT>'
        $token = '';
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            $token = trim($authHeader);
        }

        if (empty($token)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false,
                "message" => "Access Denied. API đã bị chặn khi không đăng nhập tài khoản."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Kịch bản 2: Sử dụng khối try-catch để giải mã mã token và bắt các ngoại lệ cụ thể
        try {
            $userData = JWTHelper::decodeToken($token);
            return $userData;
        } catch (ExpiredException $e) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false, 
                "message" => "Access Denied. API đã bị chặn khi không đăng nhập tài khoản."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (SignatureInvalidException $e) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false, 
                "message" => "Access Denied. API đã bị chặn khi không đăng nhập tài khoản."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false, 
                "message" => "Access Denied. API đã bị chặn khi không đăng nhập tài khoản."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    /**
     * Xác thực Token JWT và kiểm tra vai trò cụ thể
     * @param string $role Vai trò cần kiểm tra (ví dụ: 'admin')
     * @return array Dữ liệu người dùng giải mã được nếu hợp lệ
     */
    public static function hasRole($role)
    {
        // Thực hiện xác thực người dùng trước
        $userData = self::isAuthenticated();

        // Kịch bản 3: Kiểm tra phân quyền cụ thể
        $userRole = $userData['role'] ?? 'user';
        if ($userRole !== $role) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => false,
                "message" => "Forbidden. Bạn không có quyền thực hiện chức năng quản trị này."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        return $userData;
    }

    /**
     * Tương thích ngược: Xác thực Token JWT gửi kèm và kiểm tra vai trò Admin
     * @return array Dữ liệu người dùng giải mã được nếu hợp lệ
     */
    public static function checkAdmin()
    {
        return self::hasRole('admin');
    }

    /**
     * Tương thích ngược: Xác thực Token JWT gửi kèm để đảm bảo người dùng đã đăng nhập
     * @return array Dữ liệu người dùng giải mã được nếu hợp lệ
     */
    public static function checkLogin()
    {
        return self::isAuthenticated();
    }
}
