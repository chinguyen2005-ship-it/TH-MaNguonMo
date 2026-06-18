USE `my_store`;

-- 1. Xóa bảng cũ đi để làm lại cho chuẩn, tránh bị xung đột dữ liệu lệch hàng
DROP TABLE IF EXISTS `account`;

-- 2. Tạo lại bảng với cấu trúc chuẩn hóa, đầy đủ các trường mở rộng
CREATE TABLE `account` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `fullname` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Chèn lại dữ liệu của bạn vào đúng hàng đúng cột
-- Mật khẩu bên dưới là mã băm Bcrypt tương ứng của chuỗi '123456' (dùng để bạn test đăng nhập)
INSERT INTO `account` (`id`, `username`, `email`, `fullname`, `password`, `role`) 
VALUES (
    5, 
    'chinguyen2005', 
    'hsthptbb.ndcnguyen11a10@gmail.com', 
    'Nguyen Duc Chi Nguyen', 
    '$2y$10$HVrAIpBormOxzYs6N28wr.eE177lE9a9m7.7eCgA3aGf2lQc4K', -- Chuỗi Bcrypt hoàn chỉnh của mật khẩu
    'admin' -- Quyền tài khoản của bạn
);account