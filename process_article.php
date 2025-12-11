<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title    = $_POST['title'];
    $category = $_POST['category'];
    $content  = $_POST['content'];
    $author   = "Admin"; 

    // --- PROSES UPLOAD GAMBAR ---
    $imageName = "";
    if (isset($_FILES['article_image']) && $_FILES['article_image']['error'] == 0) {
        $targetDir = "image/"; 
        // Ganti nama file dengan angka unik (biar ga bentrok)
        $fileName = time() . '_' . basename($_FILES["article_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        // Pindahkan file
        if (move_uploaded_file($_FILES["article_image"]["tmp_name"], $targetFilePath)) {
            $imageName = $fileName;
        } else {
            echo "<script>alert('Gagal upload gambar!'); window.history.back();</script>";
            exit;
        }
    }

    // --- SIMPAN KE DATABASE ---
    $query = "INSERT INTO posts (title, category, image, content, author) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $title, $category, $imageName, $content, $author);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
                alert('Artikel berhasil diterbitkan!'); 
                window.location.href = 'blog.php'; 
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>