<?php
session_start();
include 'koneksi.php';

/* =========================
   CEK ID PRODUK
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: france.php");
    exit;
}

$id = (int) $_GET['id'];

/* =========================
   AMBIL DATA PRODUK
========================= */
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_produk WHERE id = $id"
);

if (mysqli_num_rows($query) == 0) {
    echo "<script>
            alert('Layanan tidak ditemukan!');
            window.location='france.php';
          </script>";
    exit;
}

$produk = mysqli_fetch_assoc($query);

/* =========================
   FORMAT HARGA
========================= */
$harga = number_format(
    $produk['harga'],
    0,
    ',',
    '.'
);

/* =========================
   CEK LOGIN
========================= */
$sudahLogin = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($produk['nama']); ?> - UMKM Digital</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f8f9fa;
            color: #212529;
            transition: 0.3s;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .navbar-brand {
            font-weight: 700;
            color: #212529;
        }

        .navbar-brand:hover {
            color: #212529;
        }

        .nav-link {
            color: #555;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #000;
        }

        /* =========================
           DETAIL
        ========================= */

        .detail-section {
            padding: 70px 0;
            min-height: 80vh;
        }

        .detail-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        /* =========================
           FOTO
        ========================= */

        .product-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
        }

        .no-image {
            width: 100%;
            height: 450px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(135deg,
                    #f1f3f5,
                    #dee2e6);

            color: #6c757d;
            font-size: 70px;
        }

        /* =========================
           INFO PRODUK
        ========================= */

        .detail-content {
            padding: 40px;
        }

        .category-badge {
            display: inline-block;

            padding: 7px 14px;

            background: #f1f3f5;
            color: #495057;

            border-radius: 50px;

            font-size: 13px;
            font-weight: 600;

            margin-bottom: 15px;
        }

        .detail-title {
            font-size: 36px;
            font-weight: 700;

            margin-bottom: 15px;
        }

        .detail-description {
            color: #6c757d;
            line-height: 1.8;

            margin-bottom: 25px;
        }

        .price {
            font-size: 30px;
            font-weight: 700;

            margin-bottom: 20px;
        }

        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 8px 14px;

            border-radius: 50px;

            font-size: 14px;
            font-weight: 600;

            margin-bottom: 25px;
        }

        .status-tersedia {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-habis {
            background: #f8d7da;
            color: #842029;
        }

        /* =========================
           JUMLAH
        ========================= */

        .quantity-box {
            margin-bottom: 20px;
        }

        .quantity-box label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .quantity-input {
            max-width: 130px;
        }

        /* =========================
           BUTTON
        ========================= */

        .btn-cart {
            padding: 12px 24px;
            border-radius: 10px;

            font-weight: 600;
        }

        .btn-back {
            padding: 12px 20px;
            border-radius: 10px;

            font-weight: 600;
        }

        /* =========================
           INFO TAMBAHAN
        ========================= */

        .info-box {
            margin-top: 25px;
            padding: 18px;

            background: #f8f9fa;

            border-radius: 12px;
        }

        .info-box i {
            font-size: 20px;
            margin-right: 8px;
        }

        /* =========================
           DARK MODE
        ========================= */

        body.dark-mode {
            background: #121212;
            color: #f1f1f1;
        }

        body.dark-mode .navbar {
            background: #1c1c1c;
        }

        body.dark-mode .navbar-brand {
            color: #ffffff;
        }

        body.dark-mode .nav-link {
            color: #cccccc;
        }

        body.dark-mode .nav-link:hover {
            color: #ffffff;
        }

        body.dark-mode .detail-card {
            background: #1e1e1e;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .category-badge {
            background: #2c2c2c;
            color: #dddddd;
        }

        body.dark-mode .detail-description {
            color: #aaaaaa;
        }

        body.dark-mode .info-box {
            background: #292929;
        }

        body.dark-mode .no-image {
            background: linear-gradient(135deg,
                    #292929,
                    #202020);

            color: #888;
        }

        body.dark-mode .form-control {
            background: #292929;
            border-color: #444;
            color: #ffffff;
        }

        body.dark-mode .form-control:focus {
            background: #292929;
            color: #ffffff;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .detail-section {
                padding: 35px 0;
            }

            .product-image,
            .no-image {
                height: 300px;
            }

            .detail-content {
                padding: 25px;
            }

            .detail-title {
                font-size: 28px;
            }

            .price {
                font-size: 25px;
            }

        }
    </style>

</head>

<body>

    <!-- =========================
     NAVBAR
========================= -->

    <nav class="navbar navbar-expand-lg sticky-top">

        <div class="container">

            <a class="navbar-brand" href="france.php">
                <i class="bi bi-stars"></i>
                UMKM Digital
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">
                        <a class="nav-link" href="france.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="france.php#layanan">
                            Layanan
                        </a>
                    </li>

                    <?php if ($sudahLogin): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                Profile
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="keranjang.php">
                                <i class="bi bi-cart3"></i>
                                Keranjang
                            </a>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a href="logout.php" class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Yakin ingin logout?')">
                                Logout
                            </a>
                        </li>

                    <?php else: ?>

                        <li class="nav-item ms-lg-2">
                            <a href="login.php" class="btn btn-outline-primary btn-sm">
                                Login
                            </a>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a href="register.php" class="btn btn-primary btn-sm">
                                Daftar
                            </a>
                        </li>

                    <?php endif; ?>

                    <!-- THEME -->
                    <li class="nav-item ms-lg-2">

                        <button type="button" class="btn btn-outline-secondary" id="themeToggle" title="Ganti tema">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>

                    </li>

                </ul>

            </div>

        </div>

    </nav>


    <!-- =========================
     DETAIL PRODUK
========================= -->

    <section class="detail-section">

        <div class="container">

            <div class="detail-card">

                <div class="row g-0">

                    <!-- FOTO -->

                    <div class="col-lg-6">

                        <?php if (!empty($produk['foto'])): ?>

                            <img src="assets/uploads/<?= htmlspecialchars($produk['foto']); ?>"
                                alt="<?= htmlspecialchars($produk['nama']); ?>" class="product-image">

                        <?php else: ?>

                            <div class="no-image">
                                <i class="bi bi-image"></i>
                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- INFORMASI -->

                    <div class="col-lg-6">

                        <div class="detail-content">

                            <!-- KATEGORI -->

                            <span class="category-badge">

                                <i class="bi bi-tag"></i>

                                <?= htmlspecialchars($produk['kategori']); ?>

                            </span>


                            <!-- NAMA -->

                            <h1 class="detail-title">

                                <?= htmlspecialchars($produk['nama']); ?>

                            </h1>


                            <!-- DESKRIPSI -->

                            <p class="detail-description">

                                <?= nl2br(
                                    htmlspecialchars($produk['deskripsi'])
                                ); ?>

                            </p>


                            <!-- HARGA -->

                            <div class="price">

                                Rp <?= $harga; ?>

                            </div>


                            <!-- STATUS -->

                            <?php if ($produk['ketersediaan'] == 'Tersedia'): ?>

                                <div class="status status-tersedia">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Layanan Tersedia

                                </div>

                            <?php else: ?>

                                <div class="status status-habis">

                                    <i class="bi bi-x-circle-fill"></i>

                                    Layanan Sedang Habis

                                </div>

                            <?php endif; ?>


                            <!-- JUMLAH -->

                            <?php if ($produk['ketersediaan'] == 'Tersedia'): ?>

                                <form action="tambah_keranjang.php" method="GET">

                                    <input type="hidden" name="id" value="<?= $produk['id']; ?>">

                                    <div class="quantity-box">

                                        <label for="jumlah">
                                            Jumlah
                                        </label>

                                        <input type="number" name="jumlah" id="jumlah" class="form-control quantity-input"
                                            value="1" min="1">

                                    </div>


                                    <div class="d-flex flex-wrap gap-2">

                                        <button type="submit" class="btn btn-primary btn-cart">

                                            <i class="bi bi-cart-plus"></i>

                                            Tambah ke Keranjang

                                        </button>


                                        <a href="france.php" class="btn btn-outline-secondary btn-back">

                                            <i class="bi bi-arrow-left"></i>

                                            Kembali

                                        </a>

                                    </div>

                                </form>

                            <?php else: ?>

                                <div class="d-flex flex-wrap gap-2">

                                    <button class="btn btn-secondary btn-cart" disabled>

                                        <i class="bi bi-x-circle"></i>

                                        Tidak Tersedia

                                    </button>

                                    <a href="france.php" class="btn btn-outline-secondary btn-back">

                                        <i class="bi bi-arrow-left"></i>

                                        Kembali

                                    </a>

                                </div>

                            <?php endif; ?>


                            <!-- INFO -->

                            <div class="info-box">

                                <div class="mb-2">

                                    <i class="bi bi-shield-check"></i>

                                    <strong>Layanan Digital</strong>

                                </div>

                                <small class="text-secondary">

                                    Pilih jumlah layanan yang dibutuhkan,
                                    kemudian tambahkan ke keranjang untuk
                                    melanjutkan proses pembelian.

                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================
     FOOTER
========================= -->

    <footer class="py-4 text-center">

        <div class="container">

            <p class="mb-0 text-secondary">

                &copy; <?= date('Y'); ?> UMKM Digital.
                Semua hak dilindungi.

            </p>

        </div>

    </footer>


    <!-- BOOTSTRAP -->

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <!-- THEME -->

    <script src="assets/js/theme.js"></script>

</body>

</html>