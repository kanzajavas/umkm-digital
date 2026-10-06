<?php
session_start();
include 'koneksi.php';

// =========================
// CEK LOGIN
// =========================
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// =========================
// CEK KERANJANG
// =========================
if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

$keranjang = $_SESSION['keranjang'];

// =========================
// AMBIL DATA USER
// =========================
$username = mysqli_real_escape_string(
    $koneksi,
    $_SESSION['username']
);

$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_user WHERE username = '$username'"
);

$user = mysqli_fetch_assoc($queryUser);

// =========================
// HITUNG TOTAL
// =========================
$total = 0;
$totalItem = 0;

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

    <title>Checkout - UMKM Digital</title>

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
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            text-decoration: none;
        }

        .brand:hover {
            color: #212529;
        }

        /* =========================
           HEADER
        ========================= */

        .checkout-header {
            padding: 45px 0 25px;
        }

        .checkout-icon {
            width: 55px;
            height: 55px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #212529;
            color: #ffffff;

            border-radius: 15px;

            font-size: 25px;
        }

        .checkout-title {
            font-weight: 700;
            margin: 0;
        }

        .checkout-subtitle {
            color: #6c757d;
            margin: 5px 0 0;
        }

        /* =========================
           CARD
        ========================= */

        .checkout-card {
            background: #ffffff;

            border: 0;
            border-radius: 18px;

            padding: 25px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);

            margin-bottom: 20px;
        }

        .card-title-custom {
            font-size: 18px;
            font-weight: 700;

            margin-bottom: 20px;
        }

        /* =========================
           FORM
        ========================= */

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 11px 13px;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
        }

        /* =========================
           USER INFO
        ========================= */

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;

            padding: 15px;

            background: #f8f9fa;

            border-radius: 12px;

            margin-bottom: 20px;
        }

        .user-icon {
            width: 45px;
            height: 45px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #212529;
            color: #ffffff;

            border-radius: 50%;

            font-size: 20px;
        }

        /* =========================
           ORDER ITEM
        ========================= */

        .order-item {
            display: flex;
            justify-content: space-between;
            gap: 15px;

            padding: 15px 0;

            border-bottom: 1px solid #eeeeee;
        }

        .order-item:last-child {
            border-bottom: 0;
        }

        .order-name {
            font-weight: 600;
        }

        .order-detail {
            color: #6c757d;
            font-size: 13px;

            margin-top: 4px;
        }

        .order-price {
            font-weight: 600;
            white-space: nowrap;
        }

        /* =========================
           TOTAL
        ========================= */

        .total-box {
            border-top: 1px solid #eeeeee;

            margin-top: 10px;
            padding-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;

            color: #6c757d;

            margin-bottom: 10px;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-top: 15px;
        }

        .grand-total strong {
            font-size: 23px;
        }

        /* =========================
           PAYMENT OPTION
        ========================= */

        .payment-option {
            border: 1px solid #dee2e6;

            border-radius: 12px;

            padding: 15px;

            margin-bottom: 10px;

            cursor: pointer;

            transition: 0.2s;
        }

        .payment-option:hover {
            border-color: #0d6efd;
            background: #f8fbff;
        }

        .payment-option input {
            margin-right: 10px;
        }

        .payment-title {
            font-weight: 600;
        }

        .payment-description {
            color: #6c757d;
            font-size: 13px;

            margin-left: 25px;
            margin-top: 3px;
        }

        /* =========================
           INFO
        ========================= */

        .info-box {
            padding: 15px;

            background: #f8f9fa;

            border-radius: 12px;

            color: #6c757d;

            font-size: 13px;
        }

        .info-box i {
            color: #0d6efd;
            margin-right: 5px;
        }

        /* =========================
           BUTTON
        ========================= */

        .confirm-btn {
            width: 100%;

            padding: 13px;

            border-radius: 12px;

            font-weight: 600;
        }

        .back-btn {
            width: 100%;

            padding: 11px;

            border-radius: 12px;

            margin-top: 10px;
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

        body.dark-mode .checkout-subtitle {
            color: #aaaaaa;
        }

        body.dark-mode .checkout-card {
            background: #1e1e1e;
            color: #ffffff;
        }

        body.dark-mode .user-info,
        body.dark-mode .info-box {
            background: #292929;
        }

        body.dark-mode .order-item,
        body.dark-mode .total-box {
            border-color: #333333;
        }

        body.dark-mode .order-detail,
        body.dark-mode .payment-description,
        body.dark-mode .total-row,
        body.dark-mode .info-box {
            color: #aaaaaa;
        }

        body.dark-mode .payment-option {
            border-color: #444444;
        }

        body.dark-mode .payment-option:hover {
            background: #252525;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: #292929;
            border-color: #444444;
            color: #ffffff;
        }

        body.dark-mode .form-control::placeholder {
            color: #888888;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .checkout-header {
                padding-top: 30px;
            }

            .checkout-card {
                padding: 20px;
            }

            .grand-total strong {
                font-size: 20px;
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

                <a href="keranjang.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cart3"></i>
                    Keranjang
                </a>

                <button type="button" class="btn btn-outline-secondary" id="themeToggle" title="Ganti tema">
                    <i class="bi bi-moon-fill" id="themeIcon"></i>
                </button>

            </div>

        </div>

    </nav>


    <div class="container">


        <!-- =========================
     HEADER
========================= -->

        <section class="checkout-header">

            <div class="d-flex align-items-center gap-3">

                <div class="checkout-icon">

                    <i class="bi bi-credit-card"></i>

                </div>

                <div>

                    <h2 class="checkout-title">
                        Checkout
                    </h2>

                    <p class="checkout-subtitle">
                        Periksa pesanan sebelum melanjutkan
                    </p>

                </div>

            </div>

        </section>


        <!-- =========================
     CONTENT
========================= -->

        <div class="row g-4">


            <!-- =========================
         KIRI
    ========================= -->

            <div class="col-lg-7">


                <!-- DATA PELANGGAN -->

                <div class="checkout-card">

                    <h5 class="card-title-custom">

                        <i class="bi bi-person-circle"></i>
                        Data Pelanggan

                    </h5>


                    <div class="user-info">

                        <div class="user-icon">

                            <i class="bi bi-person"></i>

                        </div>

                        <div>

                            <strong>
                                <?= htmlspecialchars(
                                    $user['nama'] ?: $user['username']
                                ); ?>
                            </strong>

                            <div class="text-secondary small">

                                @<?= htmlspecialchars(
                                    $user['username']
                                ); ?>

                            </div>

                        </div>

                    </div>


                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Nama
                            </label>

                            <input type="text" class="form-control" value="<?= htmlspecialchars(
                                $user['nama'] ?? ''
                            ); ?>" readonly>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email" class="form-control" value="<?= htmlspecialchars(
                                $user['email'] ?? ''
                            ); ?>" readonly>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Nomor HP
                            </label>

                            <input type="text" class="form-control" value="<?= htmlspecialchars(
                                $user['hp'] ?? ''
                            ); ?>" readonly>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Alamat
                            </label>

                            <input type="text" class="form-control" value="<?= htmlspecialchars(
                                $user['alamat'] ?? ''
                            ); ?>" readonly>

                        </div>

                    </div>


                    <div class="mt-3">

                        <a href="profile.php" class="btn btn-outline-secondary btn-sm">

                            <i class="bi bi-pencil"></i>
                            Edit Data Profil

                        </a>

                    </div>

                </div>


                <!-- METODE PEMBAYARAN -->

                <div class="checkout-card">

                    <h5 class="card-title-custom">

                        <i class="bi bi-wallet2"></i>
                        Metode Pembayaran

                    </h5>


                    <label class="payment-option">

                        <input type="radio" name="pembayaran" value="Transfer Bank" checked>

                        <span class="payment-title">
                            Transfer Bank
                        </span>

                        <div class="payment-description">

                            Pembayaran melalui transfer bank.

                        </div>

                    </label>


                    <label class="payment-option">

                        <input type="radio" name="pembayaran" value="E-Wallet">

                        <span class="payment-title">
                            E-Wallet
                        </span>

                        <div class="payment-description">

                            Pembayaran melalui dompet digital.

                        </div>

                    </label>


                    <label class="payment-option">

                        <input type="radio" name="pembayaran" value="Bayar di Tempat">

                        <span class="payment-title">
                            Bayar di Tempat
                        </span>

                        <div class="payment-description">

                            Pembayaran dilakukan sesuai kesepakatan.

                        </div>

                    </label>

                </div>


                <!-- PENGIRIMAN / PENGERJAAN -->

                <div class="checkout-card">

                    <h5 class="card-title-custom">

                        <i class="bi bi-truck"></i>
                        Metode Pengiriman / Pengerjaan

                    </h5>


                    <select class="form-select" name="pengiriman" id="pengiriman">

                        <option value="Digital / Online">
                            Digital / Online
                        </option>

                        <option value="Pengiriman File">
                            Pengiriman File
                        </option>

                        <option value="Sesuai Kesepakatan">
                            Sesuai Kesepakatan
                        </option>

                    </select>


                    <div class="info-box mt-3">

                        <i class="bi bi-info-circle"></i>

                        Karena layanan yang tersedia merupakan
                        layanan digital, proses pengerjaan atau
                        pengiriman dapat dilakukan secara online
                        sesuai kesepakatan.

                    </div>

                </div>

            </div>


            <!-- =========================
         KANAN
    ========================= -->

            <div class="col-lg-5">

                <div class="checkout-card">

                    <h5 class="card-title-custom">

                        <i class="bi bi-receipt"></i>
                        Ringkasan Pesanan

                    </h5>


                    <!-- ITEMS -->

                    <?php foreach ($keranjang as $item): ?>

                        <?php
                        $subtotal =
                            $item['harga'] * $item['jumlah'];
                        ?>

                        <div class="order-item">

                            <div>

                                <div class="order-name">

                                    <?= htmlspecialchars(
                                        $item['nama']
                                    ); ?>

                                </div>

                                <div class="order-detail">

                                    <?= $item['jumlah']; ?>
                                    ×
                                    Rp <?= number_format(
                                        $item['harga'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </div>

                            </div>


                            <div class="order-price">

                                Rp <?= number_format(
                                    $subtotal,
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <!-- TOTAL -->

                    <div class="total-box">

                        <div class="total-row">

                            <span>
                                Jumlah layanan
                            </span>

                            <strong>
                                <?= $totalItem; ?>
                            </strong>

                        </div>


                        <div class="grand-total">

                            <span>
                                Total Pembayaran
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

                    </div>


                    <!-- CONFIRM -->

                    <form action="proses_checkout.php" method="POST">

                        <input type="hidden" name="pembayaran" id="hiddenPembayaran" value="Transfer Bank">

                        <input type="hidden" name="pengiriman" id="hiddenPengiriman" value="Digital / Online">


                        <button type="submit" class="btn btn-primary confirm-btn"
                            onclick="return confirm('Yakin ingin melanjutkan pesanan?')">

                            <i class="bi bi-check-circle"></i>

                            Konfirmasi Pesanan

                        </button>

                    </form>


                    <a href="keranjang.php" class="btn btn-outline-secondary back-btn">

                        <i class="bi bi-arrow-left"></i>

                        Kembali ke Keranjang

                    </a>

                </div>


                <div class="info-box">

                    <i class="bi bi-shield-check"></i>

                    Pastikan data pelanggan dan pesanan
                    sudah benar sebelum melakukan konfirmasi.

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
     FOOTER
========================= -->

    <footer class="text-center py-5 mt-4">

        <p class="text-secondary mb-0">

            &copy; <?= date('Y'); ?> UMKM Digital.
            Semua hak dilindungi.

        </p>

    </footer>


    <!-- BOOTSTRAP -->

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <!-- THEME -->

    <script src="assets/js/theme.js"></script>


    <!-- SIMPAN PILIHAN CHECKOUT -->

    <script>

        const pembayaran =
            document.querySelectorAll(
                'input[name="pembayaran"]'
            );

        const hiddenPembayaran =
            document.getElementById(
                'hiddenPembayaran'
            );

        const pengiriman =
            document.getElementById(
                'pengiriman'
            );

        const hiddenPengiriman =
            document.getElementById(
                'hiddenPengiriman'
            );


        pembayaran.forEach(function (radio) {

            radio.addEventListener(
                'change',
                function () {

                    hiddenPembayaran.value =
                        this.value;

                }
            );

        });


        pengiriman.addEventListener(
            'change',
            function () {

                hiddenPengiriman.value =
                    this.value;

            }
        );

    </script>

</body>

</html>