<?php
define('BASE_PATH', __DIR__);
require_once(BASE_PATH . '/app/config/database.php');

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Không thể kết nối cơ sở dữ liệu.");
    }

    echo "--- BẮT ĐẦU DI CHUYỂN CƠ SỞ DỮ LIỆU ---\n";

    // 1. Kiểm tra các cột hiện tại của bảng `account`
    $stmt = $db->query("DESCRIBE `account`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $newColumns = [
        'email' => "VARCHAR(150) NULL",
        'phone' => "VARCHAR(20) NULL",
        'avatar' => "VARCHAR(255) NULL",
        'status' => "VARCHAR(20) NOT NULL DEFAULT 'active'",
        'remember_token' => "VARCHAR(255) NULL",
        'reset_token' => "VARCHAR(255) NULL",
        'reset_token_expiry' => "DATETIME NULL",
        'email_verification_token' => "VARCHAR(255) NULL",
        'is_verified' => "TINYINT(1) NOT NULL DEFAULT 0"
    ];

    // Tạo câu lệnh ALTER TABLE
    foreach ($newColumns as $colName => $colDefinition) {
        if (!in_array($colName, $columns)) {
            // Xác định vị trí chèn cột
            $after = "";
            if ($colName === 'email') $after = " AFTER `fullname`";
            elseif ($colName === 'phone') $after = " AFTER `email`";
            elseif ($colName === 'avatar') $after = " AFTER `password`";
            elseif ($colName === 'status') $after = " AFTER `role`";
            elseif ($colName === 'remember_token') $after = " AFTER `status`";
            elseif ($colName === 'reset_token') $after = " AFTER `remember_token`";
            elseif ($colName === 'reset_token_expiry') $after = " AFTER `reset_token`";
            elseif ($colName === 'email_verification_token') $after = " AFTER `reset_token_expiry`";
            elseif ($colName === 'is_verified') $after = " AFTER `email_verification_token`";

            $sql = "ALTER TABLE `account` ADD `$colName` " . $colDefinition . $after;
            $db->exec($sql);
            echo "Đã thêm cột `$colName` thành công.\n";
        } else {
            echo "Cột `$colName` đã tồn tại.\n";
        }
    }

    // 2. Kiểm tra và cập nhật các mật khẩu mẫu chưa băm (plain text '123456')
    // Mật khẩu băm Bcrypt cho '123456': $2y$10$iFkGg22/f8vjWdD34qZ6jO0O1xQvE8Z0g0g.1.uN30s1X70n.2dYy
    $hash = password_hash('123456', PASSWORD_BCRYPT);
    
    // Tìm các tài khoản có mật khẩu chưa băm (độ dài mật khẩu ngắn, vd dưới 50 ký tự)
    $stmt = $db->query("SELECT id, username, password FROM `account`");
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    foreach ($users as $u) {
        if (strlen($u->password) < 50) { // Mật khẩu Bcrypt luôn dài 60 ký tự
            $newHash = password_hash($u->password, PASSWORD_BCRYPT);
            $updateStmt = $db->prepare("UPDATE `account` SET password = :password WHERE id = :id");
            $updateStmt->execute([
                ':password' => $newHash,
                ':id' => $u->id
            ]);
            echo "Đã tự động băm mật khẩu cho tài khoản `{$u->username}` (Mật khẩu cũ: {$u->password}).\n";
        }
    }

    // Thiết lập mặc định tài khoản admin và user có sẵn là đã kích hoạt và hoạt động
    $db->exec("UPDATE `account` SET `status` = 'active', `is_verified` = 1 WHERE `username` IN ('admin', 'user')");
    echo "Đã kích hoạt và mở khóa mặc định cho tài khoản `admin` và `user`.\n";

    echo "--- DI CHUYỂN CƠ SỞ DỮ LIỆU HOÀN TẤT THÀNH CÔNG ---\n";
} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
}
