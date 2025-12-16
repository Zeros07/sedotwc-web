<?php
session_start();
require_once 'config.php';
include 'koneksi.php';

// Set security headers
setSecurityHeaders();

// Enhanced security checks
function isValidAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > SESSION_TIMEOUT) {
        return false;
    }
    
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
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

// Generate CSRF token
$csrf_token = generateCSRFToken();

$success_message = '';
$error_message = '';

// Get article ID
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id == 0) {
    header('Location: admin_dashboard.php');
    exit;
}

// Get article data
$query = "SELECT * FROM posts WHERE id = $article_id";
$result = mysqli_query($conn, $query);
$article = mysqli_fetch_assoc($result);

if (!$article) {
    header('Location: admin_dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Token keamanan tidak valid!";
    } else {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $author = mysqli_real_escape_string($conn, $_POST['author']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $image_name = $article['image']; // Keep existing image by default
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Delete old image if exists
            if (!empty($article['image']) && file_exists("image/" . $article['image'])) {
                unlink("image/" . $article['image']);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = 'image/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error_message = "Gagal mengupload gambar.";
                $image_name = $article['image']; // Revert to old image
            }
        } else {
            $error_message = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    // Update article if no errors
    if (empty($error_message)) {
        $query = "UPDATE posts SET 
                  title = '$title', 
                  content = '$content', 
                  author = '$author', 
                  category = '$category', 
                  image = '$image_name' 
                  WHERE id = $article_id";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Artikel berhasil diperbarui!";
            // Refresh article data
            $query = "SELECT * FROM posts WHERE id = $article_id";
            $result = mysqli_query($conn, $query);
            $article = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Gagal memperbarui artikel: " . mysqli_error($conn);
        }
    }
    } // Close CSRF check
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Artikel - Admin CMS</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
    <style>
        /* Fallback for Bootstrap Icons */
        .bi-pencil-square::before { content: "✏️"; }
        .bi-arrow-left::before { content: "←"; }
        .bi-check-circle::before { content: "✓"; }
        .bi-exclamation-triangle::before { content: "⚠️"; }
        .bi-upload::before { content: "⬆️"; }
        
        .admin-header {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            padding: 1.5rem 0;
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .current-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="bi-pencil me-2"></i>Edit Artikel</h1>
                    <p class="mb-0 opacity-75">ID: <?php echo $article_id; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="admin_dashboard.php" class="btn btn-light">
                        <i class="bi-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    
                    <!-- Alert Messages -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi-check-circle me-2"></i><?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Artikel *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($article['title']); ?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="author" class="form-label">Penulis *</label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo htmlspecialchars($article['author']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Kategori *</label>
                                <select class="form-control" id="categorySelect" onchange="toggleCustomCategory()">
                                    <option value="">Pilih Kategori</option>
                                    <option value="SANITASI" <?php echo ($article['category'] == 'SANITASI') ? 'selected' : ''; ?>>SANITASI</option>
                                    <option value="TIPS" <?php echo ($article['category'] == 'TIPS') ? 'selected' : ''; ?>>TIPS</option>
                                    <option value="BISNIS" <?php echo ($article['category'] == 'BISNIS') ? 'selected' : ''; ?>>BISNIS</option>
                                    <option value="SEDOT WC" <?php echo ($article['category'] == 'SEDOT WC') ? 'selected' : ''; ?>>SEDOT WC</option>
                                    <option value="SANITASI, TIPS" <?php echo ($article['category'] == 'SANITASI, TIPS') ? 'selected' : ''; ?>>SANITASI, TIPS</option>
                                    <option value="custom" <?php 
                                        $predefined = ['SANITASI', 'TIPS', 'BISNIS', 'SEDOT WC', 'SANITASI, TIPS'];
                                        echo !in_array($article['category'], $predefined) ? 'selected' : ''; 
                                    ?>>🖊️ Custom Kategori</option>
                                </select>
                                
                                <!-- Hidden input for actual category value -->
                                <input type="hidden" id="category" name="category" value="<?php echo htmlspecialchars($article['category']); ?>" required>
                                
                                <!-- Custom category input -->
                                <input type="text" class="form-control mt-2" id="customCategory" 
                                       placeholder="Masukkan kategori custom..." 
                                       value="<?php 
                                           $predefined = ['SANITASI', 'TIPS', 'BISNIS', 'SEDOT WC', 'SANITASI, TIPS'];
                                           echo !in_array($article['category'], $predefined) ? htmlspecialchars($article['category']) : ''; 
                                       ?>"
                                       style="display: <?php 
                                           $predefined = ['SANITASI', 'TIPS', 'BISNIS', 'SEDOT WC', 'SANITASI, TIPS'];
                                           echo !in_array($article['category'], $predefined) ? 'block' : 'none'; 
                                       ?>;" 
                                       onkeyup="updateCategoryValue()">
                                
                                <div class="form-text">Pilih dari dropdown atau buat kategori custom</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Artikel</label>
                            
                            <?php if (!empty($article['image'])): ?>
                                <div class="mb-2">
                                    <p class="text-muted mb-1">Gambar saat ini:</p>
                                    <img src="image/<?php echo $article['image']; ?>" 
                                         alt="Current Image" class="current-image">
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif" 
                                   onchange="previewImage(this)">
                            <div class="form-text">
                                Format yang didukung: JPG, PNG, GIF. Maksimal 5MB. 
                                <?php echo !empty($article['image']) ? 'Kosongkan jika tidak ingin mengubah gambar.' : ''; ?>
                            </div>
                            <img id="imagePreview" class="image-preview" style="display: none;">
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Konten Artikel *</label>
                            <textarea class="form-control" id="content" name="content" rows="15" 
                                      placeholder="Tulis konten artikel di sini..." required><?php echo htmlspecialchars($article['content']); ?></textarea>
                            <div class="form-text">Gunakan Enter untuk membuat paragraf baru.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="admin_dashboard.php" class="btn btn-secondary">
                                <i class="bi-x-circle me-1"></i> Batal
                            </a>
                            <div>
                                <a href="blog_detail.php?id=<?php echo $article_id; ?>" 
                                   class="btn btn-info me-2" target="_blank">
                                    <i class="bi-eye me-1"></i> Preview
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi-check-circle me-1"></i> Update Artikel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        function toggleCustomCategory() {
            const select = document.getElementById('categorySelect');
            const customInput = document.getElementById('customCategory');
            const hiddenInput = document.getElementById('category');
            
            if (select.value === 'custom') {
                customInput.style.display = 'block';
                customInput.focus();
                hiddenInput.value = customInput.value;
            } else {
                customInput.style.display = 'none';
                if (select.value !== 'custom') {
                    hiddenInput.value = select.value;
                }
            }
        }

        function updateCategoryValue() {
            const customInput = document.getElementById('customCategory');
            const hiddenInput = document.getElementById('category');
            hiddenInput.value = customInput.value;
        }
    </script>
</body>
</html>