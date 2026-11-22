CREATE DATABASE IF NOT EXISTS freelance_db CHARACTER SET utf8 COLLATE utf8_vietnamese_ci;
USE freelance_db;

-- 1. VAI_TRO
CREATE TABLE VAI_TRO (
    VT_ma INT AUTO_INCREMENT PRIMARY KEY,
    VT_ten VARCHAR(50) NOT NULL,
    VT_mota TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 2. NGUOI_DUNG
CREATE TABLE NGUOI_DUNG (
    ND_ma INT AUTO_INCREMENT PRIMARY KEY,
    VT_ma INT NOT NULL,
    ND_ho VARCHAR(100) NOT NULL,
    ND_ten VARCHAR(100) NOT NULL,
    ND_Email VARCHAR(150) UNIQUE NOT NULL,
    ND_matkhau VARCHAR(255) NOT NULL,
    ND_SDT VARCHAR(20),
    ND_diachi VARCHAR(255),
    ND_anhdaidien VARCHAR(255) DEFAULT 'https://ui-avatars.com/api/?background=108a00&color=fff&name=User',
    ND_ngaytao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ND_trangthai VARCHAR(30) DEFAULT 'Hoạt động',
    FOREIGN KEY (VT_ma) REFERENCES VAI_TRO(VT_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 3. NHAT_KY_HE_THONG
CREATE TABLE NHAT_KY_HE_THONG (
    NKHT_ma INT AUTO_INCREMENT PRIMARY KEY,
    ND_ma INT,
    NKHT_hanhdong VARCHAR(100) NOT NULL,
    NKHT_doituong VARCHAR(100),
    NKHT_mota TEXT,
    NKHT_diachiIP VARCHAR(45),
    NKHT_thietbi VARCHAR(255),
    NKHT_thoigian DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ND_ma) REFERENCES NGUOI_DUNG(ND_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 4. HO_SO_FREELANCER
CREATE TABLE HO_SO_FREELANCER (
    HS_ma INT AUTO_INCREMENT PRIMARY KEY,
    HS_nguoidung INT UNIQUE NOT NULL,
    HS_tieude VARCHAR(200),
    HS_gioithieu TEXT,
    HS_kinhnghiem TEXT,
    HS_hocvan TEXT,
    HS_mucluongmongmuon DECIMAL(12,2) DEFAULT 0.00,
    HS_duongdan VARCHAR(255),
    HS_ngaycapnhat DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (HS_nguoidung) REFERENCES NGUOI_DUNG(ND_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 5. PORTFOLIO
CREATE TABLE PORTFOLIO (
    PF_ma INT AUTO_INCREMENT PRIMARY KEY,
    HS_ma INT NOT NULL,
    PF_ten VARCHAR(200) NOT NULL,
    PF_mota TEXT,
    PF_hinhanh VARCHAR(255),
    PF_duongdan VARCHAR(255),
    PF_ngaydang DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (HS_ma) REFERENCES HO_SO_FREELANCER(HS_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 6. KY_NANG
CREATE TABLE KY_NANG (
    KN_ma INT AUTO_INCREMENT PRIMARY KEY,
    KN_ten VARCHAR(100) UNIQUE NOT NULL,
    KN_mota TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 7. HO_SO_KY_NANG
CREATE TABLE HO_SO_KY_NANG (
    HSKN_ma INT AUTO_INCREMENT PRIMARY KEY,
    HS_ma INT NOT NULL,
    KN_ma INT NOT NULL,
    HSKN_mucdothanhthao VARCHAR(50) DEFAULT 'Trung cấp',
    HSKN_sonamkinhnghiem INT DEFAULT 1,
    FOREIGN KEY (HS_ma) REFERENCES HO_SO_FREELANCER(HS_ma) ON DELETE CASCADE,
    FOREIGN KEY (KN_ma) REFERENCES KY_NANG(KN_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 8. DANH_MUC
CREATE TABLE DANH_MUC (
    DM_ma INT AUTO_INCREMENT PRIMARY KEY,
    DM_ten VARCHAR(100) NOT NULL,
    DM_mota TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 9. DU_AN
CREATE TABLE DU_AN (
    DA_ma INT AUTO_INCREMENT PRIMARY KEY,
    ND_ma INT NOT NULL,
    DM_ma INT NOT NULL,
    DA_ten VARCHAR(200) NOT NULL,
    DA_mota TEXT,
    DA_ngansach DECIMAL(12,2) NOT NULL,
    DA_ngaydang DATETIME DEFAULT CURRENT_TIMESTAMP,
    DA_hannop DATE,
    DA_trangthai VARCHAR(30) DEFAULT 'Đang mở',
    FOREIGN KEY (ND_ma) REFERENCES NGUOI_DUNG(ND_ma),
    FOREIGN KEY (DM_ma) REFERENCES DANH_MUC(DM_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 10. DU_AN_KY_NANG
CREATE TABLE DU_AN_KY_NANG (
    DAKN_ma INT AUTO_INCREMENT PRIMARY KEY,
    DA_ma INT NOT NULL,
    KN_ma INT NOT NULL,
    DAKN_ghichu VARCHAR(255),
    FOREIGN KEY (DA_ma) REFERENCES DU_AN(DA_ma) ON DELETE CASCADE,
    FOREIGN KEY (KN_ma) REFERENCES KY_NANG(KN_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 11. DE_XUAT
CREATE TABLE DE_XUAT (
    DX_ma INT AUTO_INCREMENT PRIMARY KEY,
    DA_ma INT NOT NULL,
    ND_ma INT NOT NULL,
    DX_ngaygui DATETIME DEFAULT CURRENT_TIMESTAMP,
    DX_gia DECIMAL(12,2) NOT NULL,
    DX_thoigianhoanthanh VARCHAR(100),
    DX_noidung TEXT,
    DX_trangthai VARCHAR(30) DEFAULT 'Chờ duyệt',
    FOREIGN KEY (DA_ma) REFERENCES DU_AN(DA_ma),
    FOREIGN KEY (ND_ma) REFERENCES NGUOI_DUNG(ND_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 12. HOP_DONG
CREATE TABLE HOP_DONG (
    HD_ma INT AUTO_INCREMENT PRIMARY KEY,
    DX_ma INT UNIQUE NOT NULL,
    HD_ngayky DATE DEFAULT (CURRENT_DATE),
    HD_ngaybatdau DATE,
    HD_ngayketthuc DATE,
    HD_tonggiatri DECIMAL(12,2) NOT NULL,
    HD_dieukhoan TEXT,
    HD_trangthai VARCHAR(30) DEFAULT 'Đang thực hiện',
    FOREIGN KEY (DX_ma) REFERENCES DE_XUAT(DX_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 13. MILESTONE
CREATE TABLE MILESTONE (
    MS_ma INT AUTO_INCREMENT PRIMARY KEY,
    HD_ma INT NOT NULL,
    MS_ten VARCHAR(200) NOT NULL,
    MS_mota TEXT,
    MS_sotien DECIMAL(12,2) NOT NULL,
    MS_hanhoanthanh DATE,
    MS_trangthai VARCHAR(30) DEFAULT 'Chờ ký quỹ',
    FOREIGN KEY (HD_ma) REFERENCES HOP_DONG(HD_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 14. KY_QUY
CREATE TABLE KY_QUY (
    KQ_ma INT AUTO_INCREMENT PRIMARY KEY,
    MS_ma INT UNIQUE NOT NULL,
    KQ_sotien DECIMAL(12,2) NOT NULL,
    KQ_ngay DATETIME DEFAULT CURRENT_TIMESTAMP,
    KQ_ngaygiaingan DATETIME NULL,
    KQ_trangthai VARCHAR(30) DEFAULT 'Đã ký quỹ',
    FOREIGN KEY (MS_ma) REFERENCES MILESTONE(MS_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 15. THANH_TOAN
CREATE TABLE THANH_TOAN (
    TToan_ma INT AUTO_INCREMENT PRIMARY KEY,
    KQ_ma INT NOT NULL,
    TToan_sotien DECIMAL(12,2) NOT NULL,
    TToan_phuongthuc VARCHAR(50) DEFAULT 'Thẻ Visa đuôi 1111',
    TToan_loaigiaodich VARCHAR(50) DEFAULT 'Giải ngân ký quỹ',
    TToan_ngay DATETIME DEFAULT CURRENT_TIMESTAMP,
    TToan_trangthai VARCHAR(30) DEFAULT 'Hoàn tất',
    FOREIGN KEY (KQ_ma) REFERENCES KY_QUY(KQ_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 16. GIAO_DICH_BLOCKCHAIN
CREATE TABLE GIAO_DICH_BLOCKCHAIN (
    GDB_ma INT AUTO_INCREMENT PRIMARY KEY,
    TToan_ma INT NOT NULL,
    GDB_hash VARCHAR(100) NOT NULL,
    GDB_vigui VARCHAR(100) NOT NULL,
    GDB_vinhan VARCHAR(100) NOT NULL,
    GDB_sotien DECIMAL(12,2) NOT NULL,
    GDB_phigiaodich DECIMAL(10,6) DEFAULT 0.000420,
    GDB_khoiblock INT NOT NULL,
    GDB_trangthai VARCHAR(30) DEFAULT 'Đã xác nhận',
    FOREIGN KEY (TToan_ma) REFERENCES THANH_TOAN(TToan_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 17. BAN_GIAO_SAN_PHAM
CREATE TABLE BAN_GIAO_SAN_PHAM (
    BG_ma INT AUTO_INCREMENT PRIMARY KEY,
    MS_ma INT NOT NULL,
    BG_ngaybangiao DATETIME DEFAULT CURRENT_TIMESTAMP,
    BG_mota TEXT,
    BG_trangthai VARCHAR(30) DEFAULT 'Đã nộp bài',
    FOREIGN KEY (MS_ma) REFERENCES MILESTONE(MS_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 18. TEP_TIN
CREATE TABLE TEP_TIN (
    TT_ma INT AUTO_INCREMENT PRIMARY KEY,
    BG_ma INT NOT NULL,
    TT_ten VARCHAR(200) NOT NULL,
    TT_duongdan VARCHAR(255) NOT NULL,
    TT_dinhdang VARCHAR(20),
    TT_kichthuoc VARCHAR(30),
    TT_ngaytailen DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (BG_ma) REFERENCES BAN_GIAO_SAN_PHAM(BG_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 19. NGHIEM_THU
CREATE TABLE NGHIEM_THU (
    NT_ma INT AUTO_INCREMENT PRIMARY KEY,
    BG_ma INT UNIQUE NOT NULL,
    NT_ngaynghiemthu DATETIME DEFAULT CURRENT_TIMESTAMP,
    NT_ketqua VARCHAR(50) DEFAULT 'Đã nghiệm thu',
    NT_ghichu TEXT,
    NT_trangthai VARCHAR(30) DEFAULT 'Hoàn thành',
    FOREIGN KEY (BG_ma) REFERENCES BAN_GIAO_SAN_PHAM(BG_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 20. YEU_CAU_CHINH_SUA
CREATE TABLE YEU_CAU_CHINH_SUA (
    YC_ma INT AUTO_INCREMENT PRIMARY KEY,
    BG_ma INT NOT NULL,
    YC_noidung TEXT NOT NULL,
    YC_ngayyeucau DATETIME DEFAULT CURRENT_TIMESTAMP,
    YC_hanchinhsua DATE,
    YC_trangthai VARCHAR(30) DEFAULT 'Đang mở',
    FOREIGN KEY (BG_ma) REFERENCES BAN_GIAO_SAN_PHAM(BG_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 21. DANH_GIA
CREATE TABLE DANH_GIA (
    DG_ma INT AUTO_INCREMENT PRIMARY KEY,
    HD_ma INT NOT NULL,
    ND_ma INT NOT NULL,
    DG_diem INT CHECK (DG_diem BETWEEN 1 AND 5),
    DG_noidung TEXT,
    DG_ngaydanhgia DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (HD_ma) REFERENCES HOP_DONG(HD_ma),
    FOREIGN KEY (ND_ma) REFERENCES NGUOI_DUNG(ND_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 22. KHIEU_NAI
CREATE TABLE KHIEU_NAI (
    KhieuNai_ma INT AUTO_INCREMENT PRIMARY KEY,
    HD_ma INT NOT NULL,
    ND_ma INT NOT NULL,
    KhieuNai_noidung TEXT NOT NULL,
    KhieuNai_lydo VARCHAR(200),
    KhieuNai_ngay DATETIME DEFAULT CURRENT_TIMESTAMP,
    KhieuNai_ketquaxuly TEXT,
    KhieuNai_trangthai VARCHAR(50) DEFAULT 'Bước 1: Đã gửi khiếu nại',
    FOREIGN KEY (HD_ma) REFERENCES HOP_DONG(HD_ma),
    FOREIGN KEY (ND_ma) REFERENCES NGUOI_DUNG(ND_ma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- 23. BANG_CHUNG_KHIEU_NAI
CREATE TABLE BANG_CHUNG_KHIEU_NAI (
    BCKN_ma INT AUTO_INCREMENT PRIMARY KEY,
    KhieuNai_ma INT NOT NULL,
    BCKN_tentep VARCHAR(200) NOT NULL,
    BCKN_duongdan VARCHAR(255) NOT NULL,
    BCKN_dinhdangtep VARCHAR(30),
    BCKN_ngaytailen DATETIME DEFAULT CURRENT_TIMESTAMP,
    BCKN_noidung TEXT,
    FOREIGN KEY (KhieuNai_ma) REFERENCES KHIEU_NAI(KhieuNai_ma) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci;

-- DỮ LIỆU MẪU (SEED DATA)
INSERT INTO VAI_TRO (VT_ten, VT_mota) VALUES
('Khách hàng', 'Chủ dự án / Người thuê Freelancer'),
('Freelancer', 'Chuyên gia cung cấp dịch vụ'),
('Quản trị viên', 'Quản lý hệ thống & Hòa giải tranh chấp');

INSERT INTO NGUOI_DUNG (VT_ma, ND_ho, ND_ten, ND_Email, ND_matkhau, ND_SDT, ND_diachi, ND_anhdaidien) VALUES
(1, 'Nguyễn Văn', 'An', 'client@demo.com', '123456', '0901234567', 'Quận 1, TP. Hồ Chí Minh', 'https://ui-avatars.com/api/?background=0f172a&color=fff&name=Van+An'),
(2, 'Trần Thị', 'Minh', 'freelancer@demo.com', '123456', '0907654321', 'Quận Cầu Giấy, Hà Nội', 'https://ui-avatars.com/api/?background=108a00&color=fff&name=Thi+Minh'),
(3, 'Quản trị', 'Hệ thống', 'admin@demo.com', '123456', '0909999999', 'Trụ sở chính', 'https://ui-avatars.com/api/?background=1e293b&color=fff&name=Admin');

INSERT INTO DANH_MUC (DM_ten, DM_mota) VALUES
('Digital Marketing', 'SEO, Mạng xã hội, Tiếp thị nội dung'),
('Thiết kế & Đồ họa', 'Poster, Bộ nhận diện thương hiệu, Minh họa UI/UX'),
('Lập trình & Công nghệ', 'Phát triển Web, Smart Contract, PHP/MySQL'),
('Viết lách & Dịch thuật', 'Viết bài quảng cáo, Tài liệu kỹ thuật');

INSERT INTO KY_NANG (KN_ten, KN_mota) VALUES
('Canva', 'Công cụ thiết kế đồ họa'),
('Viết nội dung Website', 'Biên tập nội dung thu hút khách hàng'),
('Tối ưu SEO Backlink', 'Tối ưu hóa công cụ tìm kiếm Off-page'),
('PHP & MySQL', 'Phát triển hệ thống Backend toàn diện'),
('Minh họa UI/UX', 'Thiết kế đồ họa vector và giao diện');

INSERT INTO HO_SO_FREELANCER (HS_nguoidung, HS_tieude, HS_gioithieu, HS_kinhnghiem, HS_hocvan, HS_mucluongmongmuon, HS_duongdan) VALUES
(2, 'Chuyên gia Thiết kế Thương hiệu & SEO', 'Hơn 5 năm kinh nghiệm thiết kế minh họa website, poster A2 và triển khai các chiến dịch SEO kỹ thuật.', 'Trưởng nhóm Thiết kế tại StudioX (2021-2026)', 'Cử nhân Thiết kế Truyền thông Đa phương tiện', 45.00, 'https://portfolio.tranminh.demo');

INSERT INTO HO_SO_KY_NANG (HS_ma, KN_ma, HSKN_mucdothanhthao, HSKN_sonamkinhnghiem) VALUES
(1, 1, 'Chuyên gia', 5), (1, 2, 'Chuyên gia', 4), (1, 5, 'Chuyên gia', 5);

INSERT INTO PORTFOLIO (HS_ma, PF_ten, PF_mota, PF_hinhanh, PF_duongdan) VALUES
(1, 'Thiết kế Poster A2 Hiện đại cho Pidgeon', 'Bố cục poster sự kiện doanh nghiệp tối giản và in ấn chất lượng cao.', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600', 'https://behance.net/demo-poster');

INSERT INTO DU_AN (ND_ma, DM_ma, DA_ten, DA_mota, DA_ngansach, DA_hannop, DA_trangthai) VALUES
(1, 2, 'Thiết kế Banner Website Minh họa Độc quyền', 'Cần bản phác thảo ý tưởng, bản nháp 1, minh họa hoàn chỉnh và bàn giao file PNG/SVG.', 250.00, '2026-12-15', 'Đang thực hiện'),
(1, 1, 'Cần Chuyên gia SEO cho Doanh nghiệp Tài chính', 'Thực hiện kiểm toán SEO toàn diện, nghiên cứu từ khóa và xây dựng liên kết chất lượng cao.', 2000.00, '2026-12-30', 'Đang mở');

INSERT INTO DU_AN_KY_NANG (DA_ma, KN_ma, DAKN_ghichu) VALUES (1, 5, 'Bắt buộc'), (2, 3, 'Bắt buộc');

INSERT INTO DE_XUAT (DA_ma, ND_ma, DX_gia, DX_thoigianhoanthanh, DX_noidung, DX_trangthai) VALUES
(1, 2, 250.00, '14 ngày', 'Tôi sẽ bàn giao 5 bản phác thảo ý tưởng trước, sau đó hoàn thiện màu sắc và xuất file nguồn PNG.', 'Đã chấp nhận');

INSERT INTO HOP_DONG (DX_ma, HD_ngaybatdau, HD_ngayketthuc, HD_tonggiatri, HD_dieukhoan, HD_trangthai) VALUES
(1, '2026-11-01', '2026-12-15', 250.00, 'Được bảo vệ bởi Cơ chế Ký quỹ (Escrow). Tiền được giải ngân theo từng cột mốc nghiệm thu.', 'Đang thực hiện');

INSERT INTO MILESTONE (HD_ma, MS_ten, MS_mota, MS_sotien, MS_hanhoanthanh, MS_trangthai) VALUES
(1, '1. Phác thảo ý tưởng (Tổng 5 bản phác thảo)', 'Bố cục khung và 2-3 ý tưởng nhân vật ban đầu', 50.00, '2026-11-10', 'Đã giải ngân'),
(1, '2. Bản nháp minh họa lần 1', 'Phối màu vector hoàn chỉnh để khách hàng phản hồi', 100.00, '2026-11-25', 'Đang thực hiện'),
(1, '3. Bàn giao file hoàn chỉnh (PNG/SVG)', 'Tài nguyên đồ họa độ phân giải cao', 100.00, '2026-12-15', 'Chờ ký quỹ');

INSERT INTO KY_QUY (MS_ma, KQ_sotien, KQ_ngaygiaingan, KQ_trangthai) VALUES
(1, 50.00, '2026-11-11 10:00:00', 'Đã giải ngân'),
(2, 100.00, NULL, 'Đã ký quỹ');

INSERT INTO THANH_TOAN (KQ_ma, TToan_sotien, TToan_phuongthuc, TToan_loaigiaodich, TToan_trangthai) VALUES
(1, 50.00, 'Thẻ Visa đuôi 1111', 'Giải ngân ký quỹ', 'Hoàn tất');

INSERT INTO GIAO_DICH_BLOCKCHAIN (TToan_ma, GDB_hash, GDB_vigui, GDB_vinhan, GDB_sotien, GDB_phigiaodich, GDB_khoiblock, GDB_trangthai) VALUES
(1, '0x000000658a822dee1c8b1e5c355da07c798d14a0301ac46988a412d179d03fac', '0x8b9605af2d460dd1018a0d5048fbb53afffda29b', '0x3d31aa949ba85ea2bba1d2ce0c8db5bc0a90b1fa', 50.00, 0.000420, 92328, 'Đã xác nhận');

INSERT INTO BAN_GIAO_SAN_PHAM (MS_ma, BG_mota, BG_trangthai) VALUES
(1, 'Đính kèm 5 bản phác thảo ý tưởng định dạng PDF độ phân giải cao.', 'Đã nghiệm thu'),
(2, 'Gói minh họa bản nháp 1 đã sẵn sàng để khách hàng kiểm tra.', 'Đã nộp bài');

INSERT INTO TEP_TIN (BG_ma, TT_ten, TT_duongdan, TT_dinhdang, TT_kichthuoc) VALUES
(1, 'ban_phac_thao_y_tuong_v1.pdf', 'uploads/ban_phac_thao_y_tuong_v1.pdf', 'PDF', '3.8 MB'),
(2, 'ban_nhap_1_banner_preview.png', 'uploads/ban_nhap_1_banner_preview.png', 'PNG', '5.2 MB');

INSERT INTO NGHIEM_THU (BG_ma, NT_ketqua, NT_ghichu, NT_trangthai) VALUES
(1, 'Đã nghiệm thu', 'Bố cục rõ ràng, đồng ý giải ngân cột mốc 1.', 'Hoàn thành');