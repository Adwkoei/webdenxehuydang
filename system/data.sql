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
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `post` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`id`, `name`, `post`) VALUES
(1, '2020', 'Wave'),
(2, '2021', 'Wave'),
(3, '2022', 'Wave'),
(4, '2023', 'Wave'),
(5, '2024', 'Wave'),
(6, '2023', 'SH'),
(7, '2024', 'SH'),
(8, '2025', 'SH');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `post`
--

INSERT INTO `post` (`id`, `name`) VALUES
(1, 'Wave'),
(2, 'SH');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subfolder`
--

CREATE TABLE `subfolder` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `youtube` varchar(100) NOT NULL,
  `tiktok` varchar(100) NOT NULL,
  `post` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `policy` text NOT NULL,
  `view` varchar(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `subfolder`
--

INSERT INTO `subfolder` (`id`, `name`, `youtube`, `tiktok`, `post`, `category`, `content`, `policy`, `view`) VALUES
(1, 'Bi cầu', 'https://www.youtube.com/', 'https://www.tiktok.com/', 'Wave', '2020', 'Bi cầu xe Wave là một loại đèn pha được độ lại cho xe Wave, sử dụng thấu kính hội tụ để tạo ra luồng sáng mạnh và tập trung hơn so với đèn pha nguyên bản. Dưới đây là một số thông tin chi tiết về bi cầu xe Wave:\r\n\r\nCấu tạo:\r\n\r\nBóng đèn: Có thể là bóng halogen, xenon hoặc LED.\r\nBi cầu: Là một khối cầu bằng kim loại hoặc nhựa, có chứa thấu kính hội tụ.\r\nChóa phản xạ: Nằm phía sau bóng đèn, giúp phản xạ ánh sáng về phía trước.   \r\nMàn chắn: Điều chỉnh luồng sáng, tạo ra ranh giới rõ ràng giữa vùng sáng và vùng tối.   \r\nƯu điểm:\r\n\r\nTăng cường độ sáng và khả năng chiếu xa, giúp người lái quan sát tốt hơn trong điều kiện thiếu sáng.\r\nTạo ra luồng sáng tập trung, giảm thiểu tình trạng chói mắt cho người đi ngược chiều.\r\nTăng tính thẩm mỹ cho xe.\r\nCác loại bi cầu phổ biến:\r\n\r\nBi cầu halogen: Loại bi cầu truyền thống, sử dụng bóng đèn halogen.\r\nBi cầu xenon: Loại bi cầu cho ánh sáng trắng xanh, cường độ sáng cao.\r\nBi cầu LED: Loại bi cầu hiện đại, sử dụng bóng đèn LED, tiết kiệm điện và tuổi thọ cao.\r\nLưu ý khi độ bi cầu:\r\n\r\nNên chọn loại bi cầu có chất lượng tốt, phù hợp với xe Wave.\r\nViệc độ bi cầu cần được thực hiện bởi thợ có tay nghề cao để đảm bảo an toàn và hiệu quả.\r\nCần tuân thủ các quy định về ánh sáng khi tham gia giao thông.', 'Bảo hành 12 tháng cho lỗi kỹ thuật từ nhà sản xuất.\r\nĐổi mới trong 30 ngày nếu sản phẩm bị lỗi không do người dùng.\r\nBảo hành chống thấm nước và bụi bẩn theo tiêu chuẩn IP68.\r\nHỗ trợ bảo trì miễn phí trong suốt thời gian bảo hành.\r\nCam kết thay thế bóng đèn LED nếu bị mờ hoặc nhấp nháy bất thường.', '4');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `subfolder`
--
ALTER TABLE `subfolder`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `subfolder`
--
ALTER TABLE `subfolder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
