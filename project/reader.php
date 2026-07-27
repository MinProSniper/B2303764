<?php
ob_start();
session_start();
require_once 'database.php';

$mangaBaseDir = 'manga/';

if (!file_exists($mangaBaseDir)) {
    mkdir($mangaBaseDir, 0755, true);
}

// --- USER SESSION & ADMIN STATUS ---
$userId = null;
$isAdmin = false;

if (isset($_SESSION['username'])) {
    $currentSessionUser = $_SESSION['username'];

    if ($currentSessionUser === 'root' || $currentSessionUser === 'admin') {
        $isAdmin = true;
    }

    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->execute([$currentSessionUser]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userRow) {
        $userId = $userRow['id'];
    } else {
        if ($isAdmin) {
            $userId = 0; 
        } else {
            unset($_SESSION['username']);
        }
    }
}

// --- AUTHENTICATION PROCESSOR ---
$authError = "";
$openModalTarget = "";

if (isset($_POST['signup'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password']; 
    $confirmPass = $_POST['confirm_password'] ?? ''; 
    
    if ($user === '') {
        $authError = "Username cannot be left blank!";
        $openModalTarget = "signUpModal";
    } elseif ($pass !== $confirmPass) {
        $authError = "Passwords do not match!";
        $openModalTarget = "signUpModal";
    } else {
        $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$user, $hashedPass]);
            $_SESSION['username'] = $user;
            header("Location: reader.php");
            exit;
        } catch (PDOException $e) {
            $authError = "Username already exists!";
            $openModalTarget = "signUpModal";
        }
    }
}

if (isset($_POST['signin'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'] ?? ''; 
    
    if (($user === 'root' || $user === 'admin') && $pass === '') {
        $_SESSION['username'] = $user;
        header("Location: reader.php");
        exit;
    }

    if ($user !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbUser && password_verify($pass, $dbUser['password'])) {
            $_SESSION['username'] = $dbUser['username'];
            header("Location: reader.php");
            exit;
        } else {
            $authError = "Invalid username or password!";
            $openModalTarget = "signInModal";
        }
    } else {
        $authError = "Username cannot be left blank!";
        $openModalTarget = "signInModal";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: reader.php");
    exit;
}

// --- FETCH ALL GLOBAL TAGS ---
$allTagsStmt = $pdo->query("SELECT * FROM tags ORDER BY name ASC");
$allTags = $allTagsStmt->fetchAll(PDO::FETCH_ASSOC);

// --- CORE MANGA UTILITIES ---
$mangas = array_filter(glob($mangaBaseDir . '*'), 'is_dir');
natsort($mangas);
$mangas = array_values($mangas);

$isDashboard = !isset($_GET['manga']) && !isset($_GET['admin_panel']);
$isAdminView = isset($_GET['admin_panel']) && $isAdmin;
$isReadingMode = isset($_GET['read']) && $_GET['read'] === 'true';

$currentMangaIndex = isset($_GET['manga']) ? (int)$_GET['manga'] : 0;
if (!isset($mangas[$currentMangaIndex])) { $currentMangaIndex = 0; }
$currentMangaPath = $mangas[$currentMangaIndex] ?? null;

// Helper function to get tags assigned to a manga folder
function getMangaTags($pdo, $mangaPath) {
    if (!$mangaPath) return [];
    $folder = basename($mangaPath);
    $stmt = $pdo->prepare("
        SELECT t.id, t.name 
        FROM tags t 
        JOIN manga_tags mt ON t.id = mt.tag_id 
        WHERE mt.manga_folder = ? 
        ORDER BY t.name ASC
    ");
    $stmt->execute([$folder]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pre-fetch tags for all mangas (for grid view)
$mangaTagsMap = [];
if (!empty($mangas)) {
    $folders = array_map('basename', $mangas);
    $inClause = implode(',', array_fill(0, count($folders), '?'));
    $stmt = $pdo->prepare("
        SELECT mt.manga_folder, t.id, t.name 
        FROM tags t 
        JOIN manga_tags mt ON t.id = mt.tag_id 
        WHERE mt.manga_folder IN ($inClause)
        ORDER BY t.name ASC
    ");
    $stmt->execute($folders);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mangaTagsMap[$row['manga_folder']][] = $row;
    }
}

// --- BOOKMARK ACTION HANDLER ---
if ($userId && isset($_GET['action']) && $_GET['action'] === 'toggle_bookmark' && $currentMangaPath) {
    $mangaFolder = basename($currentMangaPath);
    
    $checkStmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND manga_folder = ?");
    $checkStmt->execute([$userId, $mangaFolder]);
    
    if ($checkStmt->fetch()) {
        $delStmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND manga_folder = ?");
        $delStmt->execute([$userId, $mangaFolder]);
    } else {
        $insStmt = $pdo->prepare("INSERT INTO bookmarks (user_id, manga_folder) VALUES (?, ?)");
        $insStmt->execute([$userId, $mangaFolder]);
    }
    header("Location: reader.php?manga=" . $currentMangaIndex);
    exit;
}

$bookmarkedFolders = [];
if ($userId) {
    $bStmt = $pdo->prepare("SELECT manga_folder FROM bookmarks WHERE user_id = ?");
    $bStmt->execute([$userId]);
    $bookmarkedFolders = $bStmt->fetchAll(PDO::FETCH_COLUMN);
}

$isCurrentBookmarked = false;
if ($currentMangaPath) {
    $isCurrentBookmarked = in_array(basename($currentMangaPath), $bookmarkedFolders);
}

// --- CHAPTERS & PAGES CALCULATION ---
$chapters = [];
if ($currentMangaPath) {
    $chapters = array_filter(glob($currentMangaPath . '/*'), 'is_dir');
    natsort($chapters);
    $chapters = array_values($chapters);
}

$currentChIndex = isset($_GET['ch']) ? (int)$_GET['ch'] : 0;
if (!isset($chapters[$currentChIndex])) { $currentChIndex = 0; }
$currentChapterPath = $chapters[$currentChIndex] ?? null;

$pages = [];
if ($currentChapterPath) {
    $pages = glob($currentChapterPath . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
    natsort($pages);
    $pages = array_values($pages);
}

$currentPageIndex = isset($_GET['page']) ? (int)$_GET['page'] : 0;
if (!isset($pages[$currentPageIndex])) { $currentPageIndex = 0; }
$currentPageImg = $pages[$currentPageIndex] ?? null;

$nextPageUrl = null; $prevPageUrl = null;
if ($currentMangaPath) {
    if (isset($pages[$currentPageIndex + 1])) {
        $nextPageUrl = "?manga=$currentMangaIndex&ch=$currentChIndex&page=" . ($currentPageIndex + 1) . "&read=true#manga-reader";
    } elseif (isset($chapters[$currentChIndex + 1])) {
        $nextPageUrl = "?manga=$currentMangaIndex&ch=" . ($currentChIndex + 1) . "&page=0&read=true#manga-reader";
    }
    if (isset($pages[$currentPageIndex - 1])) {
        $prevPageUrl = "?manga=$currentMangaIndex&ch=$currentChIndex&page=" . ($currentPageIndex - 1) . "&read=true#manga-reader";
    } elseif ($currentChIndex > 0) {
        $prevChapterIndex = $currentChIndex - 1;
        $prevChapterPages = glob($chapters[$prevChapterIndex] . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
        $lastPageIndex = count($prevChapterPages) - 1;
        $prevPageUrl = "?manga=$currentMangaIndex&ch=$prevChapterIndex&page=" . ($lastPageIndex >= 0 ? $lastPageIndex : 0) . "&read=true#manga-reader";
    }
}

// --- ADMIN HANDLERS ---
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $isImage = function($tmpName) {
        $info = @getimagesize($tmpName);
        return $info !== false;
    };

    // 1. Create New Tag
    if (isset($_POST['add_global_tag'])) {
        $tagName = trim($_POST['new_tag_name']);
        if (!empty($tagName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                $stmt->execute([$tagName]);
                header("Location: reader.php?admin_panel=true"); exit;
            } catch (PDOException $e) {
                $authError = "Tag already exists!";
            }
        }
    }

    // 2. Delete Global Tag
    if (isset($_POST['delete_global_tag'])) {
        $tagId = (int)$_POST['tag_id'];
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
        $stmt->execute([$tagId]);
        header("Location: reader.php?admin_panel=true"); exit;
    }

    // 3. Update Manga Tags
    if (isset($_POST['update_manga_tags'])) {
        $targetManga = basename($_POST['target_manga']);
        $selectedTags = $_POST['manga_tags'] ?? [];

        // Clear existing tags for this manga
        $delStmt = $pdo->prepare("DELETE FROM manga_tags WHERE manga_folder = ?");
        $delStmt->execute([$targetManga]);

        // Re-insert selected tags
        if (!empty($selectedTags)) {
            $insStmt = $pdo->prepare("INSERT INTO manga_tags (manga_folder, tag_id) VALUES (?, ?)");
            foreach ($selectedTags as $tagId) {
                $insStmt->execute([$targetManga, (int)$tagId]);
            }
        }
        header("Location: reader.php?admin_panel=true"); exit;
    }

    // 4. Create New Manga
    if (isset($_POST['create_manga'])) {
        $newMangaName = preg_replace('/[^A-Za-z0-9_\- ]/', '', trim($_POST['new_manga_name']));
        $newMangaName = str_replace(' ', '_', $newMangaName);
        if (!empty($newMangaName)) {
            $mangaPath = $mangaBaseDir . $newMangaName;
            if (!file_exists($mangaPath)) {
                mkdir($mangaPath, 0755, true);
                if (!empty($_POST['manga_desc'])) { file_put_contents($mangaPath . '/info.txt', trim($_POST['manga_desc'])); }
                
                if (isset($_FILES['manga_cover']) && $_FILES['manga_cover']['error'] === UPLOAD_ERR_OK) {
                    if ($isImage($_FILES['manga_cover']['tmp_name'])) {
                        $ext = strtolower(pathinfo($_FILES['manga_cover']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            move_uploaded_file($_FILES['manga_cover']['tmp_name'], $mangaPath . '/cover.' . $ext);
                        }
                    }
                }

                // Attach tags if selected during creation
                if (!empty($_POST['initial_tags'])) {
                    $insStmt = $pdo->prepare("INSERT INTO manga_tags (manga_folder, tag_id) VALUES (?, ?)");
                    foreach ($_POST['initial_tags'] as $tagId) {
                        $insStmt->execute([$newMangaName, (int)$tagId]);
                    }
                }

                header("Location: reader.php?admin_panel=true"); exit;
            } else { $authError = "A manga folder with that name already exists!"; }
        }
    }

    // 5. Upload Chapter
    if (isset($_POST['upload_chapter'])) {
        $targetManga = basename($_POST['target_manga']);
        $chapterName = preg_replace('/[^A-Za-z0-9_\- ]/', '', trim($_POST['chapter_name']));
        $chapterName = str_replace(' ', '_', $chapterName);
        
        $targetPath = $mangaBaseDir . $targetManga;
        
        if (file_exists($targetPath) && is_dir($targetPath) && !empty($chapterName)) {
            $chapterPath = $targetPath . '/' . $chapterName;
            if (!file_exists($chapterPath)) { mkdir($chapterPath, 0755, true); }
            if (isset($_FILES['chapter_panels'])) {
                $files = $_FILES['chapter_panels'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK && $isImage($files['tmp_name'][$i])) {
                        $fileName = basename($files['name'][$i]);
                        move_uploaded_file($files['tmp_name'][$i], $chapterPath . '/' . $fileName);
                    }
                }
                header("Location: reader.php?admin_panel=true"); exit;
            }
        }
    }

    // 6. Delete Manga
    if (isset($_POST['delete_manga'])) {
        $targetManga = basename($_POST['delete_target_manga']);
        $targetPath = $mangaBaseDir . $targetManga;
        
        if (file_exists($targetPath) && is_dir($targetPath)) {
            $delBookmarks = $pdo->prepare("DELETE FROM bookmarks WHERE manga_folder = ?");
            $delBookmarks->execute([$targetManga]);

            $delMangaTags = $pdo->prepare("DELETE FROM manga_tags WHERE manga_folder = ?");
            $delMangaTags->execute([$targetManga]);
            
            if (deleteFolderRecursive($targetPath)) {
                header("Location: reader.php?admin_panel=true");
                exit;
            } else {
                $authError = "Failed to completely clear out files.";
            }
        }
    }
}

function cleanName($path) { return str_replace('_', ' ', basename($path)); }

function getMangaCover($mangaPath) {
    $coverFiles = glob($mangaPath . '/cover.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
    return !empty($coverFiles) ? $coverFiles[0] : 'https://via.placeholder.com/300x420/2b2b2b/ffffff?text=No+Cover';
}

function getMangaDescription($mangaPath) {
    $infoFile = $mangaPath . '/info.txt';
    return file_exists($infoFile) ? file_get_contents($infoFile) : "";
}

function deleteFolderRecursive($dir) {
    if (!file_exists($dir)) { return true; }
    if (!is_dir($dir)) { return unlink($dir); }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') { continue; }
        if (!deleteFolderRecursive($dir . DIRECTORY_SEPARATOR . $item)) { return false; }
    }
    return rmdir($dir);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Basement</title>
    <style>
        html, body { background-color: #121212; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        header { background-color: #1a1a1a; width: 100%; padding: 12px 20px; box-sizing: border-box; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2d2d2d; position: sticky; top: 0; z-index: 100; }
        h1 a { color: #ffffff; text-decoration: none; font-size: 1.3rem; font-weight: bold; }
        
        .header-right { display: flex; align-items: center; gap: 20px; }
        .selector-group { display: flex; gap: 10px; }
        select { background: #2b2b2b; color: #fff; padding: 8px 14px; border: 1px solid #444; border-radius: 6px; font-size: 15px; cursor: pointer; max-width: 150px; text-overflow: ellipsis; }
        
        .auth-group { display: flex; gap: 10px; align-items: center; }
        .btn-auth { background: transparent; color: #fff; border: 1px solid #555; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; white-space: nowrap; }
        .btn-auth.primary { background: #ff4500; border-color: #ff4500; }
        .btn-auth.admin { background: #6f42c1; border-color: #6f42c1; text-decoration: none; display: inline-block; color: white; text-align: center; line-height: 18px; }
        .btn-auth:hover { opacity: 0.9; transform: translateY(-1px); }
        .welcome-msg { font-size: 14px; color: #aaa; white-space: nowrap; }
        .logout-link { color: #ff4500; text-decoration: none; margin-left: 10px; font-weight: bold; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: #1a1a1a; padding: 30px; border-radius: 10px; width: 90%; max-width: 360px; border: 1px solid #333; position: relative; box-sizing: border-box; }
        .modal-content h3 { margin-top: 0; font-size: 1.5rem; margin-bottom: 20px; }
        .modal-content input { width: 100%; padding: 12px; margin-bottom: 15px; background: #2b2b2b; border: 1px solid #444; border-radius: 6px; color: #fff; box-sizing: border-box; font-size: 15px; }
        .modal-content .btn-submit { width: 100%; padding: 12px; background: #ff4500; border: none; color: white; font-weight: bold; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .close-modal { position: absolute; top: 15px; right: 15px; color: #aaa; cursor: pointer; font-size: 20px; }
        
        .js-error-text { color: #ff6b6b; font-size: 13px; display: none; margin-top: -10px; margin-bottom: 15px; }

        .dashboard-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .shelf-title { font-size: 1.4rem; color: #ff4500; margin-bottom: 20px; font-weight: bold; border-left: 4px solid #ff4500; padding-left: 10px; user-select: none; cursor: default; }
        
        /* Search Box Styles (Right Aligned) */
        .search-container { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .search-form { display: flex; gap: 10px; width: 100%; max-width: 400px; }
        .search-input { flex: 1; background: #1a1a1a; border: 1px solid #333; padding: 10px 15px; border-radius: 6px; color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .search-input:focus { border-color: #ff4500; }
        .btn-clear { background: #333; color: #ccc; border: 1px solid #444; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 14px; display: none; align-items: center; justify-content: center; cursor: pointer; }
        .btn-clear:hover { background: #444; color: #fff; }

        .manga-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; margin-bottom: 45px; }
        @media (min-width: 600px) { .manga-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; } }
        .manga-card { display: flex; flex-direction: column; text-decoration: none; color: #fff; background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #2d2d2d; transition: transform 0.2s, border-color 0.2s; }
        .manga-card:hover { transform: translateY(-4px); border-color: #ff4500; }
        .cover-wrapper { position: relative; width: 100%; padding-top: 140%; }
        .cover-wrapper img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
        .manga-title { padding: 10px 10px 4px 10px; font-size: 0.9rem; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }

        /* Tag Styling */
        .tag-list { display: flex; flex-wrap: wrap; gap: 5px; padding: 0 10px 10px 10px; }
        .tag-badge { background: #333; color: #ff8c00; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600; border: 1px solid #444; }
        .overview-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
        .overview-tags .tag-badge { font-size: 13px; padding: 4px 12px; background: #2a2a2a; color: #ff8c00; border-color: #555; }

        .no-results-msg { color: #aaa; margin-top: 10px; display: none; }

        .overview-container { max-width: 1000px; margin: 20px auto; padding: 20px; box-sizing: border-box; }
        .overview-header { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 20px; margin-bottom: 30px; }
        @media (min-width: 600px) { .overview-header { flex-direction: row; text-align: left; align-items: flex-start; gap: 30px; } }
        .overview-cover { width: 180px; flex-shrink: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.5); border-radius: 6px; }
        .overview-details { display: flex; flex-direction: column; justify-content: center; width: 100%; }
        .overview-name { font-size: 2rem; font-weight: bold; margin: 0 0 10px 0; }
        .action-row { display: flex; gap: 15px; align-items: center; justify-content: center; }
        @media (min-width: 600px) { .action-row { justify-content: flex-start; } }
        .btn-read { background-color: #ff4500; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 10px rgba(255, 69, 0, 0.3); }
        .btn-bookmark { display: inline-flex; align-items: center; background: #2b2b2b; color: #fff; text-decoration: none; padding: 12px 18px; border-radius: 6px; font-size: 15px; font-weight: bold; border: 1px solid #444; transition: background 0.2s; }
        .btn-bookmark.active { background: #e6b800; color: #121212; border-color: #e6b800; }
        .overview-desc { font-size: 1rem; line-height: 1.6; color: #ddd; border-top: 1px solid #2d2d2d; padding-top: 20px; white-space: pre-wrap; }

        .reader-body, .reader-body body { height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
        .main-container { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; padding: 0; box-sizing: border-box; min-height: 0; position: relative; }
        .viewer { height: 100%; width: 100%; display: flex; justify-content: center; align-items: center; min-height: 0; position: relative; }
        .viewer img { max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; box-shadow: 0 4px 20px rgba(0,0,0,0.7); }
        .page-info { position: absolute; bottom: 15px; background: rgba(0, 0, 0, 0.7); padding: 5px 15px; border-radius: 20px; color: #ccc; font-size: 13px; pointer-events: none; z-index: 10; }

        .nav-side-btn {
            position: absolute; top: 0; bottom: 0; width: 18%; max-width: 100px;
            display: flex; align-items: center; justify-content: center; color: rgba(255, 255, 255, 0.4);
            font-size: 2.2rem; text-decoration: none; z-index: 20; -webkit-tap-highlight-color: transparent;
            user-select: none; transition: background 0.2s, color 0.2s;
        }
        .nav-side-btn.left { left: 0; background: linear-gradient(to right, rgba(0,0,0,0.4), transparent); }
        .nav-side-btn.right { right: 0; background: linear-gradient(to left, rgba(0,0,0,0.4), transparent); }
        .nav-side-btn:active, .nav-side-btn:hover { color: #ffffff; background: rgba(0, 0, 0, 0.6); }
        .nav-side-btn.disabled { display: none; }

        .admin-container { max-width: 800px; margin: 20px auto; padding: 20px; background: #1a1a1a; border-radius: 8px; border: 1px solid #2d2d2d; box-sizing: border-box; }
        .admin-section { margin-bottom: 35px; padding-bottom: 25px; border-bottom: 1px solid #333; }
        .admin-section h3 { margin-top: 0; color: #ff4500; font-size: 1.3rem; }
        .admin-form label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #bbb; }
        .admin-form input[type="text"], .admin-form textarea, .admin-form select { width: 100%; padding: 10px; background: #2b2b2b; border: 1px solid #444; border-radius: 6px; color: #fff; box-sizing: border-box; margin-bottom: 15px; font-family: inherit; font-size: 15px;}
        .admin-form input[type="file"] { margin-bottom: 15px; display: block; color: #ccc; }
        .btn-admin-submit { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; }

        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-bottom: 15px; background: #222; padding: 12px; border-radius: 6px; border: 1px solid #333; }
        .checkbox-item { display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer; }
        .checkbox-item input { cursor: pointer; }

        .tag-management-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .tag-manage-item { display: inline-flex; align-items: center; gap: 8px; background: #2b2b2b; padding: 6px 12px; border-radius: 20px; border: 1px solid #444; font-size: 14px; }
        .tag-manage-item form { display: inline; }
        .btn-tag-del { background: transparent; border: none; color: #ff4500; font-weight: bold; cursor: pointer; font-size: 16px; padding: 0 4px; }
        .btn-tag-del:hover { color: #ff0000; }
    </style>
</head>
<body class="<?= $isReadingMode ? 'reader-body' : '' ?>">

<header>
    <h1><a href="reader.php">The Manga Basement</a></h1>
    <div class="header-right">
        <?php if (!$isAdminView): ?>
        <div class="selector-group">
            <select onchange="location = this.value;">
                <option value="reader.php" <?= $isDashboard ? 'selected' : '' ?>>-</option>
                <?php foreach ($mangas as $index => $path): ?>
                    <option value="?manga=<?= $index ?>" <?= (!$isDashboard && $index === $currentMangaIndex) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(cleanName($path)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!$isDashboard && !empty($chapters)): ?>
                <select onchange="location = this.value;">
                    <?php foreach ($chapters as $index => $path): ?>
                        <option value="?manga=<?= $currentMangaIndex ?>&ch=<?= $index ?><?= $isReadingMode ? '&read=true' : '' ?>" <?= $index === $currentChIndex ? 'selected' : '' ?>>
                            <?= htmlspecialchars(cleanName($path)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="auth-group">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="welcome-msg">Hi, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <?php if ($isAdmin): ?><a href="?admin_panel=true" class="btn-auth admin">⚙️ Admin</a><?php endif; ?>
                <a href="?logout=true" class="logout-link">Logout</a>
            <?php else: ?>
                <button class="btn-auth" onclick="openModal('signInModal')">Sign In</button>
                <button class="btn-auth primary" onclick="openModal('signUpModal')">Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- SIGN IN MODAL -->
<div id="signInModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal('signInModal')">&times;</span>
        <h3>Sign In</h3>
        <?php if (!empty($authError) && $openModalTarget === 'signInModal'): ?>
            <div style="background: #d9534f; color: white; padding: 8px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; text-align: center;">
                <?= htmlspecialchars($authError) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password">
            <button type="submit" name="signin" class="btn-submit">Login</button>
        </form>
    </div>
</div>

<!-- SIGN UP MODAL -->
<div id="signUpModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal('signUpModal')">&times;</span>
        <h3>Create Account</h3>
        <?php if (!empty($authError) && $openModalTarget === 'signUpModal'): ?>
            <div style="background: #d9534f; color: white; padding: 8px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; text-align: center;">
                <?= htmlspecialchars($authError) ?>
            </div>
        <?php endif; ?>
        <form id="signupForm" method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" id="signup_password" name="password" placeholder="Password" required>
            <input type="password" id="signup_confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <div id="jsPasswordError" class="js-error-text">Passwords do not match!</div>
            <button type="submit" name="signup" class="btn-submit">Register</button>
        </form>
    </div>
</div>

<?php if ($isAdminView): ?>
    <!-- VIEW: ADMIN DASHBOARD PANEL -->
    <div class="admin-container">
        <h2>🛠️ Admin Management Console</h2>
        <?php if (!empty($authError) && $openModalTarget === ''): ?>
            <div style="background: #d9534f; color: white; padding: 10px; margin-bottom: 20px; border-radius: 6px; font-weight: bold;">
                <?= htmlspecialchars($authError) ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: GLOBAL TAG MANAGEMENT -->
        <div class="admin-section">
            <h3>🏷️ 1. Global Tag Library</h3>
            <form class="admin-form" method="POST" style="display: flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="new_tag_name" placeholder="New Tag Name (e.g. Isekai, Sci-Fi)" required style="margin-bottom: 0;">
                <button type="submit" name="add_global_tag" class="btn-admin-submit" style="white-space: nowrap;">Add Tag</button>
            </form>
            
            <div class="tag-management-list">
                <?php foreach ($allTags as $tag): ?>
                    <div class="tag-manage-item">
                        <span><?= htmlspecialchars($tag['name']) ?></span>
                        <form method="POST" onsubmit="return confirm('Delete this tag? It will be removed from all assigned manga.');">
                            <input type="hidden" name="tag_id" value="<?= $tag['id'] ?>">
                            <button type="submit" name="delete_global_tag" class="btn-tag-del">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SECTION 2: EDIT MANGA TAGS -->
        <div class="admin-section">
            <h3>🏷️ 2. Edit Tags for Specific Manga</h3>
            <form class="admin-form" method="POST">
                <label>Select Manga Series:</label>
                <select name="target_manga" id="manga_tag_select" onchange="loadMangaTags(this.value)" required>
                    <option value="">-- Choose Manga --</option>
                    <?php foreach ($mangas as $path): ?>
                        <option value="<?= htmlspecialchars(basename($path)) ?>"><?= htmlspecialchars(cleanName($path)) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Select Assigned Tags:</label>
                <div class="checkbox-grid">
                    <?php foreach ($allTags as $tag): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="manga_tags[]" value="<?= $tag['id'] ?>" class="manga-tag-checkbox" data-tag-id="<?= $tag['id'] ?>">
                            <?= htmlspecialchars($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="update_manga_tags" class="btn-admin-submit">Save Manga Tags</button>
            </form>
        </div>
        
        <!-- SECTION 3: CREATE MANGA -->
        <div class="admin-section">
            <h3>3. Create New Manga</h3>
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <input type="text" name="new_manga_name" placeholder="e.g. One Piece" required>
                <textarea name="manga_desc" rows="3" placeholder="Enter storyline metadata summary..."></textarea>
                
                <label>Assign Tags:</label>
                <div class="checkbox-grid">
                    <?php foreach ($allTags as $tag): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="initial_tags[]" value="<?= $tag['id'] ?>">
                            <?= htmlspecialchars($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <input type="file" name="manga_cover" accept="image/*">
                <button type="submit" name="create_manga" class="btn-admin-submit">Create Manga Series</button>
            </form>
        </div>
        
        <!-- SECTION 4: UPLOAD CHAPTER -->
        <div class="admin-section">
            <h3>4. Upload Chapter Sheets</h3>
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <select name="target_manga" required>
                    <?php foreach ($mangas as $path): ?><option value="<?= $path ?>"><?= htmlspecialchars(cleanName($path)) ?></option><?php endforeach; ?>
                </select>
                <input type="text" name="chapter_name" placeholder="e.g. Chapter_01" required>
                <input type="file" name="chapter_panels[]" accept="image/*" multiple required>
                <button type="submit" name="upload_chapter" class="btn-admin-submit">Upload Entire Chapter</button>
            </form>
        </div>

        <!-- SECTION 5: DELETE MANGA -->
        <div class="admin-section" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">
            <h3 style="color: #d9534f;">5. Delete Manga Series</h3>
            <form class="admin-form" method="POST" onsubmit="return confirm('WARNING: This will permanently delete this manga, all its uploaded chapters, images, and user bookmarks. This cannot be undone! Are you absolutely sure?');">
                <label for="delete_target_manga">Select Series to Destroy:</label>
                <select name="delete_target_manga" id="delete_target_manga" required>
                    <option value="">-- Choose Manga to Delete --</option>
                    <?php foreach ($mangas as $path): ?>
                        <option value="<?= $path ?>"><?= htmlspecialchars(cleanName($path)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_manga" class="btn-admin-submit" style="background: #d9534f;">Permanently Delete Series</button>
            </form>
        </div>
    </div>

    <!-- JS Helper to dynamically check boxes when selecting a manga in Section 2 -->
    <script>
    const mangaTagsData = <?= json_encode($mangaTagsMap) ?>;

    function loadMangaTags(folderName) {
        const checkboxes = document.querySelectorAll('.manga-tag-checkbox');
        checkboxes.forEach(cb => cb.checked = false);

        if (folderName && mangaTagsData[folderName]) {
            const assignedIds = mangaTagsData[folderName].map(t => t.id.toString());
            checkboxes.forEach(cb => {
                if (assignedIds.includes(cb.getAttribute('data-tag-id'))) {
                    cb.checked = true;
                }
            });
        }
    }
    </script>

<?php elseif ($isDashboard): ?>
    <!-- VIEW 1: HOME GRID -->
    <div class="dashboard-container">
        <!-- User's Bookmarks Shelf -->
        <?php if ($userId && !empty($bookmarkedFolders)): ?>
            <div class="shelf-title" id="bookmarks-title">⭐ My Bookmarked Manga</div>
            <div class="manga-grid" id="bookmarks-grid">
                <?php foreach ($mangas as $index => $path): 
                    $folder = basename($path);
                    if (in_array($folder, $bookmarkedFolders)): ?>
                    <a href="?manga=<?= $index ?>" class="manga-card">
                        <div class="cover-wrapper"><img src="<?= htmlspecialchars(getMangaCover($path)) ?>" alt="Cover"></div>
                        <div class="manga-title"><?= htmlspecialchars(cleanName($path)) ?></div>
                        <?php if (!empty($mangaTagsMap[$folder])): ?>
                            <div class="tag-list">
                                <?php foreach (array_slice($mangaTagsMap[$folder], 0, 3) as $tag): ?>
                                    <span class="tag-badge"><?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endif; endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- SEARCH BAR RIGHT-ALIGNED ABOVE ALL COLLECTIONS -->
        <div class="search-container">
            <div class="search-form">
                <input type="text" id="liveSearchInput" class="search-input" placeholder="Search title or tag..." autocomplete="off">
                <button type="button" id="clearSearchBtn" class="btn-clear" onclick="clearSearch()">Clear</button>
            </div>
        </div>

        <!-- Main Catalog -->
        <div class="shelf-title">📖 All Collections</div>
        
        <p id="noResultsMsg" class="no-results-msg">No manga found matching your search.</p>

        <div class="manga-grid" id="catalog-grid">
            <?php foreach ($mangas as $index => $path): 
                $folder = basename($path); ?>
                <a href="?manga=<?= $index ?>" class="manga-card">
                    <div class="cover-wrapper"><img src="<?= htmlspecialchars(getMangaCover($path)) ?>" alt="Cover"></div>
                    <div class="manga-title"><?= htmlspecialchars(cleanName($path)) ?></div>
                    <?php if (!empty($mangaTagsMap[$folder])): ?>
                        <div class="tag-list">
                            <?php foreach (array_slice($mangaTagsMap[$folder], 0, 3) as $tag): ?>
                                <span class="tag-badge"><?= htmlspecialchars($tag['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- REAL-TIME LIVE SEARCH SCRIPT -->
    <script>
    const searchInput = document.getElementById('liveSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const catalogCards = document.querySelectorAll('#catalog-grid .manga-card');
    const bookmarkCards = document.querySelectorAll('#bookmarks-grid .manga-card');
    const bookmarksTitle = document.getElementById('bookmarks-title');

    function filterManga() {
        const query = searchInput.value.toLowerCase().trim();
        
        // Show/hide Clear button
        clearBtn.style.display = query !== '' ? 'inline-flex' : 'none';

        let catalogMatches = 0;

        // Filter Catalog Cards
        catalogCards.forEach(card => {
            const title = card.querySelector('.manga-title').textContent.toLowerCase();
            const tags = Array.from(card.querySelectorAll('.tag-badge')).map(t => t.textContent.toLowerCase());
            
            const matchesTitle = title.includes(query);
            const matchesTags = tags.some(tag => tag.includes(query));

            if (matchesTitle || matchesTags) {
                card.style.display = 'flex';
                catalogMatches++;
            } else {
                card.style.display = 'none';
            }
        });

        // Filter Bookmark Cards (if present)
        let bookmarkMatches = 0;
        bookmarkCards.forEach(card => {
            const title = card.querySelector('.manga-title').textContent.toLowerCase();
            const tags = Array.from(card.querySelectorAll('.tag-badge')).map(t => t.textContent.toLowerCase());

            if (title.includes(query) || tags.some(tag => tag.includes(query))) {
                card.style.display = 'flex';
                bookmarkMatches++;
            } else {
                card.style.display = 'none';
            }
        });

        // Hide bookmarks section title if no bookmarks match search
        if (bookmarksTitle) {
            bookmarksTitle.style.display = bookmarkMatches > 0 || query === '' ? 'block' : 'none';
        }

        // Display "No results" message if no catalog items match
        noResultsMsg.style.display = catalogMatches === 0 ? 'block' : 'none';
    }

    function clearSearch() {
        searchInput.value = '';
        filterManga();
        searchInput.focus();
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterManga);
        searchInput.addEventListener('keyup', filterManga);
    }
    </script>

<?php elseif (!$isReadingMode): ?>
    <!-- VIEW 2: MANGA OVERVIEW -->
    <?php $currentTags = getMangaTags($pdo, $currentMangaPath); ?>
    <div class="overview-container">
        <div class="overview-header">
            <img class="overview-cover" src="<?= htmlspecialchars(getMangaCover($currentMangaPath)) ?>" alt="Cover">
            <div class="overview-details">
                <h2 class="overview-name">【<?= htmlspecialchars(cleanName($currentMangaPath)) ?>】</h2>
                
                <?php if (!empty($currentTags)): ?>
                    <div class="overview-tags">
                        <?php foreach ($currentTags as $tag): ?>
                            <span class="tag-badge"><?= htmlspecialchars($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="action-row">
                    <a href="?manga=<?= $currentMangaIndex ?>&ch=<?= $currentChIndex ?>&page=0&read=true" class="btn-read">▶ Read Now</a>
                    <?php if ($userId): ?>
                        <a href="?manga=<?= $currentMangaIndex ?>&action=toggle_bookmark" class="btn-bookmark <?= $isCurrentBookmarked ? 'active' : '' ?>">
                            <?= $isCurrentBookmarked ? '★ Bookmarked' : '☆ Add to Bookmarks' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="overview-desc"><?= nl2br(htmlspecialchars(getMangaDescription($currentMangaPath))) ?></div>
    </div>

<?php else: ?>
    <!-- VIEW 3: IMMERSIVE READER MODE -->
    <div class="main-container" id="manga-reader">
        <div class="viewer">
            <a id="prev-btn" href="<?= $prevPageUrl ?? '#' ?>" class="nav-side-btn left <?= !$prevPageUrl ? 'disabled' : '' ?>">❮</a>
            
            <?php if ($currentPageImg): ?>
                <img src="<?= htmlspecialchars($currentPageImg) ?>" alt="Page">
            <?php else: ?>
                <p>No images found in this folder layout.</p>
            <?php endif; ?>

            <a id="next-btn" href="<?= $nextPageUrl ?? '#' ?>" class="nav-side-btn right <?= !$nextPageUrl ? 'disabled' : '' ?>">❯</a>
        </div>
        <div class="page-info"><?php if (count($pages) > 0): ?><?= $currentPageIndex + 1 ?> / <?= count($pages) ?><?php endif; ?></div>
    </div>
    <script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowRight') { 
            const nextBtn = document.getElementById('next-btn');
            if (nextBtn && !nextBtn.classList.contains('disabled')) { nextBtn.click(); }
        } else if (event.key === 'ArrowLeft') { 
            const prevBtn = document.getElementById('prev-btn');
            if (prevBtn && !prevBtn.classList.contains('disabled')) { prevBtn.click(); }
        }
    });
    </script>
<?php endif; ?>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = 'none'; } }

window.addEventListener('DOMContentLoaded', (event) => {
    const targetModal = "<?= $openModalTarget ?>";
    if (targetModal) { openModal(targetModal); }
});

const signupForm = document.getElementById('signupForm');
const signupPassword = document.getElementById('signup_password');
const signupConfirmPassword = document.getElementById('signup_confirm_password');
const jsPasswordError = document.getElementById('jsPasswordError');

if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
        if (signupPassword.value !== signupConfirmPassword.value) {
            e.preventDefault();
            jsPasswordError.style.display = 'block';
            signupConfirmPassword.focus();
        } else {
            jsPasswordError.style.display = 'none';
        }
    });

    signupConfirmPassword.addEventListener('input', function() {
        if (signupPassword.value === signupConfirmPassword.value) {
            jsPasswordError.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
<?php
ob_end_flush();
?>