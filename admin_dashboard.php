<?php
session_start();
require_once 'config.php';
include 'koneksi.php';

// Set security headers
setSecurityHeaders();

// Enhanced security checks
function isValidAdminSession() {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > SESSION_TIMEOUT) {
        return false;
    }
    
    // Check IP address consistency (prevent session hijacking)
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
    // Check user agent consistency
    if (isset($_SESSION['admin_user_agent']) && $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    
    return true;
}

// Validate admin session
if (!isValidAdminSession()) {
    session_destroy();
    header('Location: blog.php?error=session_expired');
    exit;
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > SESSION_REGENERATE_INTERVAL) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Handle delete action with CSRF protection
if (isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Token keamanan tidak valid!";
    } else {
        $delete_id = intval($_POST['delete']);
        
        // Get image filename before deleting
        $img_query = "SELECT image FROM posts WHERE id = ?";
        $img_stmt = mysqli_prepare($conn, $img_query);
        mysqli_stmt_bind_param($img_stmt, "i", $delete_id);
        mysqli_stmt_execute($img_stmt);
        $img_result = mysqli_stmt_get_result($img_stmt);
        $img_data = mysqli_fetch_assoc($img_result);
        
        // Delete the record
        $delete_query = "DELETE FROM posts WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            // Delete image file if exists
            if (!empty($img_data['image']) && file_exists("image/" . $img_data['image'])) {
                unlink("image/" . $img_data['image']);
            }
            $success_message = "Artikel berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus artikel.";
        }
        
        mysqli_stmt_close($img_stmt);
        mysqli_stmt_close($delete_stmt);
    }
}

// Get all articles
$query = "SELECT * FROM posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - NJR CMS</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
    <style>
        /* Fallback for Bootstrap Icons if font doesn't load */
        .bi-shield-check::before { content: "🛡️"; }
        .bi-arrow-left::before { content: "←"; }
        .bi-box-arrow-right::before { content: "→"; }
        .bi-check-circle::before { content: "✓"; }
        .bi-exclamation-triangle::before { content: "⚠️"; }
        .bi-file-text::before { content: "📄"; }
        .bi-calendar-day::before { content: "📅"; }
        .bi-plus-circle::before { content: "+"; }
        .bi-eye::before { content: "👁️"; }
        .bi-pencil::before { content: "✏️"; }
        .bi-trash::before { content: "🗑️"; }
        .bi-image::before { content: "🖼️"; }
        .bi-person::before { content: "👤"; }
        .bi-calendar::before { content: "📅"; }
        
        .admin-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem 0;
        }
        
        .article-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }
        
        .article-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .article-image {
            width: 100px;
            height: 70px;
            object-fit: cover;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #28a745;
        }
    </style>
</head>

<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="bi-shield-check me-2"></i>Admin Dashboard</h1>
                    <p class="mb-0 opacity-75">Kelola artikel blog NJR Mitra Sanitasi</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="blog.php" class="btn btn-light me-2">
                        <i class="bi-arrow-left me-1"></i> Kembali ke Blog
                    </a>
                    <a href="admin_logout.php" class="btn btn-outline-light">
                        <i class="bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xl py-5">
        
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1">Total Artikel</h5>
                                <h3 class="mb-0"><?php echo mysqli_num_rows($result); ?></h3>
                            </div>
                            <div class="text-success">
                                <i class="bi-file-text fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-1">Artikel Hari Ini</h5>
                                <h3 class="mb-0">
                                    <?php 
                                    $today_query = "SELECT COUNT(*) as today_count FROM posts WHERE DATE(created_at) = CURDATE()";
                                    $today_result = mysqli_query($conn, $today_query);
                                    echo mysqli_fetch_assoc($today_result)['today_count'];
                                    ?>
                                </h3>
                            </div>
                            <div class="text-info">
                                <i class="bi-calendar-day fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Kelola Artikel</h2>
                    <a href="admin_add_article.php" class="btn btn-success">
                        <i class="bi-plus-circle me-1"></i> Tambah Artikel Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Articles List -->
        <div class="row">
            <div class="col-12">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="card article-card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="image/<?php echo $row['image']; ?>" 
                                                 alt="Article Image" 
                                                 class="article-image rounded">
                                        <?php else: ?>
                                            <div class="article-image rounded bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                        <p class="text-muted mb-1">
                                            <i class="bi-person me-1"></i><?php echo $row['author']; ?>
                                            <span class="ms-3"><i class="bi-calendar me-1"></i><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                        </p>
                                        <p class="card-text text-truncate">
                                            <?php echo substr(strip_tags($row['content']), 0, 100) . '...'; ?>
                                        </p>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <span class="badge bg-secondary"><?php echo $row['category']; ?></span>
                                    </div>
                                    
                                    <div class="col-md-2 text-end">
                                        <div class="btn-group" role="group">
                                            <a href="blog_detail.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-outline-info btn-action" 
                                               target="_blank" 
                                               title="Lihat">
                                                <i class="bi-eye"></i>
                                            </a>
                                            <a href="admin_edit_article.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-outline-warning btn-action" 
                                               title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                                <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-action" title="Hapus">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi-file-text text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">Belum ada artikel</h4>
                        <p class="text-muted">Mulai dengan menambahkan artikel pertama Anda.</p>
                        <a href="admin_add_article.php" class="btn btn-success">
                            <i class="bi-plus-circle me-1"></i> Tambah Artikel
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>