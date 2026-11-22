-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2026 at 09:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `freelance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bang_chung_khieu_nai`
--

CREATE TABLE `bang_chung_khieu_nai` (
  `BCKN_ma` int(11) NOT NULL,
  `KhieuNai_ma` int(11) NOT NULL,
  `BCKN_tentep` varchar(200) NOT NULL,
  `BCKN_duongdan` varchar(255) NOT NULL,
  `BCKN_dinhdangtep` varchar(30) DEFAULT NULL,
  `BCKN_ngaytailen` datetime DEFAULT current_timestamp(),
  `BCKN_noidung` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ban_giao_san_pham`
--

CREATE TABLE `ban_giao_san_pham` (
  `BG_ma` int(11) NOT NULL,
  `MS_ma` int(11) NOT NULL,
  `BG_ngaybangiao` datetime DEFAULT current_timestamp(),
  `BG_mota` text DEFAULT NULL,
  `BG_trangthai` varchar(30) DEFAULT 'Đã nộp bài'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `ban_giao_san_pham`
--

INSERT INTO `ban_giao_san_pham` (`BG_ma`, `MS_ma`, `BG_ngaybangiao`, `BG_mota`, `BG_trangthai`) VALUES
(1, 1, '2026-11-22 12:32:46', 'Đính kèm 5 bản phác thảo ý tưởng định dạng PDF độ phân giải cao.', 'Đã nghiệm thu'),
(2, 2, '2026-11-22 12:32:46', 'Gói minh họa bản nháp 1 đã sẵn sàng để khách hàng kiểm tra.', 'Đã nộp bài');

-- --------------------------------------------------------

--
-- Table structure for table `danh_gia`
--

CREATE TABLE `danh_gia` (
  `DG_ma` int(11) NOT NULL,
  `HD_ma` int(11) NOT NULL,
  `ND_ma` int(11) NOT NULL,
  `DG_diem` int(11) DEFAULT NULL CHECK (`DG_diem` between 1 and 5),
  `DG_noidung` text DEFAULT NULL,
  `DG_ngaydanhgia` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `danh_muc`
--

CREATE TABLE `danh_muc` (
  `DM_ma` int(11) NOT NULL,
  `DM_ten` varchar(100) NOT NULL,
  `DM_mota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `danh_muc`
--

INSERT INTO `danh_muc` (`DM_ma`, `DM_ten`, `DM_mota`) VALUES
(1, 'Digital Marketing', 'SEO, Mạng xã hội, Tiếp thị nội dung'),
(2, 'Thiết kế & Đồ họa', 'Poster, Bộ nhận diện thương hiệu, Minh họa UI/UX'),
(3, 'Lập trình & Công nghệ', 'Phát triển Web, Smart Contract, PHP/MySQL'),
(4, 'Viết lách & Dịch thuật', 'Viết bài quảng cáo, Tài liệu kỹ thuật');

-- --------------------------------------------------------

--
-- Table structure for table `de_xuat`
--

CREATE TABLE `de_xuat` (
  `DX_ma` int(11) NOT NULL,
  `DA_ma` int(11) NOT NULL,
  `ND_ma` int(11) NOT NULL,
  `DX_ngaygui` datetime DEFAULT current_timestamp(),
  `DX_gia` decimal(12,2) NOT NULL,
  `DX_thoigianhoanthanh` varchar(100) DEFAULT NULL,
  `DX_noidung` text DEFAULT NULL,
  `DX_trangthai` varchar(30) DEFAULT 'Chờ duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `de_xuat`
--

INSERT INTO `de_xuat` (`DX_ma`, `DA_ma`, `ND_ma`, `DX_ngaygui`, `DX_gia`, `DX_thoigianhoanthanh`, `DX_noidung`, `DX_trangthai`) VALUES
(1, 1, 2, '2026-11-22 12:32:46', 250.00, '14 ngày', 'Tôi sẽ bàn giao 5 bản phác thảo ý tưởng trước, sau đó hoàn thiện màu sắc và xuất file nguồn PNG.', 'Đã chấp nhận');

-- --------------------------------------------------------

--
-- Table structure for table `du_an`
--

CREATE TABLE `du_an` (
  `DA_ma` int(11) NOT NULL,
  `ND_ma` int(11) NOT NULL,
  `DM_ma` int(11) NOT NULL,
  `DA_ten` varchar(200) NOT NULL,
  `DA_mota` text DEFAULT NULL,
  `DA_ngansach` decimal(12,2) NOT NULL,
  `DA_ngaydang` datetime DEFAULT current_timestamp(),
  `DA_hannop` date DEFAULT NULL,
  `DA_trangthai` varchar(30) DEFAULT 'Đang mở'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `du_an`
--

INSERT INTO `du_an` (`DA_ma`, `ND_ma`, `DM_ma`, `DA_ten`, `DA_mota`, `DA_ngansach`, `DA_ngaydang`, `DA_hannop`, `DA_trangthai`) VALUES
(1, 1, 2, 'Thiết kế Banner Website Minh họa Độc quyền', 'Cần bản phác thảo ý tưởng, bản nháp 1, minh họa hoàn chỉnh và bàn giao file PNG/SVG.', 250.00, '2026-11-22 12:32:46', '2026-12-15', 'Đang thực hiện'),
(2, 1, 1, 'Cần Chuyên gia SEO cho Doanh nghiệp Tài chính', 'Thực hiện kiểm toán SEO toàn diện, nghiên cứu từ khóa và xây dựng liên kết chất lượng cao.', 2000.00, '2026-11-22 12:32:46', '2026-12-30', 'Đang mở');

-- --------------------------------------------------------

--
-- Table structure for table `du_an_ky_nang`
--

CREATE TABLE `du_an_ky_nang` (
  `DAKN_ma` int(11) NOT NULL,
  `DA_ma` int(11) NOT NULL,
  `KN_ma` int(11) NOT NULL,
  `DAKN_ghichu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `du_an_ky_nang`
--

INSERT INTO `du_an_ky_nang` (`DAKN_ma`, `DA_ma`, `KN_ma`, `DAKN_ghichu`) VALUES
(1, 1, 5, 'Bắt buộc'),
(2, 2, 3, 'Bắt buộc');

-- --------------------------------------------------------

--
-- Table structure for table `giao_dich_blockchain`
--

CREATE TABLE `giao_dich_blockchain` (
  `GDB_ma` int(11) NOT NULL,
  `TToan_ma` int(11) NOT NULL,
  `GDB_hash` varchar(100) NOT NULL,
  `GDB_vigui` varchar(100) NOT NULL,
  `GDB_vinhan` varchar(100) NOT NULL,
  `GDB_sotien` decimal(12,2) NOT NULL,
  `GDB_phigiaodich` decimal(10,6) DEFAULT 0.000420,
  `GDB_khoiblock` int(11) NOT NULL,
  `GDB_trangthai` varchar(30) DEFAULT 'Đã xác nhận'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `giao_dich_blockchain`
--

INSERT INTO `giao_dich_blockchain` (`GDB_ma`, `TToan_ma`, `GDB_hash`, `GDB_vigui`, `GDB_vinhan`, `GDB_sotien`, `GDB_phigiaodich`, `GDB_khoiblock`, `GDB_trangthai`) VALUES
(1, 1, '0x000000658a822dee1c8b1e5c355da07c798d14a0301ac46988a412d179d03fac', '0x8b9605af2d460dd1018a0d5048fbb53afffda29b', '0x3d31aa949ba85ea2bba1d2ce0c8db5bc0a90b1fa', 50.00, 0.000420, 92328, 'Đã xác nhận'),
(2, 2, '0x09c18d205d14b7b353014d2588650f783c3619ebc1b6bfbbca1b61a132ecccca', '0x4cB95a9E6b065343Be6b3F12738A94b5Ef1b82E3', '0x5FbDB2315678afecb367f032d93F642f64180aa3', 100.00, 0.000084, 92329, 'Đã xác nhận');

-- --------------------------------------------------------

--
-- Table structure for table `hop_dong`
--

CREATE TABLE `hop_dong` (
  `HD_ma` int(11) NOT NULL,
  `DX_ma` int(11) NOT NULL,
  `HD_ngayky` date DEFAULT curdate(),
  `HD_ngaybatdau` date DEFAULT NULL,
  `HD_ngayketthuc` date DEFAULT NULL,
  `HD_tonggiatri` decimal(12,2) NOT NULL,
  `HD_dieukhoan` text DEFAULT NULL,
  `HD_trangthai` varchar(30) DEFAULT 'Đang thực hiện'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `hop_dong`
--

INSERT INTO `hop_dong` (`HD_ma`, `DX_ma`, `HD_ngayky`, `HD_ngaybatdau`, `HD_ngayketthuc`, `HD_tonggiatri`, `HD_dieukhoan`, `HD_trangthai`) VALUES
(1, 1, '2026-11-22', '2026-11-01', '2026-12-15', 250.00, 'Được bảo vệ bởi Cơ chế Ký quỹ (Escrow). Tiền được giải ngân theo từng cột mốc nghiệm thu.', 'Đang thực hiện');

-- --------------------------------------------------------

--
-- Table structure for table `ho_so_freelancer`
--

CREATE TABLE `ho_so_freelancer` (
  `HS_ma` int(11) NOT NULL,
  `HS_nguoidung` int(11) NOT NULL,
  `HS_tieude` varchar(200) DEFAULT NULL,
  `HS_gioithieu` text DEFAULT NULL,
  `HS_kinhnghiem` text DEFAULT NULL,
  `HS_hocvan` text DEFAULT NULL,
  `HS_mucluongmongmuon` decimal(12,2) DEFAULT 0.00,
  `HS_duongdan` varchar(255) DEFAULT NULL,
  `HS_ngaycapnhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `ho_so_freelancer`
--

INSERT INTO `ho_so_freelancer` (`HS_ma`, `HS_nguoidung`, `HS_tieude`, `HS_gioithieu`, `HS_kinhnghiem`, `HS_hocvan`, `HS_mucluongmongmuon`, `HS_duongdan`, `HS_ngaycapnhat`) VALUES
(1, 2, 'Chuyên gia Thiết kế Thương hiệu & SEO', 'Hơn 5 năm kinh nghiệm thiết kế minh họa website, poster A2 và triển khai các chiến dịch SEO kỹ thuật.', 'Trưởng nhóm Thiết kế tại StudioX (2021-2026)', 'Cử nhân Thiết kế Truyền thông Đa phương tiện', 45.00, 'https://portfolio.tranminh.demo', '2026-11-22 12:32:46');

-- --------------------------------------------------------

--
-- Table structure for table `ho_so_ky_nang`
--

CREATE TABLE `ho_so_ky_nang` (
  `HSKN_ma` int(11) NOT NULL,
  `HS_ma` int(11) NOT NULL,
  `KN_ma` int(11) NOT NULL,
  `HSKN_mucdothanhthao` varchar(50) DEFAULT 'Trung cấp',
  `HSKN_sonamkinhnghiem` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `ho_so_ky_nang`
--

INSERT INTO `ho_so_ky_nang` (`HSKN_ma`, `HS_ma`, `KN_ma`, `HSKN_mucdothanhthao`, `HSKN_sonamkinhnghiem`) VALUES
(1, 1, 1, 'Chuyên gia', 5),
(2, 1, 2, 'Chuyên gia', 4),
(3, 1, 5, 'Chuyên gia', 5);

-- --------------------------------------------------------

--
-- Table structure for table `khieu_nai`
--

CREATE TABLE `khieu_nai` (
  `KhieuNai_ma` int(11) NOT NULL,
  `HD_ma` int(11) NOT NULL,
  `ND_ma` int(11) NOT NULL,
  `KhieuNai_noidung` text NOT NULL,
  `KhieuNai_lydo` varchar(200) DEFAULT NULL,
  `KhieuNai_ngay` datetime DEFAULT current_timestamp(),
  `KhieuNai_ketquaxuly` text DEFAULT NULL,
  `KhieuNai_trangthai` varchar(50) DEFAULT 'Bước 1: Đã gửi khiếu nại'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ky_nang`
--

CREATE TABLE `ky_nang` (
  `KN_ma` int(11) NOT NULL,
  `KN_ten` varchar(100) NOT NULL,
  `KN_mota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `ky_nang`
--

INSERT INTO `ky_nang` (`KN_ma`, `KN_ten`, `KN_mota`) VALUES
(1, 'Canva', 'Công cụ thiết kế đồ họa'),
(2, 'Viết nội dung Website', 'Biên tập nội dung thu hút khách hàng'),
(3, 'Tối ưu SEO Backlink', 'Tối ưu hóa công cụ tìm kiếm Off-page'),
(4, 'PHP & MySQL', 'Phát triển hệ thống Backend toàn diện'),
(5, 'Minh họa UI/UX', 'Thiết kế đồ họa vector và giao diện');

-- --------------------------------------------------------

--
-- Table structure for table `ky_quy`
--

CREATE TABLE `ky_quy` (
  `KQ_ma` int(11) NOT NULL,
  `MS_ma` int(11) NOT NULL,
  `KQ_sotien` decimal(12,2) NOT NULL,
  `KQ_ngay` datetime DEFAULT current_timestamp(),
  `KQ_ngaygiaingan` datetime DEFAULT NULL,
  `KQ_trangthai` varchar(30) DEFAULT 'Đã ký quỹ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `ky_quy`
--

INSERT INTO `ky_quy` (`KQ_ma`, `MS_ma`, `KQ_sotien`, `KQ_ngay`, `KQ_ngaygiaingan`, `KQ_trangthai`) VALUES
(1, 1, 50.00, '2026-11-22 12:32:46', '2026-11-11 10:00:00', 'Đã giải ngân'),
(2, 2, 100.00, '2026-11-22 12:32:46', '2026-11-22 12:43:59', 'Đã giải ngân');

-- --------------------------------------------------------

--
-- Table structure for table `milestone`
--

CREATE TABLE `milestone` (
  `MS_ma` int(11) NOT NULL,
  `HD_ma` int(11) NOT NULL,
  `MS_ten` varchar(200) NOT NULL,
  `MS_mota` text DEFAULT NULL,
  `MS_sotien` decimal(12,2) NOT NULL,
  `MS_hanhoanthanh` date DEFAULT NULL,
  `MS_trangthai` varchar(30) DEFAULT 'Chờ ký quỹ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `milestone`
--

INSERT INTO `milestone` (`MS_ma`, `HD_ma`, `MS_ten`, `MS_mota`, `MS_sotien`, `MS_hanhoanthanh`, `MS_trangthai`) VALUES
(1, 1, '1. Phác thảo ý tưởng (Tổng 5 bản phác thảo)', 'Bố cục khung và 2-3 ý tưởng nhân vật ban đầu', 50.00, '2026-11-10', 'Đã giải ngân'),
(2, 1, '2. Bản nháp minh họa lần 1', 'Phối màu vector hoàn chỉnh để khách hàng phản hồi', 100.00, '2026-11-25', 'Đã giải ngân'),
(3, 1, '3. Bàn giao file hoàn chỉnh (PNG/SVG)', 'Tài nguyên đồ họa độ phân giải cao', 100.00, '2026-12-15', 'Chờ ký quỹ');

-- --------------------------------------------------------

--
-- Table structure for table `nghiem_thu`
--

CREATE TABLE `nghiem_thu` (
  `NT_ma` int(11) NOT NULL,
  `BG_ma` int(11) NOT NULL,
  `NT_ngaynghiemthu` datetime DEFAULT current_timestamp(),
  `NT_ketqua` varchar(50) DEFAULT 'Đã nghiệm thu',
  `NT_ghichu` text DEFAULT NULL,
  `NT_trangthai` varchar(30) DEFAULT 'Hoàn thành'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `nghiem_thu`
--

INSERT INTO `nghiem_thu` (`NT_ma`, `BG_ma`, `NT_ngaynghiemthu`, `NT_ketqua`, `NT_ghichu`, `NT_trangthai`) VALUES
(1, 1, '2026-11-22 12:32:46', 'Đã nghiệm thu', 'Bố cục rõ ràng, đồng ý giải ngân cột mốc 1.', 'Hoàn thành');

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `ND_ma` int(11) NOT NULL,
  `VT_ma` int(11) NOT NULL,
  `ND_ho` varchar(100) NOT NULL,
  `ND_ten` varchar(100) NOT NULL,
  `ND_Email` varchar(150) NOT NULL,
  `ND_matkhau` varchar(255) NOT NULL,
  `ND_SDT` varchar(20) DEFAULT NULL,
  `ND_diachi` varchar(255) DEFAULT NULL,
  `ND_anhdaidien` varchar(255) DEFAULT 'https://ui-avatars.com/api/?background=108a00&color=fff&name=User',
  `ND_ngaytao` datetime DEFAULT current_timestamp(),
  `ND_trangthai` varchar(30) DEFAULT 'Hoạt động',
  `ND_vi_blockchain` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`ND_ma`, `VT_ma`, `ND_ho`, `ND_ten`, `ND_Email`, `ND_matkhau`, `ND_SDT`, `ND_diachi`, `ND_anhdaidien`, `ND_ngaytao`, `ND_trangthai`, `ND_vi_blockchain`) VALUES
(1, 1, 'Nguyễn Văn', 'An', 'client@demo.com', '123456', '0901234567', 'Quận 1, TP. Hồ Chí Minh', 'https://ui-avatars.com/api/?background=0f172a&color=fff&name=Van+An', '2026-11-22 12:32:46', 'Hoạt động', NULL),
(2, 2, 'Trần Thị', 'Minh', 'freelancer@demo.com', '123456', '0907654321', 'Quận Cầu Giấy, Hà Nội', 'https://ui-avatars.com/api/?background=108a00&color=fff&name=Thi+Minh', '2026-11-22 12:32:46', 'Hoạt động', NULL),
(3, 3, 'Quản trị', 'Hệ thống', 'admin@demo.com', '123456', '0909999999', 'Trụ sở chính', 'https://ui-avatars.com/api/?background=1e293b&color=fff&name=Admin', '2026-11-22 12:32:46', 'Hoạt động', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nhat_ky_he_thong`
--

CREATE TABLE `nhat_ky_he_thong` (
  `NKHT_ma` int(11) NOT NULL,
  `ND_ma` int(11) DEFAULT NULL,
  `NKHT_hanhdong` varchar(100) NOT NULL,
  `NKHT_doituong` varchar(100) DEFAULT NULL,
  `NKHT_mota` text DEFAULT NULL,
  `NKHT_diachiIP` varchar(45) DEFAULT NULL,
  `NKHT_thietbi` varchar(255) DEFAULT NULL,
  `NKHT_thoigian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `nhat_ky_he_thong`
--

INSERT INTO `nhat_ky_he_thong` (`NKHT_ma`, `ND_ma`, `NKHT_hanhdong`, `NKHT_doituong`, `NKHT_mota`, `NKHT_diachiIP`, `NKHT_thietbi`, `NKHT_thoigian`) VALUES
(1, 1, 'ĐĂNG NHẬP', 'NGUOI_DUNG', 'Người dùng client@demo.com đã đăng nhập với vai trò Khách hàng', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-22 12:38:14'),
(2, 1, 'ĐĂNG NHẬP', 'NGUOI_DUNG', 'Người dùng client@demo.com đã đăng nhập với vai trò Khách hàng', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-22 12:43:34'),
(3, 1, 'Cập nhật', 'THANH_TOAN', 'Giải ngân ký quỹ cho cột mốc #2 (Tx: 0x09c18d205d14b7b353014d2588650f783c3619ebc1b6bfbbca1b61a132ecccca | Block: #92329)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-22 12:43:59'),
(4, 1, 'BENCHMARK', 'GIAO_DICH_BLOCKCHAIN', 'Chạy thực nghiệm đo hiệu năng (20, 50, 100 tx) trên Chain ID 1337 & 1338', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-22 12:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `PF_ma` int(11) NOT NULL,
  `HS_ma` int(11) NOT NULL,
  `PF_ten` varchar(200) NOT NULL,
  `PF_mota` text DEFAULT NULL,
  `PF_hinhanh` varchar(255) DEFAULT NULL,
  `PF_duongdan` varchar(255) DEFAULT NULL,
  `PF_ngaydang` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `portfolio`
--

INSERT INTO `portfolio` (`PF_ma`, `HS_ma`, `PF_ten`, `PF_mota`, `PF_hinhanh`, `PF_duongdan`, `PF_ngaydang`) VALUES
(1, 1, 'Thiết kế Poster A2 Hiện đại cho Pidgeon', 'Bố cục poster sự kiện doanh nghiệp tối giản và in ấn chất lượng cao.', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600', 'https://behance.net/demo-poster', '2026-11-22');

-- --------------------------------------------------------

--
-- Table structure for table `tep_tin`
--

CREATE TABLE `tep_tin` (
  `TT_ma` int(11) NOT NULL,
  `BG_ma` int(11) NOT NULL,
  `TT_ten` varchar(200) NOT NULL,
  `TT_duongdan` varchar(255) NOT NULL,
  `TT_dinhdang` varchar(20) DEFAULT NULL,
  `TT_kichthuoc` varchar(30) DEFAULT NULL,
  `TT_ngaytailen` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `tep_tin`
--

INSERT INTO `tep_tin` (`TT_ma`, `BG_ma`, `TT_ten`, `TT_duongdan`, `TT_dinhdang`, `TT_kichthuoc`, `TT_ngaytailen`) VALUES
(1, 1, 'ban_phac_thao_y_tuong_v1.pdf', 'uploads/ban_phac_thao_y_tuong_v1.pdf', 'PDF', '3.8 MB', '2026-11-22 12:32:46'),
(2, 2, 'ban_nhap_1_banner_preview.png', 'uploads/ban_nhap_1_banner_preview.png', 'PNG', '5.2 MB', '2026-11-22 12:32:46');

-- --------------------------------------------------------

--
-- Table structure for table `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `TToan_ma` int(11) NOT NULL,
  `KQ_ma` int(11) NOT NULL,
  `TToan_sotien` decimal(12,2) NOT NULL,
  `TToan_phuongthuc` varchar(50) DEFAULT 'Thẻ Visa đuôi 1111',
  `TToan_loaigiaodich` varchar(50) DEFAULT 'Giải ngân ký quỹ',
  `TToan_ngay` datetime DEFAULT current_timestamp(),
  `TToan_trangthai` varchar(30) DEFAULT 'Hoàn tất'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `thanh_toan`
--

INSERT INTO `thanh_toan` (`TToan_ma`, `KQ_ma`, `TToan_sotien`, `TToan_phuongthuc`, `TToan_loaigiaodich`, `TToan_ngay`, `TToan_trangthai`) VALUES
(1, 1, 50.00, 'Thẻ Visa đuôi 1111', 'Giải ngân ký quỹ', '2026-11-22 12:32:46', 'Hoàn tất'),
(2, 2, 100.00, 'Hợp đồng thông minh Escrow', 'Giải ngân ký quỹ', '2026-11-22 12:43:59', 'Hoàn tất');

-- --------------------------------------------------------

--
-- Table structure for table `vai_tro`
--

CREATE TABLE `vai_tro` (
  `VT_ma` int(11) NOT NULL,
  `VT_ten` varchar(50) NOT NULL,
  `VT_mota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Dumping data for table `vai_tro`
--

INSERT INTO `vai_tro` (`VT_ma`, `VT_ten`, `VT_mota`) VALUES
(1, 'Khách hàng', 'Chủ dự án / Người thuê Freelancer'),
(2, 'Freelancer', 'Chuyên gia cung cấp dịch vụ'),
(3, 'Quản trị viên', 'Quản lý hệ thống & Hòa giải tranh chấp');

-- --------------------------------------------------------

--
-- Table structure for table `yeu_cau_chinh_sua`
--

CREATE TABLE `yeu_cau_chinh_sua` (
  `YC_ma` int(11) NOT NULL,
  `BG_ma` int(11) NOT NULL,
  `YC_noidung` text NOT NULL,
  `YC_ngayyeucau` datetime DEFAULT current_timestamp(),
  `YC_hanchinhsua` date DEFAULT NULL,
  `YC_trangthai` varchar(30) DEFAULT 'Đang mở'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bang_chung_khieu_nai`
--
ALTER TABLE `bang_chung_khieu_nai`
  ADD PRIMARY KEY (`BCKN_ma`),
  ADD KEY `KhieuNai_ma` (`KhieuNai_ma`);

--
-- Indexes for table `ban_giao_san_pham`
--
ALTER TABLE `ban_giao_san_pham`
  ADD PRIMARY KEY (`BG_ma`),
  ADD KEY `MS_ma` (`MS_ma`);

--
-- Indexes for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`DG_ma`),
  ADD KEY `HD_ma` (`HD_ma`),
  ADD KEY `ND_ma` (`ND_ma`);

--
-- Indexes for table `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`DM_ma`);

--
-- Indexes for table `de_xuat`
--
ALTER TABLE `de_xuat`
  ADD PRIMARY KEY (`DX_ma`),
  ADD KEY `DA_ma` (`DA_ma`),
  ADD KEY `ND_ma` (`ND_ma`);

--
-- Indexes for table `du_an`
--
ALTER TABLE `du_an`
  ADD PRIMARY KEY (`DA_ma`),
  ADD KEY `ND_ma` (`ND_ma`),
  ADD KEY `DM_ma` (`DM_ma`);

--
-- Indexes for table `du_an_ky_nang`
--
ALTER TABLE `du_an_ky_nang`
  ADD PRIMARY KEY (`DAKN_ma`),
  ADD KEY `DA_ma` (`DA_ma`),
  ADD KEY `KN_ma` (`KN_ma`);

--
-- Indexes for table `giao_dich_blockchain`
--
ALTER TABLE `giao_dich_blockchain`
  ADD PRIMARY KEY (`GDB_ma`),
  ADD KEY `TToan_ma` (`TToan_ma`);

--
-- Indexes for table `hop_dong`
--
ALTER TABLE `hop_dong`
  ADD PRIMARY KEY (`HD_ma`),
  ADD UNIQUE KEY `DX_ma` (`DX_ma`);

--
-- Indexes for table `ho_so_freelancer`
--
ALTER TABLE `ho_so_freelancer`
  ADD PRIMARY KEY (`HS_ma`),
  ADD UNIQUE KEY `HS_nguoidung` (`HS_nguoidung`);

--
-- Indexes for table `ho_so_ky_nang`
--
ALTER TABLE `ho_so_ky_nang`
  ADD PRIMARY KEY (`HSKN_ma`),
  ADD KEY `HS_ma` (`HS_ma`),
  ADD KEY `KN_ma` (`KN_ma`);

--
-- Indexes for table `khieu_nai`
--
ALTER TABLE `khieu_nai`
  ADD PRIMARY KEY (`KhieuNai_ma`),
  ADD KEY `HD_ma` (`HD_ma`),
  ADD KEY `ND_ma` (`ND_ma`);

--
-- Indexes for table `ky_nang`
--
ALTER TABLE `ky_nang`
  ADD PRIMARY KEY (`KN_ma`),
  ADD UNIQUE KEY `KN_ten` (`KN_ten`);

--
-- Indexes for table `ky_quy`
--
ALTER TABLE `ky_quy`
  ADD PRIMARY KEY (`KQ_ma`),
  ADD UNIQUE KEY `MS_ma` (`MS_ma`);

--
-- Indexes for table `milestone`
--
ALTER TABLE `milestone`
  ADD PRIMARY KEY (`MS_ma`),
  ADD KEY `HD_ma` (`HD_ma`);

--
-- Indexes for table `nghiem_thu`
--
ALTER TABLE `nghiem_thu`
  ADD PRIMARY KEY (`NT_ma`),
  ADD UNIQUE KEY `BG_ma` (`BG_ma`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`ND_ma`),
  ADD UNIQUE KEY `ND_Email` (`ND_Email`),
  ADD KEY `VT_ma` (`VT_ma`);

--
-- Indexes for table `nhat_ky_he_thong`
--
ALTER TABLE `nhat_ky_he_thong`
  ADD PRIMARY KEY (`NKHT_ma`),
  ADD KEY `ND_ma` (`ND_ma`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`PF_ma`),
  ADD KEY `HS_ma` (`HS_ma`);

--
-- Indexes for table `tep_tin`
--
ALTER TABLE `tep_tin`
  ADD PRIMARY KEY (`TT_ma`),
  ADD KEY `BG_ma` (`BG_ma`);

--
-- Indexes for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`TToan_ma`),
  ADD KEY `KQ_ma` (`KQ_ma`);

--
-- Indexes for table `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`VT_ma`);

--
-- Indexes for table `yeu_cau_chinh_sua`
--
ALTER TABLE `yeu_cau_chinh_sua`
  ADD PRIMARY KEY (`YC_ma`),
  ADD KEY `BG_ma` (`BG_ma`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bang_chung_khieu_nai`
--
ALTER TABLE `bang_chung_khieu_nai`
  MODIFY `BCKN_ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ban_giao_san_pham`
--
ALTER TABLE `ban_giao_san_pham`
  MODIFY `BG_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `DG_ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `DM_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `de_xuat`
--
ALTER TABLE `de_xuat`
  MODIFY `DX_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `du_an`
--
ALTER TABLE `du_an`
  MODIFY `DA_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `du_an_ky_nang`
--
ALTER TABLE `du_an_ky_nang`
  MODIFY `DAKN_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `giao_dich_blockchain`
--
ALTER TABLE `giao_dich_blockchain`
  MODIFY `GDB_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hop_dong`
--
ALTER TABLE `hop_dong`
  MODIFY `HD_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ho_so_freelancer`
--
ALTER TABLE `ho_so_freelancer`
  MODIFY `HS_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ho_so_ky_nang`
--
ALTER TABLE `ho_so_ky_nang`
  MODIFY `HSKN_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `khieu_nai`
--
ALTER TABLE `khieu_nai`
  MODIFY `KhieuNai_ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ky_nang`
--
ALTER TABLE `ky_nang`
  MODIFY `KN_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ky_quy`
--
ALTER TABLE `ky_quy`
  MODIFY `KQ_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `milestone`
--
ALTER TABLE `milestone`
  MODIFY `MS_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nghiem_thu`
--
ALTER TABLE `nghiem_thu`
  MODIFY `NT_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `ND_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nhat_ky_he_thong`
--
ALTER TABLE `nhat_ky_he_thong`
  MODIFY `NKHT_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `PF_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tep_tin`
--
ALTER TABLE `tep_tin`
  MODIFY `TT_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `TToan_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `VT_ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `yeu_cau_chinh_sua`
--
ALTER TABLE `yeu_cau_chinh_sua`
  MODIFY `YC_ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bang_chung_khieu_nai`
--
ALTER TABLE `bang_chung_khieu_nai`
  ADD CONSTRAINT `bang_chung_khieu_nai_ibfk_1` FOREIGN KEY (`KhieuNai_ma`) REFERENCES `khieu_nai` (`KhieuNai_ma`) ON DELETE CASCADE;

--
-- Constraints for table `ban_giao_san_pham`
--
ALTER TABLE `ban_giao_san_pham`
  ADD CONSTRAINT `ban_giao_san_pham_ibfk_1` FOREIGN KEY (`MS_ma`) REFERENCES `milestone` (`MS_ma`);

--
-- Constraints for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_ibfk_1` FOREIGN KEY (`HD_ma`) REFERENCES `hop_dong` (`HD_ma`),
  ADD CONSTRAINT `danh_gia_ibfk_2` FOREIGN KEY (`ND_ma`) REFERENCES `nguoi_dung` (`ND_ma`);

--
-- Constraints for table `de_xuat`
--
ALTER TABLE `de_xuat`
  ADD CONSTRAINT `de_xuat_ibfk_1` FOREIGN KEY (`DA_ma`) REFERENCES `du_an` (`DA_ma`),
  ADD CONSTRAINT `de_xuat_ibfk_2` FOREIGN KEY (`ND_ma`) REFERENCES `nguoi_dung` (`ND_ma`);

--
-- Constraints for table `du_an`
--
ALTER TABLE `du_an`
  ADD CONSTRAINT `du_an_ibfk_1` FOREIGN KEY (`ND_ma`) REFERENCES `nguoi_dung` (`ND_ma`),
  ADD CONSTRAINT `du_an_ibfk_2` FOREIGN KEY (`DM_ma`) REFERENCES `danh_muc` (`DM_ma`);

--
-- Constraints for table `du_an_ky_nang`
--
ALTER TABLE `du_an_ky_nang`
  ADD CONSTRAINT `du_an_ky_nang_ibfk_1` FOREIGN KEY (`DA_ma`) REFERENCES `du_an` (`DA_ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `du_an_ky_nang_ibfk_2` FOREIGN KEY (`KN_ma`) REFERENCES `ky_nang` (`KN_ma`) ON DELETE CASCADE;

--
-- Constraints for table `giao_dich_blockchain`
--
ALTER TABLE `giao_dich_blockchain`
  ADD CONSTRAINT `giao_dich_blockchain_ibfk_1` FOREIGN KEY (`TToan_ma`) REFERENCES `thanh_toan` (`TToan_ma`);

--
-- Constraints for table `hop_dong`
--
ALTER TABLE `hop_dong`
  ADD CONSTRAINT `hop_dong_ibfk_1` FOREIGN KEY (`DX_ma`) REFERENCES `de_xuat` (`DX_ma`);

--
-- Constraints for table `ho_so_freelancer`
--
ALTER TABLE `ho_so_freelancer`
  ADD CONSTRAINT `ho_so_freelancer_ibfk_1` FOREIGN KEY (`HS_nguoidung`) REFERENCES `nguoi_dung` (`ND_ma`);

--
-- Constraints for table `ho_so_ky_nang`
--
ALTER TABLE `ho_so_ky_nang`
  ADD CONSTRAINT `ho_so_ky_nang_ibfk_1` FOREIGN KEY (`HS_ma`) REFERENCES `ho_so_freelancer` (`HS_ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `ho_so_ky_nang_ibfk_2` FOREIGN KEY (`KN_ma`) REFERENCES `ky_nang` (`KN_ma`) ON DELETE CASCADE;

--
-- Constraints for table `khieu_nai`
--
ALTER TABLE `khieu_nai`
  ADD CONSTRAINT `khieu_nai_ibfk_1` FOREIGN KEY (`HD_ma`) REFERENCES `hop_dong` (`HD_ma`),
  ADD CONSTRAINT `khieu_nai_ibfk_2` FOREIGN KEY (`ND_ma`) REFERENCES `nguoi_dung` (`ND_ma`);

--
-- Constraints for table `ky_quy`
--
ALTER TABLE `ky_quy`
  ADD CONSTRAINT `ky_quy_ibfk_1` FOREIGN KEY (`MS_ma`) REFERENCES `milestone` (`MS_ma`);

--
-- Constraints for table `milestone`
--
ALTER TABLE `milestone`
  ADD CONSTRAINT `milestone_ibfk_1` FOREIGN KEY (`HD_ma`) REFERENCES `hop_dong` (`HD_ma`) ON DELETE CASCADE;

--
-- Constraints for table `nghiem_thu`
--
ALTER TABLE `nghiem_thu`
  ADD CONSTRAINT `nghiem_thu_ibfk_1` FOREIGN KEY (`BG_ma`) REFERENCES `ban_giao_san_pham` (`BG_ma`);

--
-- Constraints for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`VT_ma`) REFERENCES `vai_tro` (`VT_ma`);

--
-- Constraints for table `nhat_ky_he_thong`
--
ALTER TABLE `nhat_ky_he_thong`
  ADD CONSTRAINT `nhat_ky_he_thong_ibfk_1` FOREIGN KEY (`ND_ma`) REFERENCES `nguoi_dung` (`ND_ma`);

--
-- Constraints for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`HS_ma`) REFERENCES `ho_so_freelancer` (`HS_ma`) ON DELETE CASCADE;

--
-- Constraints for table `tep_tin`
--
ALTER TABLE `tep_tin`
  ADD CONSTRAINT `tep_tin_ibfk_1` FOREIGN KEY (`BG_ma`) REFERENCES `ban_giao_san_pham` (`BG_ma`) ON DELETE CASCADE;

--
-- Constraints for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`KQ_ma`) REFERENCES `ky_quy` (`KQ_ma`);

--
-- Constraints for table `yeu_cau_chinh_sua`
--
ALTER TABLE `yeu_cau_chinh_sua`
  ADD CONSTRAINT `yeu_cau_chinh_sua_ibfk_1` FOREIGN KEY (`BG_ma`) REFERENCES `ban_giao_san_pham` (`BG_ma`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
