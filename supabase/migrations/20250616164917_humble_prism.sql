-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th2 26, 2025 lúc 05:36 PM
-- Phiên bản máy phục vụ: 10.4.25-MariaDB
-- Phiên bản PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `denxehuydang`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `short_name`, `slug`, `created_at`) VALUES
(1, 'Đèn pha bi cầu cao cấp', 'Bi cầu', 'bi-cau', NOW()),
(2, 'Đèn LED Audi chính hãng', 'Audi LED', 'audi-led', NOW()),
(3, 'Hệ thống khóa thông minh', 'Smartkey', 'smartkey', NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(20) NOT NULL,
  `name` varchar(500) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT '',
  `tiktok` varchar(255) DEFAULT '',
  `content` text DEFAULT '',
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `product_code`, `name`, `short_name`, `slug`, `category_id`, `youtube`, `tiktok`, `content`, `view_count`) VALUES
(1, 'SP001', 'Đèn pha bi cầu LED cao cấp cho xe Wave 2020-2024 với công nghệ chiếu sáng hiện đại', 'Bi cầu Wave', 'bi-cau-wave', 1, 'https://www.youtube.com/', 'https://www.tiktok.com/', 'Bi cầu xe Wave là một loại đèn pha được độ lại cho xe Wave, sử dụng thấu kính hội tụ để tạo ra luồng sáng mạnh và tập trung hơn so với đèn pha nguyên bản. Dưới đây là một số thông tin chi tiết về bi cầu xe Wave:\r\n\r\nCấu tạo:\r\n\r\nBóng đèn: Có thể là bóng halogen, xenon hoặc LED.\r\nBi cầu: Là một khối cầu bằng kim loại hoặc nhựa, có chứa thấu kính hội tụ.\r\nChóa phản xạ: Nằm phía sau bóng đèn, giúp phản xạ ánh sáng về phía trước.   \r\nMàn chắn: Điều chỉnh luồng sáng, tạo ra ranh giới rõ ràng giữa vùng sáng và vùng tối.   \r\nƯu điểm:\r\n\r\nTăng cường độ sáng và khả năng chiếu xa, giúp người lái quan sát tốt hơn trong điều kiện thiếu sáng.\r\nTạo ra luồng sáng tập trung, giảm thiểu tình trạng chói mắt cho người đi ngược chiều.\r\nTăng tính thẩm mỹ cho xe.\r\nCác loại bi cầu phổ biến:\r\n\r\nBi cầu halogen: Loại bi cầu truyền thống, sử dụng bóng đèn halogen.\r\nBi cầu xenon: Loại bi cầu cho ánh sáng trắng xanh, cường độ sáng cao.\r\nBi cầu LED: Loại bi cầu hiện đại, sử dụng bóng đèn LED, tiết kiệm điện và tuổi thọ cao.\r\nLưu ý khi độ bi cầu:\r\n\r\nNên chọn loại bi cầu có chất lượng tốt, phù hợp với xe Wave.\r\nViệc độ bi cầu cần được thực hiện bởi thợ có tay nghề cao để đảm bảo an toàn và hiệu quả.\r\nCần tuân thủ các quy định về ánh sáng khi tham gia giao thông.', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'warranty_policy', '1. Thời Gian Bảo Hành\n- Tất cả sản phẩm được bảo hành trong vòng 12 tháng kể từ ngày mua hàng.\n- Thời gian bảo hành có thể khác nhau tùy thuộc vào từng sản phẩm cụ thể.\n\n2. Điều Kiện Bảo Hành\n- Sản phẩm bị lỗi do nhà sản xuất.\n- Sản phẩm còn trong thời hạn bảo hành và có hóa đơn mua hàng hợp lệ.\n- Tem bảo hành phải còn nguyên vẹn, không bị rách hoặc chỉnh sửa.\n\n3. Những Trường Hợp Không Được Bảo Hành\n- Sản phẩm bị hư hỏng do sử dụng sai cách, va đập, rơi vỡ hoặc tác động từ bên ngoài.\n- Sản phẩm bị thay đổi, sửa chữa không được ủy quyền từ chúng tôi.\n- Sản phẩm bị hư hỏng do thiên tai như lũ lụt, hỏa hoạn, sét đánh...\n\n4. Quy Trình Bảo Hành\n1. Liên hệ bộ phận hỗ trợ khách hàng qua hotline hoặc email để đăng ký bảo hành.\n2. Gửi sản phẩm về trung tâm bảo hành cùng với hóa đơn mua hàng.\n3. Chúng tôi sẽ kiểm tra và thông báo kết quả trong vòng 7 ngày làm việc.\n\n5. Thời Gian Xử Lý Bảo Hành\n- Thời gian xử lý bảo hành thông thường là 7-14 ngày làm việc kể từ khi nhận sản phẩm.\n- Trong một số trường hợp đặc biệt, thời gian có thể kéo dài và sẽ được thông báo cụ thể.');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;