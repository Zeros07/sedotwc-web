<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="image/logo-sedotwc3.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NJR MITRA SANITASI UTAMA - Blog</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link href="css/blog.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tektur:wght@400..900&display=swap" rel="stylesheet">

    <style>
        @media (max-width: 575.98px) {
            .btn.admin-btn-sm {
                padding: 0.5rem 0.8rem !important;
                font-size: 0.8rem !important;
            }
        }

        .blog_h1_left {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .blog_h1_left:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .blog_h1_left1 img {
            transition: transform 0.5s ease-in-out;
            object-fit: cover;
            height: 400px;
            width: 100%;
        }

        .blog_h1_left:hover .blog_h1_left1 img {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>

    <section id="center" class="center_o bg_light pt-3 pb-3">
        <div class="container-xl">
            <div class="row center_o1 d-flex justify-content-between align-items-center">
                <div class="col-auto me-auto">
                    <h6 class="mb-0 col_green">
                        <a href="index.html">Home</a> <span class="me-2 ms-2"><i class="bi-chevron-right align-middle text-muted"></i></span> Blogs
                    </h6>
                </div>
                <div class="col-auto">
                    <button class="btn button bg_green text-white py-2 px-3 admin-btn-sm" onclick="checkPassword()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Artikel
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section id="blog" class="pt-5 pb-5">
        <div class="container-xl">
            <div class="row blog_1">
                <div class="col-md-8">
                    <div class="blog_1_left">

                        <?php
                        // --- KODE PHP LOOPING ARTIKEL ---
                        $query = "SELECT * FROM posts ORDER BY created_at DESC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id = $row['id'];
                                $judul = htmlspecialchars($row['title']);
                                $tanggal = date("F d, Y", strtotime($row['created_at']));
                                $gambar = !empty($row['image']) ? "image/" . $row['image'] : "image/default.jpg";
                                $cuplikan = substr(strip_tags($row['content']), 0, 200) . "...";
                                $penulis = $row['author'];
                        ?>

                                <div class="blog_h1_left mb-5">
                                    <div class="blog_h1_left1 position-relative" style="overflow: hidden;">
                                        <a href="blog_detail.php?id=<?php echo $id; ?>">
                                            <img src="<?php echo $gambar; ?>" class="img-fluid" alt="<?php echo $judul; ?>">
                                        </a>
                                        <h6 class="mb-0 bg_green text-white py-3 px-4 font_15 position-absolute bottom-0">
                                            <span><i class="bi-person-fill me-1 align-middle"></i> By <?php echo $penulis; ?></span>
                                            <span class="ms-3"><i class="bi-clock me-1 align-middle"></i> <?php echo $tanggal; ?></span>
                                        </h6>
                                    </div>

                                    <div class="blog_h1_left2 border_light p-4">
                                        <h1>
                                            <a href="blog_detail.php?id=<?php echo $id; ?>">
                                                <?php echo $judul; ?>
                                            </a>
                                        </h1>

                                        <p class="mt-3"><?php echo $cuplikan; ?></p>

                                        <h5 class="mb-0 mt-4"><a href="blog_detail.php?id=<?php echo $id; ?>">Read More <i class="bi-arrow-right ms-1 align-middle"></i></a></h5>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<div class='alert alert-info'>Belum ada artikel yang diposting.</div>";
                        }
                        ?>

                    </div>
                </div>

                <div class="col-md-4">
                    <div class="blog_1_right shadow p-4">
                        <div class="blog_1_right1">
                            <h4 class="line_text mb-4">RECENT POST</h4>
                            <ul class="mb-0">
                                <?php
                                // QUERY PENTING: "LIMIT 4" artinya cuma ambil 4 data teratas
                                $query_sidebar = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 4";
                                $result_sidebar = mysqli_query($conn, $query_sidebar);

                                if (mysqli_num_rows($result_sidebar) > 0) {
                                    while ($row = mysqli_fetch_assoc($result_sidebar)) {
                                        // Siapkan data
                                        $id_sidebar = $row['id'];
                                        $judul_sidebar = htmlspecialchars($row['title']);
                                        $tgl_sidebar = date("M d, Y", strtotime($row['created_at']));
                                        $img_sidebar = !empty($row['image']) ? "image/" . $row['image'] : "image/default.jpg";
                                ?>
                                        <li class="d-flex border-bottom pb-3 mb-3">
                                            <span>
                                                <a href="blog_detail.php?id=<?php echo $id_sidebar; ?>">
                                                    <img width="100" class="rounded-3" src="<?php echo $img_sidebar; ?>"
                                                        alt="img" style="height: 70px; object-fit: cover;">
                                                </a>
                                            </span>

                                            <span class="flex-column ms-3">
                                                <b class="d-block mb-1" style="font-size: 15px; line-height: 1.4;">
                                                    <a href="blog_detail.php?id=<?php echo $id_sidebar; ?>" class="text-dark text-decoration-none">
                                                        <?php echo $judul_sidebar; ?>
                                                    </a>
                                                </b>
                                                <span class="font_14 d-block text-muted mt-1">
                                                    <i class="bi-clock me-1 col_green"></i> <?php echo $tgl_sidebar; ?>
                                                </span>
                                            </span>
                                        </li>
                                <?php
                                    }
                                } else {
                                    echo "<p class='text-muted'>Belum ada postingan terbaru.</p>";
                                }
                                ?>
                            </ul>
                            </ul>
                        </div>
                        <div class="blog_1_right1 mt-5">
                            <h4 class="line_text mb-4">FOLLOW US</h4>
                            <ul class="mb-0 social_icon1 font_14">
                                <li class="d-inline-block"><a href="#"><i class="bi bi-facebook"></i></a></li>
                                <li class="d-inline-block ms-2"><a href="#"><i class="bi bi-whatsapp"></i></a></li>
                                <li class="d-inline-block ms-2"><a href="#"><i class="bi bi-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/theme.min.js"></script>
    <script>
        function checkPassword() {
            const correctPassword = "adminnjr";
            const enteredPassword = prompt("Masukkan password Admin:");
            if (enteredPassword === correctPassword) {
                window.location.href = "add_article.html";
            } else if (enteredPassword !== null) {
                alert("Password salah.");
            }
        }

        // Panggil Header Footer
        fetch("header.html").then(r => r.text()).then(d => document.getElementById("header-placeholder").innerHTML = d);
        fetch("footer.php").then(r => r.text()).then(d => document.getElementById("footer-placeholder").innerHTML = d);
    </script>
    <div id="footer-placeholder"></div>
</body>

</html>