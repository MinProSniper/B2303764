<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$user  = $_SESSION['user'];
$nd_ma = (int)$user['ND_ma'];
$role  = $user['VT_ten'];
$page  = $_GET['page'] ?? 'overview';
$msg   = $_GET['msg'] ?? '';

// Thống kê tổng quan (KPI)
$totalEscrow     = $pdo->query("SELECT COALESCE(SUM(KQ_sotien),0) FROM KY_QUY WHERE KQ_trangthai = 'Đã ký quỹ'")->fetchColumn();
$totalReleased   = $pdo->query("SELECT COALESCE(SUM(KQ_sotien),0) FROM KY_QUY WHERE KQ_trangthai = 'Đã giải ngân'")->fetchColumn();
$activeContracts = $pdo->query("SELECT COUNT(*) FROM HOP_DONG WHERE HD_trangthai = 'Đang thực hiện'")->fetchColumn();
$openProjects    = $pdo->query("SELECT COUNT(*) FROM DU_AN WHERE DA_trangthai = 'Đang mở'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Dự án Freelance & Ký quỹ Blockchain</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="topbar">
    <div class="brand">
        <span>🏛️ Nền tảng Freelance & Ký quỹ Blockchain</span>
    </div>
    <div class="user-pill">
        <span>👤 <?= e($user['ND_ho'] . ' ' . $user['ND_ten']) ?></span>
        <span class="role-badge"><?= e($role) ?></span>
        <a href="dashboard.php?logout=1" class="btn btn-sm" style="background:rgba(255,255,255,0.18); color:#fff;">Đăng xuất</a>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-title">Danh mục chức năng</div>
        <a href="dashboard.php?page=overview" class="nav-link <?= $page==='overview'?'active':'' ?>">📊 Bảng điều khiển tổng quan</a>
        <a href="dashboard.php?page=projects" class="nav-link <?= $page==='projects'?'active':'' ?>">💼 Dự án & Đề xuất báo giá</a>
        <a href="dashboard.php?page=contracts" class="nav-link <?= $page==='contracts'?'active':'' ?>">💳 Hợp đồng & Cột mốc Ký quỹ</a>
        <a href="dashboard.php?page=deliverables" class="nav-link <?= $page==='deliverables'?'active':'' ?>">📦 Bàn giao & Nghiệm thu</a>
        <a href="dashboard.php?page=profile" class="nav-link <?= $page==='profile'?'active':'' ?>">🧑‍💻 Hồ sơ & Kỹ năng Freelancer</a>
        <a href="dashboard.php?page=disputes" class="nav-link <?= $page==='disputes'?'active':'' ?>">⚖️ Khiếu nại & Hòa giải</a>
        <a href="dashboard.php?page=blockchain" class="nav-link <?= $page==='blockchain'?'active':'' ?>">⛓️ Blockchain & Nhật ký hệ thống</a>
    </aside>

    <main class="main-content">
        <?php if ($msg): ?>
            <div class="alert-success">✅ <?= e($msg) ?></div>
        <?php endif; ?>

        <?php if ($page === 'overview'): ?>
            <div class="page-header">
                <div>
                    <h1>Bảng điều khiển Tổng quan</h1>
                    <p>Xin chào, <?= e($user['ND_ho'] . ' ' . $user['ND_ten']) ?> — Theo dõi Ký quỹ, Cột mốc và Sổ cái Blockchain theo thời gian thực</p>
                </div>
                <a href="dashboard.php?page=projects" class="btn btn-primary">+ Đăng / Tìm dự án mới</a>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="label">Đang giữ trong Ký quỹ (KY_QUY)</div>
                    <div class="value">$<?= number_format($totalEscrow, 2) ?></div>
                    <div class="sub">Được bảo vệ bởi Hợp đồng thông minh</div>
                </div>
                <div class="kpi-card">
                    <div class="label">Đã giải ngân cho Freelancer</div>
                    <div class="value">$<?= number_format($totalReleased, 2) ?></div>
                    <div class="sub">Đã xác thực trên Blockchain</div>
                </div>
                <div class="kpi-card">
                    <div class="label">Hợp đồng đang thực hiện</div>
                    <div class="value"><?= $activeContracts ?></div>
                    <div class="sub">Thanh toán theo từng cột mốc</div>
                </div>
                <div class="kpi-card">
                    <div class="label">Dự án đang mở nhận báo giá</div>
                    <div class="value"><?= $openProjects ?></div>
                    <div class="sub">Sẵn sàng tiếp nhận đề xuất</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Tiến độ Thanh toán Các cột mốc Hợp đồng</h2>
                    <a href="dashboard.php?page=contracts" class="btn btn-outline btn-sm">Quản lý tất cả cột mốc</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Cột mốc (Milestone)</th>
                            <th>Hợp đồng</th>
                            <th>Hạn hoàn thành</th>
                            <th>Số tiền</th>
                            <th>Trạng thái ký quỹ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $milestones = $pdo->query("
                            SELECT m.*, h.HD_ma, d.DA_ten 
                            FROM MILESTONE m 
                            JOIN HOP_DONG h ON m.HD_ma = h.HD_ma 
                            JOIN DE_XUAT dx ON h.DX_ma = dx.DX_ma 
                            JOIN DU_AN d ON dx.DA_ma = d.DA_ma
                        ")->fetchAll();
                        foreach ($milestones as $m):
                            $badgeClass = $m['MS_trangthai']==='Đã giải ngân' ? 'badge-green' : ($m['MS_trangthai']==='Đang thực hiện' ? 'badge-blue' : 'badge-amber');
                        ?>
                        <tr>
                            <td><strong><?= e($m['MS_ten']) ?></strong><br><small style="color:var(--text-muted)"><?= e($m['MS_mota']) ?></small></td>
                            <td>#<?= $m['HD_ma'] ?> — <?= e($m['DA_ten']) ?></td>
                            <td><?= e($m['MS_hanhoanthanh']) ?></td>
                            <td><strong>$<?= number_format($m['MS_sotien'], 2) ?></strong></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= e($m['MS_trangthai']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page === 'projects'): ?>
            <div class="page-header">
                <div>
                    <h1>Dự án & Đề xuất báo giá (DU_AN & DE_XUAT)</h1>
                    <p>Đăng tin tuyển dụng theo danh mục hoặc gửi đề xuất báo giá thực hiện dự án</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Đăng Dự án Mới</h2></div>
                <form method="POST" action="actions.php">
                    <input type="hidden" name="action" value="create_project">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tiêu đề dự án *</label>
                            <input type="text" name="da_ten" class="form-control" placeholder="Ví dụ: Thiết kế Poster A2 Hiện đại" required>
                        </div>
                        <div class="form-group">
                            <label>Danh mục (DANH_MUC) *</label>
                            <select name="dm_ma" class="form-control">
                                <?php foreach ($pdo->query("SELECT * FROM DANH_MUC") as $dm): ?>
                                    <option value="<?= $dm['DM_ma'] ?>"><?= e($dm['DM_ten']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kỹ năng yêu cầu (KY_NANG)</label>
                            <select name="kn_ma" class="form-control">
                                <?php foreach ($pdo->query("SELECT * FROM KY_NANG") as $kn): ?>
                                    <option value="<?= $kn['KN_ma'] ?>"><?= e($kn['KN_ten']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngân sách trọn gói ($ USD) *</label>
                            <input type="number" step="0.01" name="da_ngansach" class="form-control" value="500.00" required>
                        </div>
                        <div class="form-group">
                            <label>Hạn chót nhận báo giá *</label>
                            <input type="date" name="da_hannop" class="form-control" value="2026-12-31" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phạm vi công việc & Mô tả chi tiết *</label>
                        <textarea name="da_mota" rows="3" class="form-control" placeholder="Mô tả các hạng mục cần bàn giao, yêu cầu kỹ thuật..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng tin dự án</button>
                </form>
            </div>

            <?php
            $projects = $pdo->query("
                SELECT d.*, dm.DM_ten, u.ND_ho, u.ND_ten 
                FROM DU_AN d 
                JOIN DANH_MUC dm ON d.DM_ma = dm.DM_ma 
                JOIN NGUOI_DUNG u ON d.ND_ma = u.ND_ma 
                ORDER BY d.DA_ma DESC
            ")->fetchAll();
            foreach ($projects as $p):
            ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="badge badge-blue"><?= e($p['DM_ten']) ?></span>
                        <span class="badge <?= $p['DA_trangthai']==='Đang mở'?'badge-green':'badge-amber' ?>"><?= e($p['DA_trangthai']) ?></span>
                        <h2 style="margin-top:6px;"><?= e($p['DA_ten']) ?></h2>
                        <small style="color:var(--text-muted)">Người đăng: <?= e($p['ND_ho'].' '.$p['ND_ten']) ?> • Ngân sách: <strong>$<?= number_format($p['DA_ngansach'], 2) ?></strong> • Hạn nộp: <?= e($p['DA_hannop']) ?></small>
                    </div>
                </div>
                <p style="margin-bottom:16px; font-size:14px;"><?= e($p['DA_mota']) ?></p>

                <?php if ($p['DA_trangthai'] === 'Đang mở'): ?>
                <details style="background:#f8faf9; padding:14px; border-radius:8px; border:1px solid var(--border-color); margin-bottom:14px;">
                    <summary style="cursor:pointer; font-weight:700; color:var(--primary);">+ Gửi Đề xuất Báo giá (Phí dịch vụ nền tảng 10%)</summary>
                    <form method="POST" action="actions.php" style="margin-top:12px;">
                        <input type="hidden" name="action" value="submit_proposal">
                        <input type="hidden" name="da_ma" value="<?= $p['DA_ma'] ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Mức giá đề xuất ($ USD)</label>
                                <input type="number" step="0.01" name="dx_gia" class="form-control" value="<?= $p['DA_ngansach'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Thời gian hoàn thành dự kiến</label>
                                <input type="text" name="dx_thoigian" class="form-control" value="14 ngày" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Thư ngỏ & Cam kết thực hiện</label>
                            <textarea name="dx_noidung" rows="2" class="form-control" required>Sẵn sàng triển khai đúng tiến độ với cơ chế bảo vệ thanh toán ký quỹ.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Gửi đề xuất</button>
                    </form>
                </details>
                <?php endif; ?>

                <h4 style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Danh sách Đề xuất đã gửi (DE_XUAT)</h4>
                <table>
                    <thead>
                        <tr><th>Freelancer</th><th>Giá chào</th><th>Thời gian</th><th>Nội dung đề xuất</th><th>Trạng thái / Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $props = $pdo->prepare("SELECT dx.*, u.ND_ho, u.ND_ten FROM DE_XUAT dx JOIN NGUOI_DUNG u ON dx.ND_ma = u.ND_ma WHERE dx.DA_ma = ?");
                        $props->execute([$p['DA_ma']]);
                        foreach ($props as $pr):
                        ?>
                        <tr>
                            <td><strong><?= e($pr['ND_ho'].' '.$pr['ND_ten']) ?></strong></td>
                            <td>$<?= number_format($pr['DX_gia'], 2) ?></td>
                            <td><?= e($pr['DX_thoigianhoanthanh']) ?></td>
                            <td><?= e($pr['DX_noidung']) ?></td>
                            <td>
                                <?php if ($pr['DX_trangthai'] === 'Chờ duyệt'): ?>
                                    <form method="POST" action="actions.php" style="display:inline;">
                                        <input type="hidden" name="action" value="hire_proposal">
                                        <input type="hidden" name="dx_ma" value="<?= $pr['DX_ma'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Chấp nhận & Ký hợp đồng</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-green"><?= e($pr['DX_trangthai']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>

        <?php elseif ($page === 'contracts'): ?>
            <div class="page-header">
                <div>
                    <h1>Hợp đồng, Cột mốc & Ký quỹ Thanh toán</h1>
                    <p>Nạp tiền vào quỹ Escrow (KY_QUY) và giải ngân thanh toán (THANH_TOAN)</p>
                </div>
            </div>

            <?php
            $contracts = $pdo->query("
                SELECT h.*, d.DA_ten, u.ND_ho, u.ND_ten 
                FROM HOP_DONG h 
                JOIN DE_XUAT dx ON h.DX_ma = dx.DX_ma 
                JOIN DU_AN d ON dx.DA_ma = d.DA_ma 
                JOIN NGUOI_DUNG u ON dx.ND_ma = u.ND_ma
            ")->fetchAll();
            foreach ($contracts as $c):
                $agreed = $c['HD_tonggiatri'];
                $paid = $pdo->query("SELECT COALESCE(SUM(MS_sotien),0) FROM MILESTONE WHERE HD_ma = {$c['HD_ma']} AND MS_trangthai = 'Đã giải ngân'")->fetchColumn();
                $remaining = $agreed - $paid;
            ?>
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Hợp đồng #<?= $c['HD_ma'] ?>: <?= e($c['DA_ten']) ?></h2>
                        <small style="color:var(--text-muted)">Freelancer: <strong><?= e($c['ND_ho'].' '.$c['ND_ten']) ?></strong> • <?= e($c['HD_dieukhoan']) ?></small>
                    </div>
                    <span class="badge badge-green"><?= e($c['HD_trangthai']) ?></span>
                </div>

                <div class="kpi-grid" style="margin-bottom:18px;">
                    <div class="kpi-card"><div class="label">Tổng giá trị thỏa thuận</div><div class="value">$<?= number_format($agreed, 2) ?></div></div>
                    <div class="kpi-card"><div class="label">Đã thanh toán (Giải ngân)</div><div class="value" style="color:var(--primary);">$<?= number_format($paid, 2) ?></div></div>
                    <div class="kpi-card"><div class="label">Còn lại</div><div class="value">$<?= number_format($remaining, 2) ?></div></div>
                </div>

                <table>
                    <thead>
                        <tr><th>Cột mốc</th><th>Hạn hoàn thành</th><th>Số tiền</th><th>Trạng thái</th><th>Thao tác Ký quỹ</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $msList = $pdo->prepare("SELECT * FROM MILESTONE WHERE HD_ma = ?");
                        $msList->execute([$c['HD_ma']]);
                        foreach ($msList as $m):
                        ?>
                        <tr>
                            <td><strong><?= e($m['MS_ten']) ?></strong><br><small style="color:var(--text-muted)"><?= e($m['MS_mota']) ?></small></td>
                            <td><?= e($m['MS_hanhoanthanh']) ?></td>
                            <td><strong>$<?= number_format($m['MS_sotien'], 2) ?></strong></td>
                            <td>
                                <span class="badge <?= $m['MS_trangthai']==='Đã giải ngân'?'badge-green':($m['MS_trangthai']==='Đang thực hiện'?'badge-blue':'badge-amber') ?>">
                                    <?= e($m['MS_trangthai']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($m['MS_trangthai'] === 'Chờ ký quỹ'): ?>
                                    <form method="POST" action="actions.php" style="display:flex; gap:6px; align-items:center;">
                                        <input type="hidden" name="action" value="fund_escrow">
                                        <input type="hidden" name="ms_ma" value="<?= $m['MS_ma'] ?>">
                                        <select name="billing_method" class="form-control" style="padding:5px 8px; font-size:12px; width:175px;">
                                            <option>Thẻ Visa đuôi 1111</option>
                                            <option>Mastercard đuôi 8842</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Nạp ký quỹ ($<?= number_format($m['MS_sotien']*1.05+4.99, 2) ?>)</button>
                                    </form>
                                <?php elseif ($m['MS_trangthai'] === 'Đang thực hiện'): ?>
                                    <form method="POST" action="actions.php">
                                        <input type="hidden" name="action" value="release_milestone">
                                        <input type="hidden" name="ms_ma" value="<?= $m['MS_ma'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Giải ngân ký quỹ</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:var(--primary); font-weight:600; font-size:13px;">✓ Đã thanh toán & Lưu Blockchain</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <details style="margin-top:16px; background:#f8faf9; padding:14px; border-radius:8px; border:1px solid var(--border-color);">
                    <summary style="cursor:pointer; font-weight:700; color:var(--primary);">+ Thêm Cột mốc Mới cho Dự án</summary>
                    <form method="POST" action="actions.php" style="margin-top:12px;">
                        <input type="hidden" name="action" value="add_milestone">
                        <input type="hidden" name="hd_ma" value="<?= $c['HD_ma'] ?>">
                        <div class="form-grid">
                            <div class="form-group"><label>Tên cột mốc *</label><input type="text" name="ms_ten" class="form-control" placeholder="Nhập nội dung công việc..." required></div>
                            <div class="form-group"><label>Hạn hoàn thành</label><input type="date" name="ms_hanhoanthanh" class="form-control" value="2026-12-20" required></div>
                            <div class="form-group"><label>Số tiền ký quỹ ($)</label><input type="number" step="0.01" name="ms_sotien" class="form-control" value="75.00" required></div>
                        </div>
                        <div class="form-group"><label>Ghi chú sản phẩm bàn giao</label><input type="text" name="ms_mota" class="form-control" placeholder="Chi tiết yêu cầu nghiệm thu"></div>
                        <button type="submit" class="btn btn-primary btn-sm">Lưu cột mốc</button>
                    </form>
                </details>

                <details style="margin-top:10px; background:#f8faf9; padding:14px; border-radius:8px; border:1px solid var(--border-color);">
                    <summary style="cursor:pointer; font-weight:700; color:var(--primary);">★ Gửi Đánh giá Mức độ Hài lòng (DANH_GIA)</summary>
                    <form method="POST" action="actions.php" style="margin-top:12px;">
                        <input type="hidden" name="action" value="add_review">
                        <input type="hidden" name="hd_ma" value="<?= $c['HD_ma'] ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Điểm đánh giá công khai (1–5 sao)</label>
                                <select name="dg_diem" class="form-control">
                                    <option value="5">★★★★★ 5.0 (Xuất sắc)</option>
                                    <option value="4">★★★★☆ 4.0 (Tốt)</option>
                                    <option value="3">★★★☆☆ 3.0 (Đạt yêu cầu)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nhận xét chi tiết</label>
                                <input type="text" name="dg_noidung" class="form-control" placeholder="Giao tiếp chuyên nghiệp, bàn giao đúng hạn!" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm">Gửi đánh giá</button>
                    </form>
                </details>
            </div>
            <?php endforeach; ?>

        <?php elseif ($page === 'deliverables'): ?>
            <div class="page-header">
                <div>
                    <h1>Tiến độ Dự án, Bàn giao & Nghiệm thu</h1>
                    <p>Nộp tệp sản phẩm (BAN_GIAO_SAN_PHAM + TEP_TIN) và thực hiện nghiệm thu (NGHIEM_THU)</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Nộp Sản phẩm Bàn giao</h2></div>
                <form method="POST" action="actions.php">
                    <input type="hidden" name="action" value="submit_deliverable">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Chọn cột mốc (MILESTONE)</label>
                            <select name="ms_ma" class="form-control">
                                <?php foreach ($pdo->query("SELECT MS_ma, MS_ten FROM MILESTONE") as $m): ?>
                                    <option value="<?= $m['MS_ma'] ?>">#<?= $m['MS_ma'] ?> — <?= e($m['MS_ten']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tên tệp đính kèm (TEP_TIN)</label>
                            <input type="text" name="tt_ten" class="form-control" placeholder="Ví dụ: ban_giao_hoan_chinh_v2.pdf" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả nội dung bàn giao (BAN_GIAO_SAN_PHAM)</label>
                        <textarea name="bg_mota" rows="2" class="form-control" required>Đã hoàn thành đầy đủ các yêu cầu của cột mốc và đính kèm tệp nguồn.</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Nộp sản phẩm</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h2>Dòng thời gian Bàn giao & Tệp tin đính kèm</h2></div>
                <div class="timeline">
                    <?php
                    $deliverables = $pdo->query("
                        SELECT bg.*, m.MS_ten, t.TT_ten, t.TT_dinhdang, t.TT_kichthuoc 
                        FROM BAN_GIAO_SAN_PHAM bg 
                        JOIN MILESTONE m ON bg.MS_ma = m.MS_ma 
                        LEFT JOIN TEP_TIN t ON t.BG_ma = bg.BG_ma 
                        ORDER BY bg.BG_ma DESC
                    ")->fetchAll();
                    foreach ($deliverables as $d):
                    ?>
                    <div class="timeline-item">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <strong><?= e($d['MS_ten']) ?></strong>
                                <span class="badge <?= $d['BG_trangthai']==='Đã nghiệm thu'?'badge-green':($d['BG_trangthai']==='Yêu cầu chỉnh sửa'?'badge-red':'badge-amber') ?>">
                                    <?= e($d['BG_trangthai']) ?>
                                </span>
                                <p style="font-size:14px; margin:4px 0;"><?= e($d['BG_mota']) ?></p>
                                <div style="font-size:12px; color:var(--text-muted);">
                                    📎 Tệp đính kèm: <strong><?= e($d['TT_ten']) ?></strong> (<?= e($d['TT_dinhdang']) ?>, <?= e($d['TT_kichthuoc']) ?>) • <?= e($d['BG_ngaybangiao']) ?>
                                </div>
                            </div>
                            <?php if ($d['BG_trangthai'] === 'Đã nộp bài'): ?>
                            <form method="POST" action="actions.php" style="display:flex; gap:6px;">
                                <input type="hidden" name="action" value="review_deliverable">
                                <input type="hidden" name="bg_ma" value="<?= $d['BG_ma'] ?>">
                                <button type="submit" name="decision" value="approve" class="btn btn-primary btn-sm">✓ Duyệt nghiệm thu</button>
                                <button type="submit" name="decision" value="revise" class="btn btn-outline btn-sm">↻ Yêu cầu chỉnh sửa</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($page === 'profile'): ?>
            <?php
            $profile = $pdo->query("SELECT * FROM HO_SO_FREELANCER LIMIT 1")->fetch();
            $hs_ma   = $profile['HS_ma'] ?? 1;
            ?>
            <div class="page-header">
                <div>
                    <h1>Hồ sơ Năng lực, Kỹ năng & Portfolio Freelancer</h1>
                    <p>Quản lý thông tin giới thiệu (HO_SO_FREELANCER), kỹ năng (HO_SO_KY_NANG) và dự án mẫu (PORTFOLIO)</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Tổng quan Hồ sơ Chuyên môn</h2></div>
                <form method="POST" action="actions.php">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Chức danh chuyên môn</label>
                            <input type="text" name="hs_tieude" class="form-control" value="<?= e($profile['HS_tieude'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Mức lương mong muốn ($ USD/giờ)</label>
                            <input type="number" step="0.01" name="hs_mucluong" class="form-control" value="<?= e($profile['HS_mucluongmongmuon'] ?? '45.00') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Giới thiệu bản thân (Tối đa 5000 ký tự)</label>
                        <textarea name="hs_gioithieu" rows="4" class="form-control"><?= e($profile['HS_gioithieu'] ?? '') ?></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label>Kinh nghiệm làm việc</label><input type="text" name="hs_kinhnghiem" class="form-control" value="<?= e($profile['HS_kinhnghiem'] ?? '') ?>"></div>
                        <div class="form-group"><label>Học vấn & Bằng cấp</label><input type="text" name="hs_hocvan" class="form-control" value="<?= e($profile['HS_hocvan'] ?? '') ?>"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu hồ sơ</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h2>Chỉnh sửa Kỹ năng Chuyên môn (Tối đa 15 kỹ năng)</h2></div>
                <div style="border:1px solid var(--border-color); border-radius:10px; padding:14px; margin-bottom:14px;">
                    <?php
                    $mySkills = $pdo->query("
                        SELECT hskn.*, kn.KN_ten 
                        FROM HO_SO_KY_NANG hskn 
                        JOIN KY_NANG kn ON hskn.KN_ma = kn.KN_ma 
                        WHERE hskn.HS_ma = $hs_ma
                    ")->fetchAll();
                    foreach ($mySkills as $sk):
                    ?>
                    <span class="skill-pill">
                        <?= e($sk['KN_ten']) ?> (<?= e($sk['HSKN_mucdothanhthao']) ?> • <?= $sk['HSKN_sonamkinhnghiem'] ?> năm)
                        <form method="POST" action="actions.php" style="display:inline;">
                            <input type="hidden" name="action" value="remove_skill">
                            <input type="hidden" name="hskn_ma" value="<?= $sk['HSKN_ma'] ?>">
                            <button type="submit" style="border:none; background:none; cursor:pointer; font-weight:700; color:#555;">✕</button>
                        </form>
                    </span>
                    <?php endforeach; ?>
                </div>
                <form method="POST" action="actions.php" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_skill">
                    <select name="kn_ma" class="form-control" style="max-width:240px;">
                        <?php foreach ($pdo->query("SELECT * FROM KY_NANG") as $kn): ?>
                            <option value="<?= $kn['KN_ma'] ?>"><?= e($kn['KN_ten']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="level" class="form-control" style="max-width:170px;">
                        <option>Chuyên gia</option><option>Trung cấp</option><option>Cơ bản</option>
                    </select>
                    <input type="number" name="years" class="form-control" value="3" min="1" max="30" style="max-width:120px;" placeholder="Số năm">
                    <button type="submit" class="btn btn-outline">+ Thêm kỹ năng</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h2>Dự án Mẫu đã Thực hiện (PORTFOLIO)</h2></div>
                <form method="POST" action="actions.php" style="margin-bottom:20px;">
                    <input type="hidden" name="action" value="add_portfolio">
                    <div class="form-grid">
                        <div class="form-group"><label>Tên dự án mẫu *</label><input type="text" name="pf_ten" class="form-control" placeholder="Nhập tiêu đề ngắn gọn, mô tả rõ dự án" required></div>
                        <div class="form-group"><label>Đường dẫn sản phẩm (URL)</label><input type="text" name="pf_duongdan" class="form-control" placeholder="https://..."></div>
                        <div class="form-group"><label>Đường dẫn ảnh bìa</label><input type="text" name="pf_hinhanh" class="form-control" placeholder="https://..."></div>
                    </div>
                    <div class="form-group"><label>Mô tả dự án *</label><textarea name="pf_mota" rows="2" class="form-control" required></textarea></div>
                    <button type="submit" class="btn btn-primary">Thêm vào Portfolio</button>
                </form>

                <div class="form-grid">
                    <?php foreach ($pdo->query("SELECT * FROM PORTFOLIO WHERE HS_ma = $hs_ma") as $pf): ?>
                    <div style="border:1px solid var(--border-color); border-radius:10px; overflow:hidden;">
                        <img src="<?= e($pf['PF_hinhanh']) ?>" alt="" style="width:100%; height:150px; object-fit:cover;">
                        <div style="padding:14px;">
                            <strong><?= e($pf['PF_ten']) ?></strong>
                            <p style="font-size:13px; color:var(--text-muted); margin-top:4px;"><?= e($pf['PF_mota']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($page === 'disputes'): ?>
            <div class="page-header">
                <div>
                    <h1>Quy trình Giải quyết Khiếu nại Ký quỹ (KHIEU_NAI)</h1>
                    <p>Quy trình hòa giải & trọng tài ký quỹ 4 bước kèm tệp bằng chứng xác thực</p>
                </div>
            </div>

            <div class="dispute-steps">
                <div class="step-box active">
                    <div class="step-header">Bước 1: Gửi khiếu nại</div>
                    <p style="font-size:12px;">Thời hạn: 7 ngày. Gửi nội dung phản hồi kèm toàn bộ bằng chứng.</p>
                </div>
                <div class="step-box">
                    <div class="step-header">Bước 2: Phản hồi đối tác</div>
                    <p style="font-size:12px;">Thời hạn: 5 ngày. Đồng ý hoàn tiền, không phản hồi hoặc chuyển hòa giải.</p>
                </div>
                <div class="step-box">
                    <div class="step-header">Bước 3: Hòa giải & Trọng tài</div>
                    <p style="font-size:12px;">Hòa giải (~2 ngày làm việc) & Trọng tài phân xử (7 ngày).</p>
                </div>
                <div class="step-box">
                    <div class="step-header">Bước 4: Phán quyết cuối cùng</div>
                    <p style="font-size:12px;">Ban hành quyết định ràng buộc và thực thi phân bổ quỹ Escrow.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Khởi tạo Hồ sơ Khiếu nại Ký quỹ</h2></div>
                <form method="POST" action="actions.php">
                    <input type="hidden" name="action" value="file_dispute">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Hợp đồng liên quan (HOP_DONG)</label>
                            <select name="hd_ma" class="form-control">
                                <?php foreach ($pdo->query("SELECT HD_ma FROM HOP_DONG") as $h): ?>
                                    <option value="<?= $h['HD_ma'] ?>">Hợp đồng #<?= $h['HD_ma'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Lý do khiếu nại</label><input type="text" name="lydo" class="form-control" placeholder="Ví dụ: Bất đồng về phạm vi cột mốc" required></div>
                        <div class="form-group"><label>Tệp bằng chứng (BANG_CHUNG_KHIEU_NAI)</label><input type="text" name="tentep" class="form-control" placeholder="bang_chung_trao_doi.pdf"></div>
                    </div>
                    <div class="form-group"><label>Nội dung trình bày chi tiết</label><textarea name="noidung" rows="2" class="form-control" required></textarea></div>
                    <button type="submit" class="btn btn-danger">Gửi khiếu nại ký quỹ</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header"><h2>Danh sách Khiếu nại Đang xử lý & Đã giải quyết</h2></div>
                <table>
                    <thead><tr><th>Mã</th><th>Hợp đồng</th><th>Lý do</th><th>Trạng thái</th><th>Kết quả xử lý</th><th>Thao tác Quản trị viên</th></tr></thead>
                    <tbody>
                        <?php foreach ($pdo->query("SELECT * FROM KHIEU_NAI ORDER BY KhieuNai_ma DESC") as $kn): ?>
                        <tr>
                            <td>#<?= $kn['KhieuNai_ma'] ?></td>
                            <td>Hợp đồng #<?= $kn['HD_ma'] ?></td>
                            <td><strong><?= e($kn['KhieuNai_lydo']) ?></strong><br><small><?= e($kn['KhieuNai_noidung']) ?></small></td>
                            <td><span class="badge badge-amber"><?= e($kn['KhieuNai_trangthai']) ?></span></td>
                            <td><?= e($kn['KhieuNai_ketquaxuly'] ?? 'Đang chờ phán quyết') ?></td>
                            <td>
                                <form method="POST" action="actions.php" style="display:flex; gap:6px;">
                                    <input type="hidden" name="action" value="resolve_dispute">
                                    <input type="hidden" name="khieunai_ma" value="<?= $kn['KhieuNai_ma'] ?>">
                                    <select name="trangthai" class="form-control" style="padding:4px 8px; font-size:12px;">
                                        <option>Bước 2: Phản hồi đối tác</option>
                                        <option>Bước 3: Hòa giải</option>
                                        <option>Bước 4: Phán quyết cuối cùng</option>
                                    </select>
                                    <input type="text" name="ketqua" class="form-control" placeholder="Nội dung phán quyết" style="padding:4px 8px; font-size:12px;" required>
                                    <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page === 'blockchain'): ?>
            <div class="page-header">
                <div>
                    <h1>Chi tiết Giao dịch Blockchain & Nhật ký Hệ thống</h1>
                    <p>Biên lai giao dịch bất biến trên chuỗi khối (GIAO_DICH_BLOCKCHAIN) & lịch sử kiểm toán (NHAT_KY_HE_THONG)</p>
                </div>
            </div>

            <?php
            $latestTx = $pdo->query("SELECT * FROM GIAO_DICH_BLOCKCHAIN ORDER BY GDB_ma DESC LIMIT 1")->fetch();
            if ($latestTx):
            ?>
            <div class="tx-dark-box" style="margin-bottom:22px;">
                <h3 style="color:#fff; margin-bottom:12px; font-family:sans-serif;">Chi tiết Giao dịch Blockchain Mới nhất (Khối #<?= $latestTx['GDB_khoiblock'] ?>)</h3>
                <div class="tx-row"><div class="tx-label">Mã băm (Tx Hash):</div><div class="tx-val"><?= e($latestTx['GDB_hash']) ?></div></div>
                <div class="tx-row"><div class="tx-label">Trạng thái:</div><div class="tx-val"><span class="badge badge-green"><?= e($latestTx['GDB_trangthai']) ?></span></div></div>
                <div class="tx-row"><div class="tx-label">Số khối (Block):</div><div class="tx-val"><?= e((string)$latestTx['GDB_khoiblock']) ?></div></div>
                <div class="tx-row"><div class="tx-label">Ví gửi (From):</div><div class="tx-val"><?= e($latestTx['GDB_vigui']) ?></div></div>
                <div class="tx-row"><div class="tx-label">Ví nhận (To):</div><div class="tx-val"><?= e($latestTx['GDB_vinhan']) ?></div></div>
                <div class="tx-row"><div class="tx-label">Giá trị & Phí Gas:</div><div class="tx-val">$<?= number_format($latestTx['GDB_sotien'], 2) ?> (Phí mạng: <?= $latestTx['GDB_phigiaodich'] ?> ETH)</div></div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><h2>Nhật ký Kiểm toán Hệ thống (NHAT_KY_HE_THONG)</h2></div>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Người thực hiện</th><th>Hành động</th><th>Địa chỉ IP</th><th>Đối tượng</th><th>Mô tả chi tiết</th><th>Thời gian</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $logs = $pdo->query("
                            SELECT l.*, u.ND_ho, u.ND_ten 
                            FROM NHAT_KY_HE_THONG l 
                            LEFT JOIN NGUOI_DUNG u ON l.ND_ma = u.ND_ma 
                            ORDER BY l.NKHT_ma DESC LIMIT 20
                        ")->fetchAll();
                        foreach ($logs as $lg):
                        ?>
                        <tr>
                            <td><?= $lg['NKHT_ma'] ?></td>
                            <td><strong><?= e(($lg['ND_ho'] ?? 'Hệ thống').' '.($lg['ND_ten'] ?? '')) ?></strong></td>
                            <td><span class="badge badge-blue"><?= e($lg['NKHT_hanhdong']) ?></span></td>
                            <td><code><?= e($lg['NKHT_diachiIP']) ?></code></td>
                            <td><code><?= e($lg['NKHT_doituong']) ?></code></td>
                            <td><?= e($lg['NKHT_mota']) ?></td>
                            <td><?= e($lg['NKHT_thoigian']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>