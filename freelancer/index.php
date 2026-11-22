<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['password'] ?? '');

        $stmt = $pdo->prepare("
            SELECT u.*, r.VT_ten 
            FROM NGUOI_DUNG u 
            JOIN VAI_TRO r ON u.VT_ma = r.VT_ma 
            WHERE u.ND_Email = ? AND u.ND_matkhau = ?
        ");
        $stmt->execute([$email, $pass]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = $user;
            logAudit($pdo, 'ĐĂNG NHẬP', 'NGUOI_DUNG', "Người dùng {$user['ND_Email']} đã đăng nhập với vai trò {$user['VT_ten']}");
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không chính xác.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $vt_ma = (int)$_POST['vt_ma'];
        $ho    = trim($_POST['nd_ho']);
        $ten   = trim($_POST['nd_ten']);
        $email = trim($_POST['nd_email']);
        $pass  = trim($_POST['nd_matkhau']);
        $addr  = trim($_POST['nd_diachi']);

        $stmt = $pdo->prepare("INSERT INTO NGUOI_DUNG (VT_ma, ND_ho, ND_ten, ND_Email, ND_matkhau, ND_diachi) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$vt_ma, $ho, $ten, $email, $pass, $addr]);
        $newId = $pdo->lastInsertId();

        if ($vt_ma === 2) {
            $pdo->prepare("INSERT INTO HO_SO_FREELANCER (HS_nguoidung, HS_tieude) VALUES (?, 'Chuyên viên Freelancer mới')")->execute([$newId]);
        }
        $error = 'Tạo tài khoản thành công! Vui lòng đăng nhập bên dưới.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cổng thông tin Freelance & Ký quỹ Blockchain - Đăng nhập</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px;">
    <div class="card" style="width:100%; max-width:460px; padding:32px;">
        <div style="text-align:center; margin-bottom:22px;">
            <div style="width:52px; height:52px; border-radius:50%; background:var(--primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; margin-bottom:10px;">FE</div>
            <h1 style="font-size:22px;">Đăng nhập Hệ thống Ký quỹ Freelance</h1>
            <p style="color:var(--text-muted); font-size:13px;">Nền tảng Quản lý Cột mốc & Thanh toán Minh bạch Blockchain</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-success"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Địa chỉ Email</label>
                <input type="email" name="email" class="form-control" value="client@demo.com" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" value="123456" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:6px;">Đăng nhập</button>
        </form>

        <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--border-color); font-size:12px; color:var(--text-muted);">
            <strong>Tài khoản dùng thử nhanh (Mật khẩu: <code>123456</code>):</strong><br>
            • Khách hàng (Client): <code>client@demo.com</code><br>
            • Freelancer: <code>freelancer@demo.com</code><br>
            • Quản trị viên (Admin): <code>admin@demo.com</code>
        </div>

        <details style="margin-top:16px;">
            <summary style="cursor:pointer; color:var(--primary); font-weight:600; font-size:13px;">+ Đăng ký tài khoản mới</summary>
            <form method="POST" style="margin-top:12px;">
                <input type="hidden" name="action" value="register">
                <div class="form-grid">
                    <div class="form-group"><label>Họ và tên đệm</label><input type="text" name="nd_ho" class="form-control" required></div>
                    <div class="form-group"><label>Tên</label><input type="text" name="nd_ten" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" name="nd_email" class="form-control" required></div>
                <div class="form-group"><label>Mật khẩu</label><input type="password" name="nd_matkhau" class="form-control" required></div>
                <div class="form-group"><label>Địa chỉ / Quốc gia</label><input type="text" name="nd_diachi" class="form-control" placeholder="Việt Nam"></div>
                <div class="form-group">
                    <label>Vai trò (VAI_TRO)</label>
                    <select name="vt_ma" class="form-control">
                        <option value="1">Thuê nhân sự (Khách hàng)</option>
                        <option value="2">Tìm việc làm (Freelancer)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline" style="width:100%;">Tạo tài khoản</button>
            </form>
        </details>
    </div>
</body>
</html>