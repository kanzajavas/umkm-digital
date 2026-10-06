<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Buat keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$keranjang = $_SESSION['keranjang'];

$total = 0;
$totalItem = 0;

// Hitung total
foreach ($keranjang as $item) {
    $subtotal = $item['harga'] * $item['jumlah'];

    $total += $subtotal;
    $totalItem += $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Keranjang - UMKM Digital</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =========================
           GLOBAL
        ========================= */

        body {
            background: #f6f8fb;
            color: #212529;
            transition: 0.3s;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .brand {
            font-weight: 700;
            font-size: 20px;
            color: #212529;
            text-decoration: none;
        }

        .brand:hover {
            color: #212529;
        }

        /* =========================
           HEADER
        ========================= */

        .cart-header {
            padding: 45px 0 25px;
        }

        .cart-icon {
            width: 55px;
            height: 55px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 15px;

            background: #212529;
            color: white;

            font-size: 25px;
        }

        .cart-title {
            font-weight: 700;
            margin: 0;
        }

        .cart-subtitle {
            color: #6c757d;
            margin: 5px 0 0;
        }

        /* =========================
           PRODUCT CARD
        ========================= */

        .cart-card {
            background: #ffffff;
            border: 0;
            border-radius: 18px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);

            overflow: hidden;
        }

        .cart-item {
            padding: 22px;
            border-bottom: 1px solid #eeeeee;
        }

        .cart-item:last-child {
            border-bottom: 0;
        }

        /* =========================
           IMAGE
        ========================= */

        .product-image {
            width: 85px;
            height: 85px;

            object-fit: cover;

            border-radius: 14px;

            background: #f1f3f5;
        }

        .no-image {
            width: 85px;
            height: 85px;

            border-radius: 14px;

            background: linear-gradient(135deg,
                    #f1f3f5,
                    #dee2e6);

            display: flex;
            align-items: center;
            justify-content: center;

            color: #8a8f98;

            font-size: 28px;
        }

        /* =========================
           PRODUCT INFO
        ========================= */

        .product-name {
            font-weight: 700;
            font-size: 17px;

            margin-bottom: 5px;
        }

        .product-category {
            display: inline-block;

            font-size: 12px;
            font-weight: 600;

            padding: 4px 10px;

            border-radius: 50px;

            background: #f1f3f5;
            color: #495057;

            margin-bottom: 7px;
        }

        .product-price {
            font-size: 14px;
            color: #6c757d;
        }

        /* =========================
           QUANTITY
        ========================= */

        .quantity-form {
            display: flex;
            align-items: center;
        }

        .quantity-input {
            width: 70px;

            text-align: center;

            border-radius: 10px;
        }

        .update-btn {
            border-radius: 10px;
        }

        /* =========================
           SUBTOTAL
        ========================= */

        .subtotal-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .subtotal {
            font-weight: 700;
            font-size: 17px;
        }

        /* =========================
           SUMMARY
        ========================= */

        .summary-card {
            background: #ffffff;

            border: 0;
            border-radius: 18px;

            padding: 25px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);

            position: sticky;
            top: 90px;
        }

        .summary-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;

            margin-bottom: 12px;

            color: #6c757d;
        }

        .summary-total {
            border-top: 1px solid #eeeeee;

            padding-top: 18px;
            margin-top: 18px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-total strong {
            font-size: 22px;
        }

        .checkout-btn {
            width: 100%;

            padding: 13px;

            border-radius: 12px;

            font-weight: 600;

            margin-top: 20px;
        }

        .continue-btn {
            width: 100%;

            margin-top: 10px;

            border-radius: 12px;
            padding: 11px;
        }

        /* =========================
           DIGITAL INFO
        ========================= */

        .digital-info {
            margin-top: 18px;

            padding: 15px;

            border-radius: 12px;

            background: #f8f9fa;
        }

        .digital-info i {
            font-size: 18px;
            margin-right: 6px;
        }

        .digital-info small {
            color: #6c757d;
        }

        /* =========================
           EMPTY CART
        ========================= */

        .empty-card {
            background: #ffffff;

            border-radius: 20px;

            padding: 70px 20px;

            text-align: center;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            width: 90px;
            height: 90px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: auto;

            border-radius: 25px;

            background: #f1f3f5;

            font-size: 40px;
            color: #6c757d;
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

        body.dark-mode .brand {
            color: #ffffff;
        }

        body.dark-mode .cart-subtitle {
            color: #aaaaaa;
        }

        body.dark-mode .cart-card,
        body.dark-mode .summary-card,
        body.dark-mode .empty-card {
            background: #1e1e1e;
            color: #ffffff;
        }

        body.dark-mode .cart-item {
            border-color: #333333;
        }

        body.dark-mode .product-category {
            background: #2c2c2c;
            color: #dddddd;
        }

        body.dark-mode .product-price,
        body.dark-mode .subtotal-label {
            color: #aaaaaa;
        }

        body.dark-mode .summary-row {
            color: #aaaaaa;
        }

        body.dark-mode .summary-total {
            border-color: #333333;
        }

        body.dark-mode .digital-info {
            background: #292929;
        }

        body.dark-mode .digital-info small {
            color: #aaaaaa;
        }

        body.dark-mode .empty-icon {
            background: #292929;
            color: #aaaaaa;
        }

        body.dark-mode .form-control {
            background: #292929;
            border-color: #444444;
            color: #ffffff;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 991px) {

            .summary-card {
                position: static;
                margin-top: 20px;
            }

        }

        @media (max-width: 575px) {

            .cart-header {
                padding-top: 30px;
            }

            .cart-item {
                padding: 18px;
            }

            .product-image,
            .no-image {
                width: 65px;
                height: 65px;
            }

            .product-name {
                font-size: 15px;
            }

            .quantity-form {
                margin-top: 15px;
            }

        }
    </style>

</head>

<body>


    <!-- =========================
     NAVBAR
========================= -->

    <nav class="navbar sticky-top">

        <div class="container py-3">

            <a href="france.php" class="brand">
                <i class="bi bi-stars"></i>
                UMKM Digital
            </a>

            <div class="d-flex align-items-center gap-2">

                <a href="france.php" class="btn btn-outline-secondary btn-sm">

                    <i class="bi bi-arrow-left"></i>
                    Layanan

                </a>

                <button type="button" class="btn btn-outline-secondary" id="themeToggle" title="Ganti tema">

                    <i class="bi bi-moon-fill" id="themeIcon">
                    </i>

                </button>

            </div>

        </div>

    </nav>


    <div class="container">


        <!-- =========================
     HEADER
========================= -->

        <section class="cart-header">

            <div class="d-flex align-items-center gap-3">

                <div class="cart-icon">
                    <i class="bi bi-cart3"></i>
                </div>

                <div>

                    <h2 class="cart-title">
                        Keranjang Kamu
                    </h2>

                    <p class="cart-subtitle">
                        <?= $totalItem; ?> layanan siap diproses
                    </p>

                </div>

            </div>

        </section>


        <?php if (empty($keranjang)): ?>


            <!-- =========================
     EMPTY CART
========================= -->

            <div class="empty-card">

                <div class="empty-icon">

                    <i class="bi bi-cart-x"></i>

                </div>

                <h3 class="fw-bold mt-4">
                    Keranjang masih kosong
                </h3>

                <p class="text-secondary">

                    Belum ada layanan yang kamu pilih.
                    Yuk cari layanan digital yang kamu butuhkan.

                </p>

                <a href="france.php" class="btn btn-dark mt-3">

                    <i class="bi bi-grid"></i>
                    Jelajahi Layanan

                </a>

            </div>


        <?php else: ?>


            <!-- =========================
     CART CONTENT
========================= -->

            <div class="row g-4">


                <!-- =========================
         DAFTAR PRODUK
    ========================= -->

                <div class="col-lg-8">

                    <div class="cart-card">

                        <?php foreach ($keranjang as $id => $item): ?>

                            <?php
                            $subtotal =
                                $item['harga'] * $item['jumlah'];
                            ?>

                            <div class="cart-item">

                                <div class="row align-items-center g-3">


                                    <!-- PRODUK -->

                                    <div class="col-md-5">

                                        <div class="d-flex align-items-center gap-3">

                                            <?php if (!empty($item['foto'])): ?>

                                                <img src="assets/uploads/<?= htmlspecialchars($item['foto']); ?>"
                                                    class="product-image" alt="<?= htmlspecialchars($item['nama']); ?>">

                                            <?php else: ?>

                                                <div class="no-image">

                                                    <i class="bi bi-image"></i>

                                                </div>

                                            <?php endif; ?>


                                            <div>

                                                <?php if (!empty($item['kategori'])): ?>

                                                    <span class="product-category">

                                                        <?= htmlspecialchars($item['kategori']); ?>

                                                    </span>

                                                <?php endif; ?>


                                                <div class="product-name">

                                                    <?= htmlspecialchars($item['nama']); ?>

                                                </div>


                                                <div class="product-price">

                                                    Rp <?= number_format(
                                                        $item['harga'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- JUMLAH -->

                                    <div class="col-md-3">

                                        <div class="subtotal-label">
                                            Jumlah
                                        </div>

                                        <form action="update_keranjang.php" method="POST" class="quantity-form gap-2">

                                            <input type="hidden" name="id_produk" value="<?= $id; ?>">

                                            <input type="number" name="jumlah" value="<?= $item['jumlah']; ?>" min="1"
                                                class="form-control quantity-input">

                                            <button type="submit" class="btn btn-dark update-btn" title="Update jumlah">

                                                <i class="bi bi-arrow-repeat"></i>

                                            </button>

                                        </form>

                                    </div>


                                    <!-- SUBTOTAL -->

                                    <div class="col-md-3">

                                        <div class="subtotal-label">
                                            Subtotal
                                        </div>

                                        <div class="subtotal">

                                            Rp <?= number_format(
                                                $subtotal,
                                                0,
                                                ',',
                                                '.'
                                            ); ?>

                                        </div>

                                    </div>


                                    <!-- HAPUS -->

                                    <div class="col-md-1 text-end">

                                        <a href="hapus_keranjang.php?id=<?= $id; ?>" class="btn btn-outline-danger"
                                            title="Hapus" onclick="return confirm('Hapus layanan ini dari keranjang?')">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>


                    <!-- LANJUT BELANJA -->

                    <div class="mt-3">

                        <a href="france.php" class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-left"></i>
                            Lanjut Pilih Layanan

                        </a>

                    </div>

                </div>


                <!-- =========================
         RINGKASAN
    ========================= -->

                <div class="col-lg-4">

                    <div class="summary-card">

                        <h5 class="summary-title">

                            <i class="bi bi-receipt"></i>
                            Ringkasan Pesanan

                        </h5>


                        <div class="summary-row">

                            <span>
                                Total layanan
                            </span>

                            <strong>
                                <?= $totalItem; ?>
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                Biaya layanan
                            </span>

                            <strong>
                                Rp <?= number_format(
                                    $total,
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </strong>

                        </div>


                        <div class="summary-total">

                            <span>
                                Total
                            </span>

                            <strong>
                                Rp <?= number_format(
                                    $total,
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </strong>

                        </div>


                        <a href="checkout.php" class="btn btn-primary checkout-btn">

                            <i class="bi bi-arrow-right-circle"></i>

                            Lanjut ke Checkout

                        </a>


                        <a href="france.php" class="btn btn-outline-secondary continue-btn">

                            <i class="bi bi-plus-circle"></i>

                            Tambah Layanan

                        </a>


                        <div class="digital-info">

                            <div class="mb-1">

                                <i class="bi bi-shield-check"></i>

                                <strong>
                                    Layanan Digital
                                </strong>

                            </div>

                            <small>

                                Pesanan kamu akan diproses
                                setelah melakukan checkout.

                            </small>

                        </div>

                    </div>

                </div>

            </div>


        <?php endif; ?>


    </div>


    <!-- =========================
     FOOTER
========================= -->

    <footer class="text-center py-5 mt-5">

        <p class="text-secondary mb-0">

            &copy; <?= date('Y'); ?> UMKM Digital.
            Semua hak dilindungi.

        </p>

    </footer>


    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

</body>

</html>