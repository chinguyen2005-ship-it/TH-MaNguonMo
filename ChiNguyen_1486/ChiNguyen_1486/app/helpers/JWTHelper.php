<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}
// Nạp autoload của Composer để sử dụng các lớp của Firebase JWT
require_once(BASE_PATH . '/vendor/autoload.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    // Khóa bí mật dùng để ký và xác thực token JWT (yêu cầu ít nhất 32 ký tự / 256 bits cho HS256)
    private static $secret_key = "Hutech_IT_Secret_Key_2026_Secure_Strong_Key_32_Bytes";

    /**
     * Sinh mã Token JWT từ dữ liệu của người dùng
     * @param mixed $user (object hoặc array chứa thông tin tài khoản)
     * @return string Chuỗi token JWT đã ký
     */
    public static function generateToken($user)
    {
        $issued_at = time();
        $expire = $issued_at + 3600; // Mã Token có thời hạn sử dụng là 1 giờ

        // Định dạng thông tin payload JWT theo chuẩn
        $payload = [
            "iss" => "ShopIT",               // Tổ chức phát hành token (Issuer)
            "aud" => "ShopIT_Client",        // Ứng dụng khách sử dụng token (Audience)
            "iat" => $issued_at,             // Thời điểm phát hành token (Issued At)
            "exp" => $expire,                // Thời điểm token hết hạn (Expiration Time)
            "data" => [                      // Thông tin thực tế đóng gói bên trong token
                "id" => isset($user->id) ? $user->id : ($user['id'] ?? null),
                "username" => isset($user->username) ? $user->username : ($user['username'] ?? ''),
                "email" => isset($user->email) ? $user->email : ($user['email'] ?? ''),
                "role" => isset($user->role) ? $user->role : ($user['role'] ?? 'user')
            ]
        ];

        // Mã hóa HS256 và ký mã token
        return JWT::encode($payload, self::$secret_key, 'HS256');
    }

    /**
     * Giải mã và kiểm tra mã Token JWT truyền lên từ client
     * @param string $token Mã token JWT
     * @return array Trả về mảng dữ liệu người dùng nếu token hợp lệ
     * @throws Exception Các ngoại lệ nếu token sai, hết hạn, giả mạo
     */
    public static function decodeToken($token)
    {
        // Thực hiện giải mã mã token bằng khóa bí mật và thuật toán HS256
        $decoded = JWT::decode($token, new Key(self::$secret_key, 'HS256'));
        // Chuyển đối tượng data giải mã được sang dạng mảng (array)
        return (array) $decoded->data;
    }
}
