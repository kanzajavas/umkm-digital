<?php
session_start();
include 'koneksi.php';

/* =========================
   SEARCH & KATEGORI
========================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

$where = [];

if ($search != '') {
    $search_safe = mysqli_real_escape_string($koneksi, $search);

    $where[] = "(nama LIKE '%$search_safe%'
                OR deskripsi LIKE '%$search_safe%')";
}

if ($kategori != '') {
    $kategori_safe = mysqli_real_escape_string($koneksi, $kategori);

    $where[] = "kategori = '$kategori_safe'";
}

$where_sql = '';

if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}


/* =========================
   DATA PRODUK
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_produk
     $where_sql
     ORDER BY id DESC"
);


/* =========================
   DATA KATEGORI
========================= */

$query_kategori = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kategori
     FROM tb_produk
     WHERE kategori IS NOT NULL
     AND kategori != ''
     ORDER BY kategori ASC"
);


/* =========================
   JUMLAH KERANJANG
========================= */

$jumlah_keranjang = 0;

if (isset($_SESSION['keranjang'])) {

    foreach ($_SESSION['keranjang'] as $item) {
        $jumlah_keranjang += $item['jumlah'];
    }
}

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UMKM Digital | Jasa Digital</title>

    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <style>
        /* =========================
           GLOBAL
        ========================= */

        body {
            background: #f7f9fc;
            color: #1f2937;
            transition: background .3s, color .3s;
        }

        a {
            text-decoration: none;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            transition: .3s;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
        }

        .navbar-brand span {
            color: #0d6efd;
        }

        .nav-link {
            font-weight: 500;
            color: #555;
            margin: 0 6px;
        }

        .nav-link:hover {
            color: #0d6efd;
        }


        /* =========================
           THEME BUTTON
        ========================= */

        .theme-toggle {
            width: 42px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            transition: .3s;
        }


        /* =========================
           CART
        ========================= */

        .cart-button {
            position: relative;
        }

        .cart-badge {
            position: absolute;

            top: -7px;
            right: -7px;

            background: #dc3545;
            color: white;

            width: 20px;
            height: 20px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 11px;
            font-weight: bold;
        }


        /* =========================
           HERO CAROUSEL
        ========================= */

        .hero-section {
            padding: 35px 0 20px;
        }

        .carousel-item {
            height: 430px;
        }

        .hero-slide {
            height: 100%;

            border-radius: 25px;

            padding: 60px;

            display: flex;
            align-items: center;

            background:
                linear-gradient(135deg,
                    #0d6efd,
                    #4d94ff);

            color: white;

            overflow: hidden;
        }

        .hero-slide.second {
            background:
                linear-gradient(135deg,
                    #111827,
                    #374151);
        }

        .hero-slide.third {
            background:
                linear-gradient(135deg,
                    #198754,
                    #45b879);
        }

        .hero-content {
            max-width: 700px;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
        }

        .hero-text {
            font-size: 17px;
            opacity: .9;
            max-width: 620px;
        }

        .hero-icon {
            font-size: 150px;
            opacity: .2;
        }


        /* =========================
           SEARCH
        ========================= */

        .search-area {
            padding: 30px 0;
        }

        .search-card {
            background: white;

            padding: 20px;

            border-radius: 18px;

            box-shadow:
                0 5px 25px rgba(0, 0, 0, .05);

            transition: .3s;
        }


        /* =========================
           SECTION
        ========================= */

        .section-title {
            font-size: 32px;
            font-weight: 800;
        }

        .section-subtitle {
            color: #6c757d;
        }


        /* =========================
           CATEGORY
        ========================= */

        .category-btn {
            border-radius: 50px;
            padding: 8px 18px;
            margin: 4px;
        }


        /* =========================
           PRODUCT CARD
        ========================= */

        .product-card {
            border: none;
            border-radius: 18px;

            overflow: hidden;

            background: white;

            box-shadow:
                0 5px 25px rgba(0, 0, 0, .05);

            transition: .3s;
        }

        .product-card:hover {
            transform: translateY(-6px);

            box-shadow:
                0 15px 35px rgba(0, 0, 0, .10);
        }

        .product-image {
            height: 210px;
            width: 100%;

            object-fit: cover;
        }

        .no-image {
            height: 210px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eef2f7;
            color: #adb5bd;

            font-size: 50px;
        }

        .product-card .card-body {
            padding: 22px;
        }

        .product-category {
            color: #0d6efd;
            font-size: 13px;
            font-weight: 600;
        }

        .product-name {
            font-size: 20px;
            font-weight: 700;
        }

        .product-description {
            color: #6c757d;
            font-size: 14px;

            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;

            overflow: hidden;
        }

        .product-price {
            font-size: 20px;
            font-weight: 800;
            color: #0d6efd;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 50px;

            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background: #e8f7ee;
            color: #198754;
        }

        .status-empty {
            background: #fff0f0;
            color: #dc3545;
        }


        /* =========================
           INFORMATION
        ========================= */

        .info-section {
            padding: 70px 0;
        }

        .info-card {
            background: white;

            border-radius: 18px;

            padding: 30px;

            height: 100%;

            box-shadow:
                0 5px 25px rgba(0, 0, 0, .04);

            transition: .3s;
        }

        .info-icon {
            width: 55px;
            height: 55px;

            border-radius: 14px;

            background: #eaf2ff;
            color: #0d6efd;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 25px;

            margin-bottom: 18px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            margin-top: 40px;

            background: #111827;

            color: white;

            padding: 50px 0 25px;
        }

        footer p {
            color: #9ca3af;
        }


        /* =========================
           DARK MODE
        ========================= */

        [data-bs-theme="dark"] body {
            background: #111827;
            color: #f3f4f6;
        }

        [data-bs-theme="dark"] .navbar {
            background: rgba(17, 24, 39, .96);
        }

        [data-bs-theme="dark"] .navbar-brand {
            color: #f3f4f6;
        }

        [data-bs-theme="dark"] .nav-link {
            color: #d1d5db;
        }

        [data-bs-theme="dark"] .nav-link:hover {
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] .search-card,
        [data-bs-theme="dark"] .product-card,
        [data-bs-theme="dark"] .info-card {
            background: #1f2937;
            color: #f3f4f6;
        }

        [data-bs-theme="dark"] .section-subtitle {
            color: #9ca3af;
        }

        [data-bs-theme="dark"] .product-description {
            color: #9ca3af;
        }

        [data-bs-theme="dark"] .no-image {
            background: #374151;
            color: #9ca3af;
        }

        [data-bs-theme="dark"] .info-icon {
            background: #263b5c;
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] .form-control {
            background: #111827;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #9ca3af;
        }

        [data-bs-theme="dark"] .theme-toggle {
            color: #facc15;
            border-color: #6b7280;
        }

        [data-bs-theme="dark"] .theme-toggle:hover {
            color: #111827;
            background: #facc15;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .carousel-item {
                height: 500px;
            }

            .hero-slide {
                padding: 35px;
            }

            .hero-title {
                font-size: 35px;
            }

            .hero-icon {
                display: none;
            }

        }
    </style>

</head>


<body>


    <!-- ==================================================
         NAVBAR
    ================================================== -->

    <nav class="navbar navbar-expand-lg sticky-top border-bottom">

        <div class="container">

            <a class="navbar-brand" href="france.php">

                UMKM<span>Digital</span>

            </a>


            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">

                <span class="navbar-toggler-icon"></span>

            </button>


            <div class="collapse navbar-collapse" id="navbarMenu">


                <!-- MENU -->

                <ul class="navbar-nav mx-auto">

                    <li class="nav-item">

                        <a class="nav-link" href="france.php">
                            Home
                        </a>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link" href="#layanan">
                            Layanan
                        </a>

                    </li>


                    <?php if (isset($_SESSION['username'])): ?>

                        <li class="nav-item">

                            <a class="nav-link" href="profile.php">
                                Profile
                            </a>

                        </li>

                    <?php endif; ?>

                </ul>


                <!-- AKSI -->

                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">


                    <!-- THEME -->

                    <button type="button" class="btn btn-outline-secondary theme-toggle" id="themeToggle"
                        title="Ganti tema">

                        <i class="bi bi-moon-fill" id="themeIcon"></i>

                    </button>


                    <!-- KERANJANG -->

                    <a href="keranjang.php" class="btn btn-outline-primary cart-button">

                        <i class="bi bi-cart3"></i>

                        <span class="d-none d-sm-inline">
                            Keranjang
                        </span>


                        <?php if ($jumlah_keranjang > 0): ?>

                            <span class="cart-badge">

                                <?= $jumlah_keranjang; ?>

                            </span>

                        <?php endif; ?>

                    </a>


                    <?php if (isset($_SESSION['username'])): ?>


                        <span class="text-muted ms-2">

                            <i class="bi bi-person-circle"></i>

                            <?= htmlspecialchars(
                                $_SESSION['username']
                            ); ?>

                        </span>


                        <a href="logout.php" class="btn btn-danger" onclick="return confirm('Yakin ingin logout?')">

                            Logout

                        </a>


                    <?php else: ?>


                        <a href="login.php" class="btn btn-primary">

                            Login

                        </a>


                        <a href="register.php" class="btn btn-outline-primary">

                            Daftar

                        </a>


                    <?php endif; ?>


                </div>

            </div>

        </div>

    </nav>


    <!-- ==================================================
         CAROUSEL
    ================================================== -->

    <section class="hero-section">

        <div class="container">

            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">


                <!-- INDICATOR -->

                <div class="carousel-indicators">

                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>

                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>

                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>

                </div>


                <div class="carousel-inner">


                    <!-- SLIDE 1 -->

                    <div class="carousel-item active">

                        <div class="hero-slide">

                            <div class="hero-content">

                                <h1 class="hero-title">

                                    Solusi Digital
                                    untuk Bisnis Kamu

                                </h1>

                                <p class="hero-text mt-3">

                                    Temukan berbagai layanan digital
                                    untuk membantu mengembangkan
                                    bisnis dan kebutuhan kamu.

                                </p>

                                <a href="#layanan" class="btn btn-light btn-lg mt-3">

                                    Lihat Layanan

                                    <i class="bi bi-arrow-right ms-2"></i>

                                </a>

                            </div>

                            <div class="ms-auto">

                                <i class="bi bi-laptop hero-icon"></i>

                            </div>

                        </div>

                    </div>


                    <!-- SLIDE 2 -->

                    <div class="carousel-item">

                        <div class="hero-slide second">

                            <div class="hero-content">

                                <h1 class="hero-title">

                                    Desain yang
                                    Menarik

                                </h1>

                                <p class="hero-text mt-3">

                                    Buat kebutuhan promosi bisnis
                                    kamu terlihat lebih profesional
                                    dengan layanan desain digital.

                                </p>

                                <a href="#layanan" class="btn btn-light btn-lg mt-3">

                                    Jelajahi Layanan

                                </a>

                            </div>

                            <div class="ms-auto">

                                <i class="bi bi-palette hero-icon"></i>

                            </div>

                        </div>

                    </div>


                    <!-- SLIDE 3 -->

                    <div class="carousel-item">

                        <div class="hero-slide third">

                            <div class="hero-content">

                                <h1 class="hero-title">

                                    Bangun Website
                                    untuk Bisnis

                                </h1>

                                <p class="hero-text mt-3">

                                    Hadirkan website yang dapat
                                    membantu memperkenalkan bisnis
                                    dan layanan kamu secara online.

                                </p>

                                <a href="#layanan" class="btn btn-light btn-lg mt-3">

                                    Mulai Sekarang

                                </a>

                            </div>

                            <div class="ms-auto">

                                <i class="bi bi-code-slash hero-icon"></i>

                            </div>

                        </div>

                    </div>


                </div>


                <!-- PREV -->

                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">

                    <span class="carousel-control-prev-icon"></span>

                </button>


                <!-- NEXT -->

                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">

                    <span class="carousel-control-next-icon"></span>

                </button>


            </div>

        </div>

    </section>


    <!-- ==================================================
         SEARCH
    ================================================== -->

    <section class="search-area">

        <div class="container">

            <div class="search-card">

                <form method="GET" action="france.php">

                    <div class="input-group">

                        <input type="text" name="search" class="form-control form-control-lg"
                            placeholder="Cari layanan..." value="<?= htmlspecialchars($search); ?>">

                        <button class="btn btn-primary px-4" type="submit">

                            <i class="bi bi-search"></i>

                            Cari

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </section>


    <!-- ==================================================
         LAYANAN
    ================================================== -->

    <section id="layanan" class="py-5">

        <div class="container">


            <div class="text-center mb-4">

                <h2 class="section-title">

                    Layanan Kami

                </h2>

                <p class="section-subtitle">

                    Pilih layanan digital sesuai
                    kebutuhan bisnis kamu.

                </p>

            </div>


            <!-- KATEGORI -->

            <div class="text-center mb-5">

                <a href="france.php" class="btn btn-primary category-btn">

                    Semua

                </a>


                <?php while (
                    $kat = mysqli_fetch_assoc(
                        $query_kategori
                    )
                ): ?>

                    <a href="france.php?kategori=<?= urlencode(
                        $kat['kategori']
                    ); ?>" class="btn btn-outline-primary category-btn">

                        <?= htmlspecialchars(
                            $kat['kategori']
                        ); ?>

                    </a>

                <?php endwhile; ?>

            </div>


            <!-- PRODUK -->

            <div class="row g-4">


                <?php if (
                    mysqli_num_rows($query) > 0
                ): ?>


                    <?php while (
                        $produk = mysqli_fetch_assoc($query)
                    ): ?>


                        <div class="col-md-6 col-lg-4">


                            <div class="card product-card h-100">


                                <!-- FOTO -->

                                <?php if (
                                    !empty($produk['foto'])
                                ): ?>

                                    <img src="assets/uploads/<?= htmlspecialchars(
                                        $produk['foto']
                                    ); ?>" class="product-image" alt="<?= htmlspecialchars(
                                         $produk['nama']
                                     ); ?>">

                                <?php else: ?>

                                    <div class="no-image">

                                        <i class="bi bi-image"></i>

                                    </div>

                                <?php endif; ?>


                                <div class="card-body">


                                    <!-- KATEGORI -->

                                    <?php if (
                                        !empty($produk['kategori'])
                                    ): ?>

                                        <div class="product-category">

                                            <?= htmlspecialchars(
                                                $produk['kategori']
                                            ); ?>

                                        </div>

                                    <?php endif; ?>


                                    <!-- NAMA -->

                                    <h5 class="product-name mt-2">

                                        <?= htmlspecialchars(
                                            $produk['nama']
                                        ); ?>

                                    </h5>


                                    <!-- DESKRIPSI -->

                                    <p class="product-description">

                                        <?= htmlspecialchars(
                                            $produk['deskripsi']
                                        ); ?>

                                    </p>


                                    <!-- HARGA -->

                                    <div class="product-price mb-2">

                                        Rp <?= number_format(
                                            $produk['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </div>


                                    <!-- STATUS -->

                                    <?php if (
                                        $produk['ketersediaan']
                                        == 'Tersedia'
                                    ): ?>

                                        <span class="status status-available">

                                            <i class="bi bi-check-circle"></i>

                                            Tersedia

                                        </span>

                                    <?php else: ?>

                                        <span class="status status-empty">

                                            <i class="bi bi-x-circle"></i>

                                            Habis

                                        </span>

                                    <?php endif; ?>


                                    <!-- TOMBOL -->

                                    <div class="mt-4">


                                        <!-- DETAIL -->

                                        <a href="detail_produk.php?id=<?= $produk['id']; ?>"
                                            class="btn btn-outline-primary w-100">

                                            <i class="bi bi-eye me-1"></i>

                                            Lihat Detail

                                        </a>


                                        <?php if (
                                            $produk['ketersediaan']
                                            == 'Tersedia'
                                        ): ?>


                                            <?php if (
                                                isset($_SESSION['username'])
                                            ): ?>

                                                <a href="tambah_keranjang.php?id=<?= $produk['id']; ?>"
                                                    class="btn btn-primary w-100 mt-2">

                                                    <i class="bi bi-cart-plus me-1"></i>

                                                    Tambah ke Keranjang

                                                </a>

                                            <?php else: ?>

                                                <a href="login.php" class="btn btn-primary w-100 mt-2">

                                                    <i class="bi bi-box-arrow-in-right me-1"></i>

                                                    Login untuk Membeli

                                                </a>

                                            <?php endif; ?>


                                        <?php else: ?>


                                            <button class="btn btn-secondary w-100 mt-2" disabled>

                                                Layanan Tidak Tersedia

                                            </button>


                                        <?php endif; ?>


                                    </div>


                                </div>

                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="col-12">

                        <div class="text-center py-5">

                            <i class="bi bi-search" style="
                                    font-size:55px;
                                    color:#adb5bd;
                                "></i>


                            <h4 class="mt-3">

                                Layanan Tidak Ditemukan

                            </h4>


                            <p class="text-muted">

                                Coba gunakan kata kunci
                                atau kategori lain.

                            </p>


                            <a href="france.php" class="btn btn-primary">

                                Tampilkan Semua

                            </a>

                        </div>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </section>


    <!-- ==================================================
         INFORMASI
    ================================================== -->

    <section class="info-section">

        <div class="container">


            <div class="text-center mb-5">

                <h2 class="section-title">

                    Kenapa Memilih Kami?

                </h2>

                <p class="section-subtitle">

                    Layanan digital untuk membantu
                    kebutuhan bisnis kamu.

                </p>

            </div>


            <div class="row g-4">


                <div class="col-md-4">

                    <div class="info-card">

                        <div class="info-icon">

                            <i class="bi bi-lightning-charge"></i>

                        </div>

                        <h5 class="fw-bold">

                            Proses Praktis

                        </h5>

                        <p class="text-muted">

                            Pilih layanan yang dibutuhkan,
                            masukkan ke keranjang,
                            kemudian lakukan pembelian.

                        </p>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-card">

                        <div class="info-icon">

                            <i class="bi bi-stars"></i>

                        </div>

                        <h5 class="fw-bold">

                            Layanan Digital

                        </h5>

                        <p class="text-muted">

                            Tersedia berbagai layanan digital
                            untuk mendukung kebutuhan
                            bisnis dan promosi.

                        </p>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-card">

                        <div class="info-icon">

                            <i class="bi bi-receipt"></i>

                        </div>

                        <h5 class="fw-bold">

                            Transaksi Tercatat

                        </h5>

                        <p class="text-muted">

                            Setiap transaksi nantinya akan
                            tercatat dan dapat dilihat
                            melalui riwayat transaksi.

                        </p>

                    </div>

                </div>


            </div>

        </div>

    </section>


    <!-- ==================================================
         FOOTER
    ================================================== -->

    <footer>

        <div class="container">

            <div class="row">


                <div class="col-md-6">

                    <h4 class="fw-bold">

                        UMKM<span class="text-primary">
                            Digital
                        </span>

                    </h4>

                    <p class="mt-3">

                        Solusi jasa digital untuk membantu
                        kebutuhan bisnis dan usaha kamu.

                    </p>

                </div>


                <div class="col-md-6 text-md-end">

                    <h6 class="fw-bold">

                        Navigasi

                    </h6>

                    <p class="mb-1">

                        <a href="france.php" class="text-secondary">

                            Home

                        </a>

                    </p>

                    <p class="mb-1">

                        <a href="#layanan" class="text-secondary">

                            Layanan

                        </a>

                    </p>


                    <?php if (isset($_SESSION['username'])): ?>

                        <p>

                            <a href="profile.php" class="text-secondary">

                                Profile

                            </a>

                        </p>

                    <?php endif; ?>


                </div>


            </div>


            <hr class="border-secondary">


            <div class="text-center">

                <p class="mb-0">

                    © <?= date('Y'); ?>
                    UMKM Digital.
                    All Rights Reserved.

                </p>

            </div>

        </div>

    </footer>


    <!-- ==================================================
         JAVASCRIPT
    ================================================== -->

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

</body>

</html>