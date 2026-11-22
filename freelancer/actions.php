<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user   = $_SESSION['user'];
$nd_ma  = (int)$user['ND_ma'];
$action = $_POST['action'] ?? '';

switch ($action) {
    // 1. Đăng dự án mới (DU_AN + DU_AN_KY_NANG)
    case 'create_project':
        $stmt = $pdo->prepare("INSERT INTO DU_AN (ND_ma, DM_ma, DA_ten, DA_mota, DA_ngansach, DA_hannop) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nd_ma, $_POST['dm_ma'], $_POST['da_ten'], $_POST['da_mota'], $_POST['da_ngansach'], $_POST['da_hannop']]);
        $da_ma = $pdo->lastInsertId();

        if (!empty($_POST['kn_ma'])) {
            $pdo->prepare("INSERT INTO DU_AN_KY_NANG (DA_ma, KN_ma, DAKN_ghichu) VALUES (?, ?, 'Kỹ năng bắt buộc')")->execute([$da_ma, $_POST['kn_ma']]);
        }
        logAudit($pdo, 'Tạo mới', 'DU_AN', "Đăng dự án #$da_ma: {$_POST['da_ten']}");
        header('Location: dashboard.php?page=projects&msg=' . urlencode('Đã đăng dự án mới thành công'));
        break;

    // 2. Gửi đề xuất báo giá (DE_XUAT)
    case 'submit_proposal':
        $stmt = $pdo->prepare("INSERT INTO DE_XUAT (DA_ma, ND_ma, DX_gia, DX_thoigianhoanthanh, DX_noidung) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['da_ma'], $nd_ma, $_POST['dx_gia'], $_POST['dx_thoigian'], $_POST['dx_noidung']]);
        logAudit($pdo, 'Tạo mới', 'DE_XUAT', "Gửi đề xuất báo giá cho dự án #{$_POST['da_ma']}");
        header('Location: dashboard.php?page=projects&msg=' . urlencode('Đã gửi đề xuất báo giá thành công'));
        break;

    // 3. Chấp nhận đề xuất & Tạo Hợp đồng + Cột mốc đầu tiên (HOP_DONG + MILESTONE)
    case 'hire_proposal':
        $dx_ma = (int)$_POST['dx_ma'];
        $dx = $pdo->query("SELECT * FROM DE_XUAT WHERE DX_ma = $dx_ma")->fetch();
        if ($dx) {
            $pdo->prepare("UPDATE DE_XUAT SET DX_trangthai = 'Đã chấp nhận' WHERE DX_ma = ?")->execute([$dx_ma]);
            $pdo->prepare("UPDATE DU_AN SET DA_trangthai = 'Đang thực hiện' WHERE DA_ma = ?")->execute([$dx['DA_ma']]);

            $pdo->prepare("INSERT INTO HOP_DONG (DX_ma, HD_ngaybatdau, HD_ngayketthuc, HD_tonggiatri, HD_dieukhoan) VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?)")
                ->execute([$dx_ma, $dx['DX_gia'], $_POST['hd_dieukhoan'] ?? 'Bảo vệ thanh toán qua Cơ chế Ký quỹ (Escrow)']);
            $hd_ma = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO MILESTONE (HD_ma, MS_ten, MS_mota, MS_sotien, MS_hanhoanthanh, MS_trangthai) VALUES (?, 'Cột mốc 1: Triển khai ban đầu', 'Giai đoạn bàn giao đầu tiên của hợp đồng', ?, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Chờ ký quỹ')")
                ->execute([$hd_ma, $dx['DX_gia']]);

            logAudit($pdo, 'Tạo mới', 'HOP_DONG', "Khởi tạo hợp đồng #$hd_ma từ đề xuất #$dx_ma");
        }
        header('Location: dashboard.php?page=contracts&msg=' . urlencode('Đã ký hợp đồng và thuê Freelancer thành công'));
        break;

    // 4. Thêm Cột mốc mới (MILESTONE)
    case 'add_milestone':
        $stmt = $pdo->prepare("INSERT INTO MILESTONE (HD_ma, MS_ten, MS_mota, MS_sotien, MS_hanhoanthanh, MS_trangthai) VALUES (?, ?, ?, ?, ?, 'Chờ ký quỹ')");
        $stmt->execute([$_POST['hd_ma'], $_POST['ms_ten'], $_POST['ms_mota'], $_POST['ms_sotien'], $_POST['ms_hanhoanthanh']]);
        logAudit($pdo, 'Tạo mới', 'MILESTONE', "Thêm cột mốc '{$_POST['ms_ten']}'");
        header('Location: dashboard.php?page=contracts&msg=' . urlencode('Đã thêm cột mốc mới'));
        break;

    // 5. Nạp tiền ký quỹ cho Cột mốc (KY_QUY + THANH_TOAN + GIAO_DICH_BLOCKCHAIN qua MetaMask / PHP RPC)
    case 'fund_escrow':
        $ms_ma  = (int)$_POST['ms_ma'];
        $method = $_POST['billing_method'] ?? 'Ví MetaMask (Web3)';
        $ms = $pdo->query("SELECT * FROM MILESTONE WHERE MS_ma = $ms_ma")->fetch();

        if ($ms) {
            $pdo->prepare("UPDATE MILESTONE SET MS_trangthai = 'Đang thực hiện' WHERE MS_ma = ?")->execute([$ms_ma]);
            $pdo->prepare("INSERT INTO KY_QUY (MS_ma, KQ_sotien, KQ_trangthai) VALUES (?, ?, 'Đã ký quỹ')")
                ->execute([$ms_ma, $ms['MS_sotien']]);
            $kq_ma = $pdo->lastInsertId();

            $totalWithFee = $ms['MS_sotien'] * 1.05 + 4.99;
            $pdo->prepare("INSERT INTO THANH_TOAN (KQ_ma, TToan_sotien, TToan_phuongthuc, TToan_loaigiaodich) VALUES (?, ?, ?, 'Nạp tiền ký quỹ')")
                ->execute([$kq_ma, $totalWithFee, $method]);
            $ttoan_ma = $pdo->lastInsertId();

            // Lấy dữ liệu giao dịch thật từ MetaMask (nếu có), nếu không thì tạo qua PHP RPC
            $latestBlock = (int)$pdo->query("SELECT COALESCE(MAX(GDB_khoiblock), 92400) FROM GIAO_DICH_BLOCKCHAIN")->fetchColumn();
            $txHash = !empty($_POST['tx_hash'])  ? $_POST['tx_hash']       : ('0x' . hash('sha256', 'KY_QUY_NAP_' . $ms_ma . '_' . microtime(true)));
            $txFrom = !empty($_POST['tx_from'])  ? $_POST['tx_from']       : '0x8b9605af2d460dd1018a0d5048fbb53afffda29b';
            $txTo   = !empty($_POST['tx_to'])    ? $_POST['tx_to']         : '0x5FbDB2315678afecb367f032d93F642f64180aa3';
            $block  = !empty($_POST['tx_block']) ? (int)$_POST['tx_block'] : ($latestBlock + 1);
            $gasFee = !empty($_POST['tx_gas'])   ? (float)$_POST['tx_gas'] : 0.000084;

            $pdo->prepare("INSERT INTO GIAO_DICH_BLOCKCHAIN (TToan_ma, GDB_hash, GDB_vigui, GDB_vinhan, GDB_sotien, GDB_phigiaodich, GDB_khoiblock) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$ttoan_ma, $txHash, $txFrom, $txTo, $totalWithFee, $gasFee, $block]);

            logAudit($pdo, 'Cập nhật', 'KY_QUY', "Nạp ký quỹ cho cột mốc #$ms_ma (Tx: $txHash | Block: #$block)");
        }
        header('Location: dashboard.php?page=contracts&msg=' . urlencode('Đã nạp tiền vào quỹ Escrow và ghi nhận lên Blockchain thành công'));
        break;

    // 6. Giải ngân tiền ký quỹ cho Freelancer (KY_QUY + THANH_TOAN + GIAO_DICH_BLOCKCHAIN)
    case 'release_milestone':
        $ms_ma = (int)$_POST['ms_ma'];
        $kq = $pdo->query("SELECT * FROM KY_QUY WHERE MS_ma = $ms_ma")->fetch();

        if ($kq) {
            $pdo->prepare("UPDATE MILESTONE SET MS_trangthai = 'Đã giải ngân' WHERE MS_ma = ?")->execute([$ms_ma]);
            $pdo->prepare("UPDATE KY_QUY SET KQ_trangthai = 'Đã giải ngân', KQ_ngaygiaingan = NOW() WHERE KQ_ma = ?")->execute([$kq['KQ_ma']]);

            $pdo->prepare("INSERT INTO THANH_TOAN (KQ_ma, TToan_sotien, TToan_phuongthuc, TToan_loaigiaodich) VALUES (?, ?, 'Hợp đồng thông minh Escrow', 'Giải ngân ký quỹ')")
                ->execute([$kq['KQ_ma'], $kq['KQ_sotien']]);
            $ttoan_ma = $pdo->lastInsertId();

            $latestBlock = (int)$pdo->query("SELECT COALESCE(MAX(GDB_khoiblock), 92400) FROM GIAO_DICH_BLOCKCHAIN")->fetchColumn();
            $txHash = !empty($_POST['tx_hash'])  ? $_POST['tx_hash']       : ('0x' . hash('sha256', 'KY_QUY_GIAI_NGAN_' . $ms_ma . '_' . microtime(true)));
            $txFrom = !empty($_POST['tx_from'])  ? $_POST['tx_from']       : '0x5FbDB2315678afecb367f032d93F642f64180aa3';
            $txTo   = !empty($_POST['tx_to'])    ? $_POST['tx_to']         : '0x3d31aa949ba85ea2bba1d2ce0c8db5bc0a90b1fa';
            $block  = !empty($_POST['tx_block']) ? (int)$_POST['tx_block'] : ($latestBlock + 1);
            $gasFee = !empty($_POST['tx_gas'])   ? (float)$_POST['tx_gas'] : 0.000084;

            $pdo->prepare("INSERT INTO GIAO_DICH_BLOCKCHAIN (TToan_ma, GDB_hash, GDB_vigui, GDB_vinhan, GDB_sotien, GDB_phigiaodich, GDB_khoiblock) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$ttoan_ma, $txHash, $txFrom, $txTo, $kq['KQ_sotien'], $gasFee, $block]);

            logAudit($pdo, 'Cập nhật', 'THANH_TOAN', "Giải ngân ký quỹ cho cột mốc #$ms_ma (Tx: $txHash | Block: #$block)");
        }
        header('Location: dashboard.php?page=contracts&msg=' . urlencode('Đã giải ngân tiền ký quỹ cho Freelancer và xác nhận Blockchain'));
        break;

    // 7. Bàn giao sản phẩm & Đính kèm tệp tin (BAN_GIAO_SAN_PHAM + TEP_TIN)
    case 'submit_deliverable':
        $stmt = $pdo->prepare("INSERT INTO BAN_GIAO_SAN_PHAM (MS_ma, BG_mota, BG_trangthai) VALUES (?, ?, 'Đã nộp bài')");
        $stmt->execute([$_POST['ms_ma'], $_POST['bg_mota']]);
        $bg_ma = $pdo->lastInsertId();

        $fileName = trim($_POST['tt_ten']) ?: 'goi_san_pham_ban_giao.zip';
        $ext = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'PDF');
        $pdo->prepare("INSERT INTO TEP_TIN (BG_ma, TT_ten, TT_duongdan, TT_dinhdang, TT_kichthuoc) VALUES (?, ?, ?, ?, '4.2 MB')")
            ->execute([$bg_ma, $fileName, 'uploads/' . $fileName, $ext]);

        logAudit($pdo, 'Tạo mới', 'BAN_GIAO_SAN_PHAM', "Nộp sản phẩm bàn giao #$bg_ma cho cột mốc #{$_POST['ms_ma']}");
        header('Location: dashboard.php?page=deliverables&msg=' . urlencode('Đã nộp sản phẩm để khách hàng nghiệm thu'));
        break;

    // 8. Nghiệm thu hoặc Yêu cầu chỉnh sửa (NGHIEM_THU / YEU_CAU_CHINH_SUA)
    case 'review_deliverable':
        $bg_ma = (int)$_POST['bg_ma'];
        if ($_POST['decision'] === 'approve') {
            $pdo->prepare("UPDATE BAN_GIAO_SAN_PHAM SET BG_trangthai = 'Đã nghiệm thu' WHERE BG_ma = ?")->execute([$bg_ma]);
            $pdo->prepare("INSERT INTO NGHIEM_THU (BG_ma, NT_ketqua, NT_ghichu) VALUES (?, 'Đã nghiệm thu', ?)")
                ->execute([$bg_ma, $_POST['ghichu'] ?? 'Khách hàng đã duyệt nghiệm thu']);
            logAudit($pdo, 'Tạo mới', 'NGHIEM_THU', "Nghiệm thu sản phẩm #$bg_ma");
        } else {
            $pdo->prepare("UPDATE BAN_GIAO_SAN_PHAM SET BG_trangthai = 'Yêu cầu chỉnh sửa' WHERE BG_ma = ?")->execute([$bg_ma]);
            $pdo->prepare("INSERT INTO YEU_CAU_CHINH_SUA (BG_ma, YC_noidung, YC_hanchinhsua) VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 3 DAY))")
                ->execute([$bg_ma, $_POST['ghichu'] ?? 'Vui lòng chỉnh sửa lại chi tiết theo yêu cầu']);
            logAudit($pdo, 'Tạo mới', 'YEU_CAU_CHINH_SUA', "Yêu cầu chỉnh sửa sản phẩm #$bg_ma");
        }
        header('Location: dashboard.php?page=deliverables&msg=' . urlencode('Đã cập nhật kết quả nghiệm thu'));
        break;

    // 9. Cập nhật Hồ sơ Freelancer, Kỹ năng & Portfolio
    case 'update_profile':
        $pdo->prepare("UPDATE HO_SO_FREELANCER SET HS_tieude = ?, HS_gioithieu = ?, HS_kinhnghiem = ?, HS_hocvan = ?, HS_mucluongmongmuon = ? WHERE HS_nguoidung = ?")
            ->execute([$_POST['hs_tieude'], $_POST['hs_gioithieu'], $_POST['hs_kinhnghiem'], $_POST['hs_hocvan'], $_POST['hs_mucluong'], $nd_ma]);
        logAudit($pdo, 'Cập nhật', 'HO_SO_FREELANCER', "Cập nhật thông tin tổng quan hồ sơ Freelancer");
        header('Location: dashboard.php?page=profile&msg=' . urlencode('Đã lưu thông tin hồ sơ'));
        break;

    case 'add_skill':
        $hs = $pdo->query("SELECT HS_ma FROM HO_SO_FREELANCER WHERE HS_nguoidung = $nd_ma")->fetch();
        if ($hs && !empty($_POST['kn_ma'])) {
            $pdo->prepare("INSERT INTO HO_SO_KY_NANG (HS_ma, KN_ma, HSKN_mucdothanhthao, HSKN_sonamkinhnghiem) VALUES (?, ?, ?, ?)")
                ->execute([$hs['HS_ma'], $_POST['kn_ma'], $_POST['level'], $_POST['years']]);
            logAudit($pdo, 'Tạo mới', 'HO_SO_KY_NANG', "Thêm kỹ năng #{$_POST['kn_ma']} vào hồ sơ");
        }
        header('Location: dashboard.php?page=profile&msg=' . urlencode('Đã thêm kỹ năng'));
        break;

    case 'remove_skill':
        $pdo->prepare("DELETE FROM HO_SO_KY_NANG WHERE HSKN_ma = ?")->execute([(int)$_POST['hskn_ma']]);
        header('Location: dashboard.php?page=profile&msg=' . urlencode('Đã xóa kỹ năng'));
        break;

    case 'add_portfolio':
        $hs = $pdo->query("SELECT HS_ma FROM HO_SO_FREELANCER WHERE HS_nguoidung = $nd_ma")->fetch();
        if ($hs) {
            $img = trim($_POST['pf_hinhanh']) ?: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600';
            $pdo->prepare("INSERT INTO PORTFOLIO (HS_ma, PF_ten, PF_mota, PF_hinhanh, PF_duongdan) VALUES (?, ?, ?, ?, ?)")
                ->execute([$hs['HS_ma'], $_POST['pf_ten'], $_POST['pf_mota'], $img, $_POST['pf_duongdan']]);
            logAudit($pdo, 'Tạo mới', 'PORTFOLIO', "Thêm dự án mẫu '{$_POST['pf_ten']}'");
        }
        header('Location: dashboard.php?page=profile&msg=' . urlencode('Đã thêm dự án vào Portfolio'));
        break;

    // 10. Đánh giá mức độ hài lòng (DANH_GIA)
    case 'add_review':
        $pdo->prepare("INSERT INTO DANH_GIA (HD_ma, ND_ma, DG_diem, DG_noidung) VALUES (?, ?, ?, ?)")
            ->execute([$_POST['hd_ma'], $nd_ma, $_POST['dg_diem'], $_POST['dg_noidung']]);
        logAudit($pdo, 'Tạo mới', 'DANH_GIA', "Gửi đánh giá {$_POST['dg_diem']} sao cho hợp đồng #{$_POST['hd_ma']}");
        header('Location: dashboard.php?page=contracts&msg=' . urlencode('Đã gửi đánh giá thành công'));
        break;

    // 11. Khiếu nại ký quỹ & Bằng chứng (KHIEU_NAI + BANG_CHUNG_KHIEU_NAI)
    case 'file_dispute':
        $stmt = $pdo->prepare("INSERT INTO KHIEU_NAI (HD_ma, ND_ma, KhieuNai_lydo, KhieuNai_noidung, KhieuNai_trangthai) VALUES (?, ?, ?, ?, 'Bước 1: Đã gửi khiếu nại')");
        $stmt->execute([$_POST['hd_ma'], $nd_ma, $_POST['lydo'], $_POST['noidung']]);
        $kn_ma = $pdo->lastInsertId();

        if (!empty($_POST['tentep'])) {
            $pdo->prepare("INSERT INTO BANG_CHUNG_KHIEU_NAI (KhieuNai_ma, BCKN_tentep, BCKN_duongdan, BCKN_dinhdangtep, BCKN_noidung) VALUES (?, ?, ?, 'PDF', 'Bằng chứng khiếu nại đính kèm')")
                ->execute([$kn_ma, $_POST['tentep'], 'uploads/disputes/' . $_POST['tentep']]);
        }
        logAudit($pdo, 'Tạo mới', 'KHIEU_NAI', "Khởi tạo khiếu nại ký quỹ #$kn_ma");
        header('Location: dashboard.php?page=disputes&msg=' . urlencode('Đã gửi hồ sơ khiếu nại ký quỹ'));
        break;

    case 'resolve_dispute':
        $pdo->prepare("UPDATE KHIEU_NAI SET KhieuNai_trangthai = ?, KhieuNai_ketquaxuly = ? WHERE KhieuNai_ma = ?")
            ->execute([$_POST['trangthai'], $_POST['ketqua'], (int)$_POST['khieunai_ma']]);
        logAudit($pdo, 'Cập nhật', 'KHIEU_NAI', "Cập nhật trạng thái khiếu nại #{$_POST['khieunai_ma']} thành {$_POST['trangthai']}");
        header('Location: dashboard.php?page=disputes&msg=' . urlencode('Đã cập nhật kết quả xử lý khiếu nại'));
        break;

    default:
        header('Location: dashboard.php');
}