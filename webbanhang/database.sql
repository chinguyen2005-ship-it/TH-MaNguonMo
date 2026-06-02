-- 1. Khởi tạo và sử dụng Cơ sở dữ liệu my_store
CREATE DATABASE IF NOT EXISTS `my_store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `my_store`;

-- Tắt kiểm tra khóa ngoại để dọn dẹp cấu trúc cũ nếu có, tránh xung đột hệ thống
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_details`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `account`;
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `category`;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Tạo bảng danh mục sản phẩm (category)
CREATE TABLE `category` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tạo bảng sản phẩm (product) - Liên kết chặt chẽ với bảng danh mục
CREATE TABLE `product` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image` VARCHAR(255) DEFAULT NULL,
  `category_id` INT,
  FOREIGN KEY (`category_id`) REFERENCES `category`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tạo bảng tài khoản (account) - Đã có sẵn cột 'fullname' khớp 100% với code PHP của bạn
CREATE TABLE `account` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tạo bảng đơn hàng (orders)
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cod',
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tạo bảng chi tiết đơn hàng (order_details) - Liên kết với đơn hàng và sản phẩm
CREATE TABLE `order_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- CHÈN DỮ LIỆU MẪU CHUẨN (KHÔNG BỊ TRÙNG LẶP DANH MỤC)
-- --------------------------------------------------------

-- Chèn dữ liệu mẫu cho bảng danh mục (Đúng 5 nhóm công nghệ sạch sẽ)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
(1, 'Điện thoại', 'Danh mục các loại điện thoại chính hãng giá tốt'),
(2, 'Laptop', 'Các dòng laptop học tập, làm việc và Gaming hiệu năng cao'),
(3, 'Máy tính bảng', 'Máy tính bảng giải trí, làm việc màn hình lớn'),
(4, 'Phụ kiện', 'Cáp sạc nhanh, tai nghe, bao da, ốp lưng chính hiệu'),
(5, 'Thiết bị âm thanh', 'Loa bluetooth, tai nghe chống ồn giải trí cực đỉnh');

-- Chèn dữ liệu mẫu cho bảng sản phẩm (Để trang chủ không bị trống)
INSERT INTO `product` (`name`, `description`, `price`, `image`, `category_id`) VALUES 
('iPhone 15 Pro Max 256GB', 'Màn hình Dynamic Island, Chip Apple A17 Pro siêu mạnh mẽ', 29990000.00, 'uploads/iphone15.png', 1),
('Laptop Asus ROG Strix G16', 'CPU Intel Core i7, VGA RTX 4060, Màn hình 165Hz chuyên Gaming', 34500000.00, 'uploads/rog_strix.png', 2);

-- Chèn sẵn tài khoản kiểm thử hệ thống (Mật khẩu text mẫu: 123456)
-- Bạn có thể dùng tài khoản này để đăng nhập test tính năng Admin / User trên giao diện web
INSERT INTO `account` (`fullname`, `username`, `password`, `role`) VALUES 
('Quản trị viên Hệ thống', 'admin', '123456', 'admin'),
('Khách hàng thành viên', 'user', '123456', 'user');