-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th12 11, 2025 lúc 02:22 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `jdm`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `created_at`) VALUES
(13, 3, 1200000.00, 'cancelled', '2025-12-03 03:28:51'),
(14, 3, 1200000.00, 'cancelled', '2025-12-03 03:30:03'),
(17, 4, 580000.00, 'cancelled', '2025-12-05 06:53:48'),
(18, 4, 2660000.00, 'paid', '2025-12-05 07:53:29'),
(19, 10, 1078000.00, 'cancelled', '2025-12-10 12:58:52'),
(20, 3, 280000.00, 'pending', '2025-12-10 15:08:04'),
(21, 3, 300000.00, 'pending', '2025-12-10 15:50:39'),
(22, 10, 300000.00, 'cancelled', '2025-12-11 04:08:25'),
(23, 10, 280000.00, 'cancelled', '2025-12-11 13:30:47'),
(24, 10, 300000.00, 'pending', '2025-12-11 13:32:42'),
(25, 10, 350000.00, 'paid', '2025-12-11 13:59:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(48, 18, 35, 1, 300000.00),
(49, 18, 31, 1, 300000.00),
(50, 18, 29, 1, 300000.00),
(51, 18, 22, 1, 300000.00),
(52, 18, 16, 1, 290000.00),
(53, 18, 14, 1, 290000.00),
(54, 18, 8, 1, 300000.00),
(55, 18, 12, 1, 290000.00),
(56, 18, 11, 1, 290000.00),
(60, 20, 43, 1, 280000.00),
(61, 21, 31, 1, 300000.00),
(64, 24, 8, 1, 300000.00),
(65, 25, 30, 1, 350000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) DEFAULT NULL COMMENT 'Nissan,Mazda,Toyota,Honda,Mitsubishi',
  `model` varchar(100) DEFAULT NULL COMMENT 'skyline r34,ae86,lancer evo,civic ',
  `scale` varchar(10) NOT NULL COMMENT '1:18,1:32',
  `price` decimal(10,2) NOT NULL,
  `color` varchar(100) NOT NULL,
  `stock` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `brand`, `model`, `scale`, `price`, `color`, `stock`, `image`, `description`, `created_at`) VALUES
(1, 'Toyota', 'Supra MK5 ', '1:32', 200000.00, 'Đỏ', 50, 'toyota_supra_mk5.jpg', 'Toyota GR Supra Mk5 (còn gọi là A90, ra mắt vào năm 2019) là một chiếc xe thể thao hai chỗ ngồi rất được mong đợi, đánh dấu sự trở lại của huyền thoại Supra sau 17 năm vắng bóng.', '2025-11-20 14:38:33'),
(2, 'Toyota', 'Supra MK4 ', '1:32', 250000.00, 'Trắng Sữa', 45, 'toyota_supra_mk4.jpeg', 'Toyota Supra Mk4 (hay còn gọi là A80) là một trong những chiếc xe thể thao Nhật Bản (JDM - Japan Domestic Market) mang tính biểu tượng và huyền thoại nhất trong lịch sử ô tô. Được sản xuất từ năm 1993 đến 2002, Mk4 đã vượt qua ranh giới của một chiếc xe thể thao thông thường để trở thành một biểu tượng văn hóa, đặc biệt là trong giới độ xe và qua các bộ phim.', '2025-11-20 14:38:33'),
(3, 'Toyota', 'GT86 ', '1:32', 280000.00, 'Đỏ', 50, 'toyota_gt86.jpeg', 'GT86 là kết quả của sự hợp tác kỹ thuật giữa Toyota và Subaru. Cả hai công ty cùng phát triển khung gầm và hệ thống truyền động, dẫn đến hai mẫu xe song sinh là Toyota GT86 và Subaru BRZ.', '2025-11-20 14:42:55'),
(4, 'Toyota', 'MR2 ', '1:32', 230000.00, 'Vàng Sọc Đỏ', 45, 'toyota_mr2.jpg', 'Toyota MR2 là một chiếc xe thể thao rất độc đáo và thú vị của Toyota, nổi tiếng với bố cục động cơ đặt giữa (Mid-engine, Rear-wheel drive, 2-seater), mang lại khả năng xử lý sắc nét như một chiếc siêu xe thu nhỏ.', '2025-11-20 14:42:55'),
(5, 'Toyota', 'Celica ', '1:32', 220000.00, 'Trắng Bạc', 50, 'toyota_celica.jpg', 'Toyota Celica là một dòng xe thể thao coupe/liftback mang tính biểu tượng của Toyota, được sản xuất qua bảy thế hệ (từ 1970 đến 2006). Không giống như Supra (động cơ I6 lớn) hay MR2 (động cơ đặt giữa), Celica nổi tiếng là một chiếc xe thể thao dẫn động cầu trước (FWD) tập trung vào phong cách và khả năng xử lý linh hoạt.\r\n\r\nĐiểm làm nên huyền thoại của Celica là phiên bản hiệu năng cao Celica GT-Four (sử dụng dẫn động bốn bánh - AWD), thống trị các giải đua WRC (World Rally Championship) trong thập niên 80 và 90.', '2025-11-20 14:46:43'),
(6, 'Toyota', 'GR Supra GT4 ', '1:32', 290000.00, 'Đen', 45, 'toyota_gr_supra_gt4.jpg', 'Toyota GR Supra GT4 là phiên bản xe đua chuyên dụng, được phát triển từ chiếc GR Supra (A90) thương mại, nhằm mục đích cạnh tranh trong các giải đua thể thao GT (Grand Touring) trên toàn thế giới, đặc biệt là ở hạng mục GT4.', '2025-11-20 14:46:43'),
(7, 'Toyota', 'AE86 Levin ', '1:32', 300000.00, 'Trắng Đen', 50, 'toyota_ae86_levin.jpg', 'Levin là người anh em song sinh về mặt cơ khí của Trueno, sử dụng cùng khung gầm RWD và động cơ 4A-GE mạnh mẽ. Điểm khác biệt duy nhất là Levin được trang bị đèn pha cố định (fixed headlights), khiến đầu xe có hình dáng vuông vức và góc cạnh hơn. Mặc dù ít xuất hiện trong văn hóa đại chúng hơn Trueno, Levin vẫn được các tín đồ JDM săn đón không kém nhờ triết lý thiết kế đơn giản, sự bền bỉ của động cơ 4A-GE và khả năng xử lý sắc nét, tập trung vào niềm vui lái xe thuần túy.', '2025-11-20 14:50:58'),
(8, 'Toyota', 'AE86 Trueno  Panda ', '1:32', 300000.00, 'Trắng Đen  ', 46, 'toyota_ae86_trueno.jpg', 'Trueno là phiên bản AE86 nổi tiếng nhất với đặc trưng là đèn pha bật/mở (pop-up headlights). Khi đèn tắt, mũi xe trở nên mượt mà và khí động học, mang lại vẻ ngoài cổ điển của một chiếc xe thể thao thập niên 80. Về cơ khí, nó sử dụng động cơ huyền thoại 4A-GE 1.6L, DOHC, 16 van, kết hợp với hệ dẫn động cầu sau (RWD) và trọng lượng nhẹ (dưới 1 tấn), khiến nó trở thành chiếc xe hoàn hảo cho việc drift và xử lý cân bằng. Trueno Hatchback (màu Panda) là chiếc xe biểu tượng trong series Initial D thuộc về Takumi Fujiwara.', '2025-11-20 14:50:58'),
(9, 'Subaru', 'Impreza ', '1:32', 300000.00, 'Xanh Dương', 40, 'subaru_impreza.jpg', 'Subaru Impreza là một dòng xe rất quan trọng đối với Subaru, nổi tiếng toàn cầu vì hệ dẫn động bốn bánh toàn thời gian (AWD) tiêu chuẩn và di sản rực rỡ trong các giải đua Rally.Phần lớn danh tiếng của Impreza đến từ các phiên bản hiệu năng cao, đặc biệt là WRX (World Rally eXperimental) và WRX STI (Subaru Tecnica International).', '2025-11-20 14:56:04'),
(10, 'Nissan', 'Silvia S15 ', '1:32', 290000.00, 'Cam Xanh Dương', 45, 'nissan_silvia_s15.jpg', 'Nissan Silvia S15 là một trong những chiếc xe thể thao được yêu thích nhất của Nissan và được coi là đỉnh cao, là thế hệ cuối cùng của dòng xe Silvia huyền thoại.S15 sử dụng phiên bản cuối cùng và mạnh mẽ nhất của dòng động cơ SR của Nissan.Nissan Silvia S15 là một chiếc xe có giá trị sưu tầm rất cao trên toàn thế giới. Sau khi ngừng sản xuất, sự vắng bóng của một chiếc coupe thể thao RWD giá cả phải chăng đã tạo nên một lỗ hổng lớn.', '2025-11-20 14:56:04'),
(11, 'Nissan', 'Silvia S13  ', '1:32', 290000.00, 'Xanh Lá', 44, 'nissan_silvia_s13.jpg', 'Nissan S13 là thế hệ đã biến dòng Silvia thành một biểu tượng toàn cầu về khả năng drift và độ xe.S13 mang phong cách thiết kế gọn gàng, vuông vức nhưng thanh thoát, rất đặc trưng của cuối thập niên 80.Linh hồn S13: Động cơ SR20DET (đặc biệt là bản \"Red Top\" hoặc \"Black Top\") được tôn sùng vì tiềm năng độ chế khổng lồ, là động cơ tiêu chuẩn cho xe drift.', '2025-11-20 15:03:58'),
(12, 'Nissan', 'Silvia S14 ', '1:32', 290000.00, 'Cam', 44, 'nissan_silvia_s14.jpg', 'S14 là thế hệ thứ hai của dòng S-chassis được quốc tế hóa, mang lại sự tinh tế và trưởng thành hơn về thiết kế.S14 tiếp tục sử dụng động cơ SR20DET và KA24DE, nhưng đã có những cải tiến đáng kể.Nhờ kích thước lớn hơn và chiều dài cơ sở dài hơn, S14 được đánh giá là ổn định hơn S13 ở tốc độ cao, nhưng vẫn giữ được sự cân bằng RWD tuyệt vời.', '2025-11-20 15:03:58'),
(13, 'Nissan', 'GTR R35 Nismo ', '1:32', 290000.00, 'Trắng', 40, 'nissan_gtr_r35_nismo.jpg', 'Nissan GT-R R35 NISMO là đỉnh cao về hiệu suất và công nghệ trong dòng xe GT-R hiện đại. Nó đại diện cho sự kết hợp giữa kỹ thuật tiên tiến của Nissan và kinh nghiệm đua xe hàng thập kỷ của bộ phận hiệu suất cao NISMO (Nissan Motorsports International).Nissan GT-R R35 NISMO không chỉ là một chiếc xe nhanh; nó là một kiệt tác kỹ thuật số, được chế tạo để thống trị trên đường đua, kết hợp sức mạnh động cơ tuyệt đối với khí động học tiên tiến và khả năng xử lý của xe đua chuyên nghiệp.', '2025-11-20 15:08:45'),
(14, 'Nissan', 'Sileighty ', '1:32', 290000.00, 'Xanh Dương', 44, 'nissan_sileighty.jpg', 'Nissan Sileighty (còn được viết là Silvia Sileighty) là một chiếc xe rất độc đáo và nổi tiếng trong văn hóa JDM (Japan Domestic Market), đặc biệt là trong cộng đồng drift. Sileighty không phải là một mẫu xe do Nissan sản xuất hàng loạt, mà là một sự kết hợp giữa hai mẫu xe khác nhau.Sileighty về cơ bản là một chiếc Nissan 180SX Hatchback được gắn đầu xe của Nissan Silvia S13 (đèn pha cố định).Sileighty cũng được góp mặt trong bộ truyện tranh và anime Initial D với nhân vật Mako Sato và Sayuki.', '2025-11-20 15:08:45'),
(15, 'Nissan', 'GTR R32 Skyline Cánh gió lớn', '1:32', 290000.00, 'Đen', 45, 'nissan_gtr_r32_skyline_upgrade.jpg', 'R32 đánh dấu sự trở lại của tên gọi GT-R sau 16 năm vắng bóng. Nissan đã quyết tâm tạo ra chiếc xe thống trị các giải đua Group A.Chiếc xe được mệnh danh là \"Godzilla\" (quái vật từ Nhật Bản) bởi tạp chí Wheels của Úc sau khi nó hoàn toàn áp đảo các đối thủ tại giải đua ATCC (Australian Touring Car Championship). R32 đã bất bại trong 29 chặng đua liên tiếp!', '2025-11-20 15:11:50'),
(16, 'Nissan', 'GTR R32 Skyline ', '1:32', 290000.00, 'Đen', 43, 'nissan_gtr_r32_skyline.jpg', 'R32 đánh dấu sự trở lại của tên gọi GT-R sau 16 năm vắng bóng. Nissan đã quyết tâm tạo ra chiếc xe thống trị các giải đua Group A.Chiếc xe được mệnh danh là \"Godzilla\" (quái vật từ Nhật Bản) bởi tạp chí Wheels của Úc sau khi nó hoàn toàn áp đảo các đối thủ tại giải đua ATCC (Australian Touring Car Championship). R32 đã bất bại trong 29 chặng đua liên tiếp!', '2025-11-20 15:11:50'),
(17, 'Nissan', 'GTR R34 Skyline ', '1:32', 300000.00, 'Xanh Dương Bạc', 30, 'nissan_gtr_r34_skyline.jpg', 'Nissan Skyline GT-R R34 (1999–2002) có lẽ là chiếc xe mang tính biểu tượng và được khao khát nhất trong toàn bộ dòng xe GT-R. Nó đại diện cho sự kết hợp hoàn hảo giữa phong cách thiết kế mạnh mẽ, công nghệ tiên tiến, và sự thống trị về hiệu suất.Danh tiếng của R34 được củng cố vững chắc nhờ sự xuất hiện nổi bật trong các trò chơi điện tử (như Gran Turismo) và đặc biệt là bộ phim \"The Fast and the Furious\" (với nhân vật Brian O\'Conner).', '2025-11-20 15:15:17'),
(18, 'Nissan', '1972 Skyline ', '1:32', 220000.00, 'Trắng', 50, 'nissan_1972_skyline.jpg', 'Nissan Skyline 2000 GT-R (Dựa trên khung gầm C10 của Skyline).\"Hakosuka\": Đây là biệt danh được người hâm mộ Nhật Bản đặt cho xe. Nó là sự kết hợp của hai từ:\r\nHako (ハコ): nghĩa là \"Hộp\" (ám chỉ hình dáng vuông vức của xe).Suka (スカ): là viết tắt của \"Skyline\".Ý nghĩa: \"Hộp Skyline\"–mô tả hoàn hảo hình dáng cổ điển, góc cạnh của chiếc xe.', '2025-11-20 15:15:17'),
(19, 'Nissan', '350Z ', '1:32', 300000.00, 'Đỏ', 30, 'nissan_350z_red.jpg', 'Nissan 350Z (có tên mã là Z33) là một chiếc xe rất quan trọng đối với Nissan, đánh dấu sự hồi sinh của dòng xe thể thao Z-car huyền thoại sau khi chiếc 300ZX (Z32) ngừng sản xuất. Nó là một chiếc xe thể thao thuần túy, mang lại hiệu suất tốt với mức giá phải chăng.Nissan 350Z là một chiếc coupe thể thao cổ điển của thế kỷ 21. Nó đã thành công trong việc kết hợp thiết kế cuốn hút, động cơ V6 mạnh mẽ (VQ35), và khả năng xử lý RWD tuyệt vời, giúp dòng xe Z-car lấy lại vị thế là một trong những chiếc xe thể thao JDM đáng khao khát nhất.', '2025-11-20 15:19:10'),
(20, 'Nissan', '350Z ', '1:32', 300000.00, 'Vàng ', 50, 'nissan_350z_yellow.jpg', 'Nissan 350Z (có tên mã là Z33) là một chiếc xe rất quan trọng đối với Nissan, đánh dấu sự hồi sinh của dòng xe thể thao Z-car huyền thoại sau khi chiếc 300ZX (Z32) ngừng sản xuất. Nó là một chiếc xe thể thao thuần túy, mang lại hiệu suất tốt với mức giá phải chăng.Nissan 350Z là một chiếc coupe thể thao cổ điển của thế kỷ 21. Nó đã thành công trong việc kết hợp thiết kế cuốn hút, động cơ V6 mạnh mẽ (VQ35), và khả năng xử lý RWD tuyệt vời, giúp dòng xe Z-car lấy lại vị thế là một trong những chiếc xe thể thao JDM đáng khao khát nhất.', '2025-11-20 15:19:10'),
(21, 'Nissan', '350Z ', '1:32', 300000.00, 'Đen', 30, 'nissan_350z_black.jpg', 'Nissan 350Z (có tên mã là Z33) là một chiếc xe rất quan trọng đối với Nissan, đánh dấu sự hồi sinh của dòng xe thể thao Z-car huyền thoại sau khi chiếc 300ZX (Z32) ngừng sản xuất. Nó là một chiếc xe thể thao thuần túy, mang lại hiệu suất tốt với mức giá phải chăng.Nissan 350Z là một chiếc coupe thể thao cổ điển của thế kỷ 21. Nó đã thành công trong việc kết hợp thiết kế cuốn hút, động cơ V6 mạnh mẽ (VQ35), và khả năng xử lý RWD tuyệt vời, giúp dòng xe Z-car lấy lại vị thế là một trong những chiếc xe thể thao JDM đáng khao khát nhất.', '2025-11-20 15:21:33'),
(22, 'Nissan', '180SX ', '1:32', 300000.00, 'Trắng', 46, 'nissan_180sx.jpg', 'Nissan 180SX là một chiếc xe rất nổi tiếng, đặc biệt trong cộng đồng drift, và là phiên bản hatchback (mũi gấp) của chiếc Silvia S13 tại thị trường Nhật Bản.Nó được tôn sùng vì tính thẩm mỹ JDM cổ điển, độ bền cơ khí và khả năng điều khiển tuyệt vời, là một trong những biểu tượng vĩ đại nhất của lịch sử xe drift.', '2025-11-20 15:21:33'),
(23, 'Mitsubishi', 'Lancer Evo IV ', '1:32', 300000.00, 'Trắng', 46, 'mitsubishi_lancer_evo_iv_white.jpg', 'Evo IV là một bước nhảy vọt về kỹ thuật và thiết kế, vì nó được phát triển dựa trên khung gầm Lancer hoàn toàn mới.Động cơ 4G63T được tinh chỉnh lại, đặt xoay 180 độ (đổi vị trí turbo và ống xả), giúp cải thiện sự cân bằng của xe.Công suất được đẩy lên mức giới hạn thỏa thuận 280 PS (~276 HP).', '2025-11-20 15:32:15'),
(24, 'Mitsubishi', 'Lancer Evo VI ', '1:32', 300000.00, 'Đỏ', 40, 'mitsubishi_lancer_evo_vi_red.jpg', 'Evo VI là thế hệ được coi là đỉnh cao của thiết kế Rally và là một trong những chiếc Evo được yêu thích nhất.Tiếp tục là 4G63T với công suất 280 PS.Cải tiến lớn nhất là pít-tông nhẹ hơn và hệ thống làm mát/độ bền được nâng cấp để chịu được cường độ đua Rally cao.', '2025-11-20 15:32:15'),
(25, 'Mitsubishi', 'Lancer Evo III ', '1:32', 300000.00, 'Đen', 45, 'mitsubishi_lancer_evo_iii_black.jpg', 'Evo III là phiên bản cuối cùng và hoàn thiện nhất của thế hệ Evo đầu tiên (bao gồm Evo I và II), được nhớ đến là chiếc xe đã mang lại danh hiệu vô địch WRC đầu tiên cho Tommi Mäkinen.Tiếp tục sử dụng động cơ huyền thoại 4G63T 2.0L, I4, Tăng áp.Công suất được tăng lên tới 270 PS (~266 HP), nhờ vào việc tăng tỷ số nén (compression ratio) và thiết kế turbocharger cải tiến.', '2025-11-20 15:32:15'),
(26, 'Mitsubishi', 'Lancer Evo III ', '1:32', 280000.00, 'Trắng', 50, 'mitsubishi_lancer_evo_iii_white.jpg', 'Evo III là phiên bản cuối cùng và hoàn thiện nhất của thế hệ Evo đầu tiên (bao gồm Evo I và II), được nhớ đến là chiếc xe đã mang lại danh hiệu vô địch WRC đầu tiên cho Tommi Mäkinen.Tiếp tục sử dụng động cơ huyền thoại 4G63T 2.0L, I4, Tăng áp.Công suất được tăng lên tới 270 PS (~266 HP), nhờ vào việc tăng tỷ số nén (compression ratio) và thiết kế turbocharger cải tiến.', '2025-11-20 15:32:15'),
(27, 'Mitsubishi', 'Eclipse ', '1:32', 300000.00, 'Xanh Lá Đen', 30, 'mitsubishi_eclipse.jpg', 'Mitsubishi Eclipse là một dòng xe thể thao compact coupe rất phổ biến, đặc biệt là ở thị trường Bắc Mỹ, và có vị thế quan trọng trong phân khúc xe thể thao giá cả phải chăng trong suốt những năm 1990 và 2000.Nó chuyển hướng sang dòng xe FWD Grand Tourer cho đến khi ngừng sản xuất. Dù vậy, Eclipse 1G và 2G vẫn giữ một vị trí đặc biệt trong lịch sử JDM và văn hóa độ xe.', '2025-11-20 15:41:34'),
(28, 'Mazda', 'RX7 Veilside Fortune ', '1:32', 350000.00, 'Cam Đen', 21, 'mazda_rx7_veilside.jpg', 'Mazda RX-7 VeilSide Fortune Huyền Thoại Điện Ảnh.Mazda RX-7 thế hệ thứ ba(FD3S), bản thân nó đã là một chiếc xe thể thao huyền thoại của Nhật Bản tạo ra bởi VeilSide, một hãng độ (tuning house) danh tiếng của Nhật Bản, chuyên về thân vỏ rộng (widebody) và hiệu suất cao.Tên gọi \"Fortune\": Là tên của bộ body kit (bộ thân vỏ) do VeilSide thiết kế. VeilSide Fortune là một body kit cực kỳ đắt tiền và hiếm, thay đổi gần như toàn bộ bề ngoài của RX-7 FD3S.Nó không chỉ là một chiếc xe hiệu suất cao mà còn là một tác phẩm độ xe mang tính văn hóa, nổi tiếng toàn cầu như là chiếc xe cam mang tính biểu tượng của Tokyo Drift.', '2025-11-20 15:41:34'),
(29, 'Mazda', 'RX7 FD ', '1:32', 300000.00, 'Vàng', 29, 'mazda_rx7_fd.jpg', 'FD3S là đỉnh cao của thiết kế RX-7 và được coi là một trong những chiếc xe đẹp nhất từng được sản xuất.FD là biểu tượng của công nghệ động cơ Rotary hiện đại và là một trong những chiếc xe JDM được săn lùng và có giá trị nhất, nổi tiếng về cả vẻ đẹp lẫn tiềm năng độ chế.', '2025-11-20 15:44:56'),
(30, 'Mazda', 'RX7 FD Cánh Gió Lớn', '1:32', 350000.00, 'Vàng', 20, 'mazda_rx7_fd_upgrade.jpg', 'FD3S là đỉnh cao của thiết kế RX-7 và được coi là một trong những chiếc xe đẹp nhất từng được sản xuất.FD là biểu tượng của công nghệ động cơ Rotary hiện đại và là một trong những chiếc xe JDM được săn lùng và có giá trị nhất, nổi tiếng về cả vẻ đẹp lẫn tiềm năng độ chế.', '2025-11-20 15:44:56'),
(31, 'Mazda', 'RX7 FC ', '1:32', 300000.00, 'Trắng', 19, 'mazda_rx7_fc.jpg', 'FC3S là thế hệ đã giúp RX-7 trở thành đối thủ cạnh tranh trực tiếp với các mẫu xe thể thao lớn của thế giới, đặc biệt là Porsche.FC là chiếc RX-7 đầu tiên đạt được danh tiếng đáng kể trong các giải đua chuyên nghiệp và là một nền tảng độ chế được yêu thích.', '2025-11-20 15:47:03'),
(32, 'Mazda', 'RX7 FC Cánh Gió Lớn', '1:32', 300000.00, 'Trắng ', 31, 'mazda_rx7_fc_upgrade.jpg', 'FC3S là thế hệ đã giúp RX-7 trở thành đối thủ cạnh tranh trực tiếp với các mẫu xe thể thao lớn của thế giới, đặc biệt là Porsche.FC là chiếc RX-7 đầu tiên đạt được danh tiếng đáng kể trong các giải đua chuyên nghiệp và là một nền tảng độ chế được yêu thích.', '2025-11-20 15:47:03'),
(33, 'Honda', 'NSX ', '1:32', 280000.00, 'Đỏ', 45, 'honda_nsx.jpg', 'Honda NSX là một trong những chiếc xe thể thao có ảnh hưởng nhất trong lịch sử ô tô, được biết đến là chiếc siêu xe Nhật Bản đầu tiên, chứng minh rằng siêu xe có thể vừa nhanh, vừa đáng tin cậy và dễ lái.Honda NSX là biểu tượng của tinh thần đổi mới. Thế hệ đầu tiên (NA1/NA2) định nghĩa lại sự đáng tin cậy của siêu xe, trong khi thế hệ thứ hai (NC1) thể hiện tương lai của siêu xe với công nghệ Hybrid và AWD tiên tiến.', '2025-11-20 15:50:12'),
(34, 'Honda', 'S2000 ', '1:32', 300000.00, 'Xanh Dương', 44, 'honda_s2000.jpg', 'Honda S2000 là một trong những chiếc roadster (xe mui trần hai chỗ) mang tính biểu tượng nhất của Honda, được phát triển để kỷ niệm 50 năm thành lập công ty. Nó nổi tiếng với triết lý kỹ thuật cực kỳ tiên tiến, đặc biệt là động cơ vòng tua máy siêu cao.Honda S2000 là một kiệt tác kỹ thuật. Nó nổi bật với động cơ VTEC 9000 RPM (trên AP1), khả năng xử lý sắc nét, và khung gầm siêu cứng, định nghĩa lại trải nghiệm lái xe mui trần hiệu suất cao.', '2025-11-20 15:50:12'),
(35, 'Honda', 'Civic EG6 Vtec ', '1:32', 300000.00, 'Đỏ', 44, 'honda_civic_eg6_vtec.jpg', 'EG6 là phiên bản hiệu suất cao của Civic thế hệ thứ năm (EG), được tôn sùng vì kiểu dáng mềm mại, nhẹ nhàng và khả năng xử lý tuyệt vời.B16A 1.6L, I4, DOHC VTEC.Sản sinh công suất khoảng 160 PS (158 HP).EG6 cực kỳ nhẹ (thường dưới 1.000 kg), kết hợp với hệ thống treo độc lập bốn bánh, mang lại cảm giác lái rất linh hoạt và trực tiếp.Nhiều người hâm mộ đánh giá cao EG6 vì sự đơn giản cơ học và khả năng độ chế dễ dàng, biến nó thành một chiếc xe đua đường phố hoặc đường đua rất hiệu quả.', '2025-11-20 15:56:02'),
(36, 'Honda', 'Civic Type R EK9 ', '1:32', 300000.00, 'Vàng', 45, 'honda_civic_typer_ek9.jpg', 'EK9 là chiếc Civic đầu tiên mang biểu tượng \"Type R\" (Racing) và được coi là định nghĩa cho triết lý xe thể thao FWD (Dẫn động cầu trước) của Honda.B16B 1.6L, I4, DOHC VTEC.Sản sinh 185 PS (182 HP) tại vòng tua máy cực cao, thường là 8,200 RPM.B16B là một phiên bản được tinh chỉnh thủ công từ B16A, với thanh truyền dài hơn, pít-tông nhẹ hơn và đầu xi-lanh được cân bằng hoàn hảo.Trang bị Bộ vi sai chống trượt Helical (LSD) tiêu chuẩn, giúp phân bổ lực kéo hiệu quả hơn nhiều, đặc biệt khi vào cua gắt, biến nó thành một cỗ máy xử lý FWD cực kỳ nhanh nhẹn.', '2025-11-20 15:56:02'),
(37, 'Toyota', 'Soarer ', '1:32', 200000.00, 'Đen', 50, '1764771344_1991-toyota-soarer-gz20-widebody.jpg', 'Phiên bản JDM của Lexus SC. Coupe thể thao hạng sang, nổi tiếng với động cơ 1JZ/2JZ.', '2025-12-03 14:15:44'),
(39, 'Toyota', 'Altezza ', '1:32', 200000.00, 'Trắng', 50, '1764771609_toyota_altezza.jpg', 'Phiên bản JDM của Lexus IS. Xe sedan thể thao nhỏ gọn.', '2025-12-03 14:20:09'),
(41, 'Nissan', 'Stagea ', '1:32', 200000.01, 'Trắng Bạc', 50, '1764771784_nissan_stagea_260rs.jpg', 'Mẫu xe Wagon (xe gia đình) nhưng có phiên bản sử dụng động cơ và hệ truyền động của Skyline GT-R (như Autech Stagea 260RS).', '2025-12-03 14:22:11'),
(43, 'Nissan', 'GTR SKYLINE R34 ', '1:32', 280000.00, 'Vàng', 41, '1764860488_nissan_gtr_skyline_r34_god_foot.jpg', 'Nissan R34 là xe có hệ dẫn động AWD, động cơ RB26DETT, và được tinh chỉnh để phù hợp với phong cách full-power, tận dụng lực chân ga kinh khủng của God Foot.God Foot là biệt danh của Kozo Hoshino – một tay đua cực mạnh thuộc nhóm Thần Bàn Đạp (God Hand & God Foot)..Trong series, chiếc R34 này nổi tiếng vì khả năng tăng tốc cực mạnh và ra vào cua với AWD ổn định, đối đầu với Takumi và Bunta.', '2025-12-04 15:01:28'),
(44, 'Honda', 'Neo CRX', '1:64', 250000.00, 'Xanh Dương', 50, '1765371892_neo_honda_crx_del_1992.jpg', 'Không có thông tin ', '2025-12-10 13:04:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rating` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `comment`, `created_at`, `rating`) VALUES
(2, 31, 3, 'Quá đẹp,hoàn mỹ ,chuẩn phong cách của Ryosuke Takahashi', '2025-12-04 14:26:53', 10),
(3, 31, 4, 'Đẹp', '2025-12-04 14:51:00', 10),
(4, 44, 3, 'Kiểu dáng không đẹp lắm ', '2025-12-10 13:11:44', 8),
(5, 44, 4, 'Đẹp ', '2025-12-10 13:13:23', 9);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'baobao', 'binkongu24@gmail.com', '$2y$10$Mal3J9HFuV7X3BOYfh1h0uAUxedkLe/a5cvkedw/5E.9EJcllzVsW', '0378884120', 'Đồng Nai', 'admin', '2025-11-19 15:00:15'),
(3, 'nganngan', 'tieubaobao305@gmail.com', '$2y$10$PEdUNKPO/8AnpqHimGC4Ku31uE/lO5FW9I8rZIKSKYdOTFXIibTJO', '0814145255', 'Đắk Nông', 'customer', '2025-11-20 10:51:37'),
(4, 'hoangbao', 'dh52200381@student.stu.edu.vn', '$2y$10$ajsjMfk1de3/9XPWQiVxwum/w.OdwYNTtW3yCRFnNHkdtevDqk9RW', '0326745747', 'Đồng Nai', 'customer', '2025-12-04 14:50:25'),
(9, 'tiennguyenvuthuy', 'tiennguyenvuthuy@gmail.com', '$2y$10$ACn.DwoJKgoskkoNJ9eOCOd6S2OVxuB8/hUI1M0aHinEMgOWfD97.', '0906498073', 'TP Hồ Chí Minh', 'customer', '2025-12-09 06:33:33'),
(10, 'tientien', 'tiennguyen@gmail.com', '$2y$10$cvTed2Z9QTj1AkirNiSdB.rRBZ.lC9kWPDuMosZl.1yL7GkZF2Kxm', '0378884120', 'Quận 8, TP Hồ Chí Minh', 'customer', '2025-12-10 12:56:03');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
