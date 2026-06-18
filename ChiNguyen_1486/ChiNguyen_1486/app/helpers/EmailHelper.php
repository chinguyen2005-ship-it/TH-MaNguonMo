<?php
class EmailHelper {
    /**
     * Mô phỏng gửi Email bằng cách ghi nhật ký ra file uploads/email_log.txt.
     * Người dùng có thể kiểm tra tệp này để lấy mã kích hoạt hoặc đường dẫn đặt lại mật khẩu.
     */
    public static function send($to, $subject, $body) {
        $logDir = BASE_PATH . '/uploads';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/email_log.txt';
        
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('=', 80);
        
        $emailContent = "{$divider}\n";
        $emailContent .= "THỜI GIAN GỬI: {$timestamp}\n";
        $emailContent .= "GỬI ĐẾN: {$to}\n";
        $emailContent .= "TIÊU ĐỀ: {$subject}\n";
        $emailContent .= "NỘI DUNG:\n{$body}\n";
        $emailContent .= "{$divider}\n\n";

        // Ghi tiếp vào cuối file
        return file_put_contents($logFile, $emailContent, FILE_APPEND | LOCK_EX) !== false;
    }
}
