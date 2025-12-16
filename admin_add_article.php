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
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = 'image/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error_message = "Gagal mengupload gambar.";
                $image_name = '';
            }
        } else {
            $error_message = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    // Insert article if no errors
    if (empty($error_message)) {
        $query = "INSERT INTO posts (title, content, author, category, image) 
                  VALUES ('$title', '$content', '$author', '$category', '$image_name')";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Artikel berhasil ditambahkan!";
            // Clear form
            $_POST = array();
        } else {
            $error_message = "Gagal menambahkan artikel: " . mysqli_error($conn);
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
    <title>Tambah Artikel - Admin CMS</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
    <style>
        /* Fallback for Bootstrap Icons */
        .bi-plus-circle::before { content: "+"; }
        .bi-arrow-left::before { content: "←"; }
        .bi-check-circle::before { content: "✓"; }
        .bi-exclamation-triangle::before { content: "⚠️"; }
        .bi-upload::before { content: "⬆️"; }
        
        .admin-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1.5rem 0;
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
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
                    <h1 class="mb-0"><i class="bi-plus-circle me-2"></i>Tambah Artikel Baru</h1>
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
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="author" class="form-label">Penulis *</label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : 'Admin'; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Kategori *</label>
                                <select class="form-control" id="categorySelect" onchange="toggleCustomCategory()">
                                    <option value="">Pilih Kategori</option>
                                    <option value="SANITASI">SANITASI</option>
                                    <option value="TIPS">TIPS</option>
                                    <option value="BISNIS">BISNIS</option>
                                    <option value="SEDOT WC">SEDOT WC</option>
                                    <option value="SANITASI, TIPS">SANITASI, TIPS</option>
                                    <option value="custom">🖊️ Custom Kategori</option>
                                </select>
                                
                                <!-- Hidden input for actual category value -->
                                <input type="hidden" id="category" name="category" value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>" required>
                                
                                <!-- Custom category input (hidden by default) -->
                                <input type="text" class="form-control mt-2" id="customCategory" 
                                       placeholder="Masukkan kategori custom..." 
                                       style="display: none;" 
                                       onkeyup="updateCategoryValue()">
                                
                                <div class="form-text">Pilih dari dropdown atau buat kategori custom</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Artikel</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif" 
                                   onchange="previewImage(this)">
                            <div class="form-text">Format yang didukung: JPG, PNG, GIF. Maksimal 5MB.</div>
                            <img id="imagePreview" class="image-preview" style="display: none;">
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Konten Artikel *</label>
                            <textarea class="form-control" id="content" name="content" rows="15" 
                                      placeholder="Tulis konten artikel di sini..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            <div class="form-text">Gunakan Enter untuk membuat paragraf baru.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="admin_dashboard.php" class="btn btn-secondary">
                                <i class="bi-x-circle me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi-check-circle me-1"></i> Simpan Artikel
                            </button>
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
                customInput.value = '';
                hiddenInput.value = select.value;
            }
        }

        function updateCategoryValue() {
            const customInput = document.getElementById('customCategory');
            const hiddenInput = document.getElementById('category');
            hiddenInput.value = customInput.value;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const existingCategory = document.getElementById('category').value;
            const select = document.getElementById('categorySelect');
            
            if (existingCategory && select.value === '') {
                // Check if existing category is in predefined options
                let found = false;
                for (let option of select.options) {
                    if (option.value === existingCategory) {
                        option.selected = true;
                        found = true;
                        break;
                    }
                }
                
                // If not found in predefined, set as custom
                if (!found && existingCategory !== '') {
                    select.value = 'custom';
                    document.getElementById('customCategory').value = existingCategory;
                    document.getElementById('customCategory').style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>