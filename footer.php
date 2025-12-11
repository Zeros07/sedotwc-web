<?php include 'koneksi.php'; ?>
<section id="footer" class="pt-5 pb-4 bg_back1">
    <div class="container-xl">
        <div class="row footer_1">

            <!-- KIRI -->
            <div class="col-md-4">
                <div class="footer_1_left">
                    <b class="fs-3 d-block text-white mb-4">
                        Kami adalah penyedia layanan sanitasi profesional yang memberikan solusi cepat dan terpercaya.
                    </b>

                    <div class="input-group border_dark">
                        <input type="text" class="form-control bg-transparent border-0 text-white"
                            placeholder="Masukkan Email Anda">
                        <span class="input-group-btn">
                            <button class="btn btn-primary bg_green py-3 px-4 border-0" type="button">
                                <i class="bi bi-send"></i>
                            </button>
                        </span>
                    </div>

                    <ul class="justify-content-between mb-0 mt-4 d-flex">
                        <li>
                            <b class="d-block text-white fs-3">Hubungi Kami</b>
                            <span class="d-block mt-3 text-white-50">0857-7107-1415</span>
                            <span class="d-block mt-3 text-white-50">(021) 1234-5678</span>
                        </li>

                        <li>
                            <b class="d-block text-white fs-3">Alamat</b>
                            <span class="d-block mt-3 text-white-50">
                                Jakarta & Tangerang <br>
                                Layanan 24 Jam Siap Datang
                            </span>
                        </li>
                    </ul>

                </div>
            </div>

            <!-- KANAN: BERITA TERBARU -->
            <div class="col-md-4">
                <div class="footer_1_left">
                    <b class="fs-3 d-block text-white mb-4">Berita Terbaru</b>

                    <ul class="mb-0 ps-2">
                        <?php
            // 1. Query ambil 3 artikel terbaru
            $query_footer = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 3";
            $result_footer = mysqli_query($conn, $query_footer);
            
            // Hitung jumlah data biar kita bisa atur garis bawah (border)
            $total_data = mysqli_num_rows($result_footer);
            $no = 0;

            if ($total_data > 0) {
                while ($row = mysqli_fetch_assoc($result_footer)) {
                    $no++; // Penanda urutan (1, 2, 3)

                    // Setup Variabel
                    $id_f = $row['id'];
                    $judul_f = htmlspecialchars($row['title']);
                    $tgl_f = date("M d, Y", strtotime($row['created_at']));
                    $img_f = !empty($row['image']) ? "image/" . $row['image'] : "image/default.jpg";

                    // Logika CSS: Kalau bukan item terakhir, kasih garis bawah (border-bottom)
                    // Kalau item terakhir, kosongkan class border-nya
                    $class_border = ($no < $total_data) ? "border-bottom pb-3 mb-3" : "";
            ?>

                        <li class="d-flex <?php echo $class_border; ?>">
                            <span>
                                <a href="blog_detail.php?id=<?php echo $id_f; ?>">
                                    <img width="90" class="rounded-3" src="<?php echo $img_f; ?>" alt="img"
                                        style="height: 60px; object-fit: cover;">
                                </a>
                            </span>
                            <span class="flex-column ms-3">
                                <span class="font_14 text-white d-block">
                                    <i class="bi-clock me-1 col_green"></i>
                                    <?php echo $tgl_f; ?>
                                </span>
                                <b class="d-block mt-1 text-white-50">
                                    <a href="blog_detail.php?id=<?php echo $id_f; ?>"
                                        class="text-white-50 text-decoration-none">
                                        <?php echo $judul_f; ?>
                                    </a>
                                </b>
                            </span>
                        </li>
                        <?php 
                } // Tutup While
            } else {
                echo "<li class='text-white-50'>Belum ada berita.</li>";
            }
            ?>
                    </ul>
                </div>
            </div>


            <!-- TENGAH: GOOGLE MAPS (KOLom KECIL) -->
            <div class="col-md-3">
                <div class="footer_1_left ps-md-4">
                    <b class="fs-3 d-block text-white mb-4">Lokasi Kami</b>

                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31722.960885785167!2d106.65340557431641!3d-6.346095700000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69e5a6e26dc3cd%3A0xccd6344b8021119d!2sPamulang%20University%20Campus%202%20(UNPAM%20Viktor)!5e0!3m2!1sen!2sid!4v1765208938046!5m2!1sen!2sid"
                        width="100%" height="180" style="border-radius:10px; border:0;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>




        </div>

        <!-- BAGIAN COPYRIGHT & SOSIAL MEDIA -->
        <div class="row footer_2 border-top mt-4 pt-4 mx-0">
            <div class="col-md-8">
                <p class="mb-0 text-white-50">© 2025 NJR Mitra Sanitasi Utama</p>
            </div>

            <div class="col-md-4 text-end">
                <ul class="mb-0 social_icon1 font_14">
                    <li class="d-inline-block">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                    </li>
                    <li class="d-inline-block ms-2">
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                    </li>
                    <li class="d-inline-block ms-2">
                        <a href="#"><i class="bi bi-instagram"></i></a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</section>