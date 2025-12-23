<?php
include 'koneksi.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = "SELECT * FROM posts WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Artikel tidak ditemukan!'); window.location.href='blog.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="image/logo-sedotwc3.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $data['title']; ?> - Blog Details</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/global.css" rel="stylesheet">
    <link href="css/about.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tektur:wght@400..900&display=swap" rel="stylesheet">
    
    <style>
        /* Text Wrapping Fix for Blog Detail */
        .blog_dt1 h1 {
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            overflow-wrap: break-word;
            line-height: 1.3;
        }

        .content-text {
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            overflow-wrap: break-word;
            white-space: pre-wrap; /* Preserve line breaks and spaces */
            line-height: 1.7;
            font-size: 16px;
            text-align: justify;
        }

        /* Sidebar text wrapping */
        .blog_1_right b {
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
        }

        /* Responsive image */
        .content-text img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<div id="header-placeholder"></div>

<section id="center" class="center_o bg_light pt-3 pb-3">
 <div class="container-xl">
  <div class="row center_o1">
   <div class="col-md-12">
	  <h6 class="mb-0 col_green"><a href="index.html">Home</a>   <span class="me-2 ms-2"><i class="bi-chevron-right align-middle text-muted"></i></span> <a href="blog.php">Blogs</a> <span class="me-2 ms-2"><i class="bi-chevron-right align-middle text-muted"></i></span> Detail</h6>
   </div>
  </div>
 </div>
</section>

<section id="blog" class="pt-5 pb-5">
 <div class="container-xl">
  <div class="row blog_1">
   <div class="col-md-8">
    <div class="blog_dt">
	  <div class="blog_dt1">
        
        <h1><?php echo $data['title']; ?></h1>
		
        <h6 class="font_15 mt-3 mb-4"> 
            <span><i class="bi-person-fill me-1 align-middle col_green"></i> By <?php echo $data['author']; ?></span>  
            <span class="ms-3"><i class="bi-clock me-1 align-middle col_green"></i> <?php echo date("F d, Y", strtotime($data['created_at'])); ?></span> 
        </h6>
		
        <?php if(!empty($data['image'])): ?>
		    <img src="image/<?php echo $data['image']; ?>" alt="blog image" class="img-fluid w-100 mb-4 rounded">
        <?php endif; ?>

        <div class="content-text mt-4">
            <?php echo nl2br(htmlspecialchars($data['content'])); ?>
        </div>
        
        <div class="blog_dt1_inner row row-cols-1 row-cols-md-2 row-cols-sm-2 mt-4 border-top pt-4 mx-0">
		  <div class="col">
		   <div class="blog_dt1_inner_left">
		      <ul class="mb-0 tags">
                 <li class="d-inline-block me-2">TAGS :</li>
                 <?php 
                    $tags = explode(",", $data['category']);
                    foreach($tags as $tag) {
                        echo '<li class="d-inline-block"><a href="#">'.trim($tag).'</a></li> ';
                    }
                 ?>
             </ul>
		   </div>
		  </div>
		  <div class="col">
           <div class="blog_dt1_inner_right text-end">
		     <ul class="mb-0 social_icon1 font_14">
                <li class="d-inline-block me-2">SHARE :</li>
                <li class="d-inline-block">
                    <a href="#" onclick="shareToFacebook()" title="Share to Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                </li>
                <li class="d-inline-block ms-1">
                    <a href="#" onclick="shareToWhatsApp()" title="Share to WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </li>
			</ul>
		   </div>
		  </div>
		</div>

	  </div>
	</div>
   </div>
   
    <div class="col-md-4">
                    <div class="blog_1_right shadow p-4">
                        <div class="blog_1_right1">
                            <h4 class="line_text mb-4">RECENT POST</h4>
                            <ul class="mb-0">
                                    <?php
                                    // Query Khusus Sidebar: Ambil 4 berita terbaru
                                    $query_recent = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 4";
                                    $result_recent = mysqli_query($conn, $query_recent);

                                    if (mysqli_num_rows($result_recent) > 0) {
                                        while ($row_r = mysqli_fetch_assoc($result_recent)) {
                                            // Setup variabel biar rapi
                                            $id_r = $row_r['id'];
                                            $judul_r = htmlspecialchars($row_r['title']);
                                            $tgl_r = date("M d, Y", strtotime($row_r['created_at']));
                                            $img_r = !empty($row_r['image']) ? "image/" . $row_r['image'] : "image/default.jpg";
                                    ?>

                                            <li class="d-flex border-bottom pb-3 mb-3">
                                                <span>
                                                    <a href="blog_detail.php?id=<?php echo $id_r; ?>">
                                                        <img width="100" class="rounded-3" src="<?php echo $img_r; ?>" alt="img" style="height: 70px; object-fit: cover;">
                                                    </a>
                                                </span>
                                                <span class="flex-column ms-3">
                                                    <b class="d-block mb-1" style="font-size: 14px; line-height: 1.4;">
                                                        <a href="blog_detail.php?id=<?php echo $id_r; ?>" class="text-dark text-decoration-none">
                                                            <?php echo $judul_r; ?>
                                                        </a>
                                                    </b>
                                                    <span class="font_14 d-block text-muted">
                                                        <i class="bi-clock me-1 col_green"></i> <?php echo $tgl_r; ?>
                                                    </span>
                                                </span>
                                            </li>
                                    <?php
                                        } // Tutup While
                                    } // Tutup If
                                    ?>
                            </ul>
                        </div>
                        <div class="blog_1_right1 mt-5">
                            <h4 class="line_text mb-4">FOLLOW US</h4>
                            <ul class="mb-0 social_icon1 font_14">
                                <li class="d-inline-block">
                                    <a href="https://facebook.com/njrmitrasanitasi" target="_blank" title="Follow us on Facebook">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                </li>
                                <li class="d-inline-block ms-2">
                                    <a href="https://wa.me/6285771071415" target="_blank" title="Contact us on WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </li>
                                <li class="d-inline-block ms-2">
                                    <a href="https://instagram.com/njrmitrasanitasi" target="_blank" title="Follow us on Instagram">
                                        <i class="bi bi-instagram"></i>
                                    </a>
                                </li>
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
    // Get current page info for sharing
    const currentUrl = window.location.href;
    const pageTitle = document.title;
    const articleTitle = "<?php echo addslashes($data['title']); ?>";
    const articleContent = "<?php echo addslashes(substr(strip_tags($data['content']), 0, 100)); ?>...";
    
    // Facebook Share Function
    function shareToFacebook() {
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}&quote=${encodeURIComponent(articleTitle + ' - ' + articleContent)}`;
        window.open(facebookUrl, 'facebook-share', 'width=580,height=400,scrollbars=yes,resizable=yes');
        return false;
    }
    
    // WhatsApp Share Function
    function shareToWhatsApp() {
        const whatsappText = `*${articleTitle}*\n\n${articleContent}\n\nBaca selengkapnya: ${currentUrl}`;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(whatsappText)}`;
        window.open(whatsappUrl, '_blank');
        return false;
    }
    
    // Load header and footer
    fetch("header.html").then(r => r.text()).then(d => document.getElementById("header-placeholder").innerHTML = d);
    fetch("footer.php").then(r => r.text()).then(d => document.getElementById("footer-placeholder").innerHTML = d);
</script>
<div id="footer-placeholder"></div>
</body>
</html>