<?php
session_start();
include 'koneksi.php';

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

/* =========================
   AMBIL DATA USER
========================= */

$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_user
     WHERE username = '$username'"
);

$user = mysqli_fetch_assoc($queryUser);

if (!$user) {
    header("Location: france.php");
    exit;
}

$id_user = $user['id'];

/* =========================
   AMBIL RIWAYAT TRANSAKSI
========================= */

$queryTransaksi = mysqli_query(
    $koneksi,
    "SELECT *
     FROM tb_transaksi
     WHERE id_pelanggan = $id_user
     ORDER BY id_transaksi DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Transaksi - UMKM Digital</title>

    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =========================
           HEADER
        ========================= */

        .page-header {
            padding: 45px 0 25px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 800;
        }

        .page-subtitle {
            color: var(--muted-color);
        }


        /* =========================
           TRANSACTION CARD
        ========================= */

        .transaction-card {

            background: var(--card-color);

            border: 1px solid var(--border-color);

            border-radius: 18px;

            padding: 22px;

            margin-bottom: 18px;

            transition: .25s;

        }

        .transaction-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 10px 25px var(--shadow-color);

        }


        /* =========================
           TRANSACTION ICON
        ========================= */

        .transaction-icon {

            width: 52px;
            height: 52px;

            border-radius: 14px;

            background: rgba(13, 110, 253, .1);

            color: #0d6efd;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 23px;

        }


        /* =========================
           ID TRANSAKSI
        ========================= */

        .transaction-id {

            font-weight: 700;

            font-size: 17px;

        }


        /* =========================
           TANGGAL
        ========================= */

        .transaction-date {

            color: var(--muted-color);

            font-size: 14px;

        }


        /* =========================
           TOTAL
        ========================= */

        .transaction-total {

            font-size: 19px;

            font-weight: 800;

            color: #0d6efd;

        }


        /* =========================
           EMPTY
        ========================= */

        .empty-card {

            background: var(--card-color);

            border: 1px solid var(--border-color);

            border-radius: 20px;

            padding: 70px 20px;

            text-align: center;

        }

        .empty-icon {

            font-size: 70px;

            color: var(--muted-color);

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {

            .page-title {
                font-size: 26px;
            }

            .transaction-card {
                padding: 18px;
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

            <a class="navbar-brand fw-bold text-primary" href="france.php">

                <i class="bi bi-grid-1x2-fill"></i>

                UMKM Digital

            </a>


            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">

                <span class="navbar-toggler-icon"></span>

            </button>


            <div class="collapse navbar-collapse" id="navbarMenu">


                <ul class="navbar-nav ms-auto align-items-lg-center">


                    <!-- HOME -->

                    <li class="nav-item">

                        <a class="nav-link" href="france.php">

                            <i class="bi bi-house"></i>

                            Home

                        </a>

                    </li>


                    <!-- LAYANAN -->

                    <li class="nav-item">

                        <a class="nav-link" href="france.php#layanan">

                            <i class="bi bi-grid"></i>

                            Layanan

                        </a>

                    </li>


                    <!-- KERANJANG -->

                    <li class="nav-item">

                        <a class="nav-link" href="keranjang.php">

                            <i class="bi bi-cart3"></i>

                            Keranjang

                        </a>

                    </li>


                    <!-- PROFILE -->

                    <li class="nav-item">

                        <a class="nav-link" href="profile.php">

                            <i class="bi bi-person-circle"></i>

                            Profile

                        </a>

                    </li>


                    <!-- THEME -->

                    <li class="nav-item ms-lg-2">

                        <button id="themeBtn" class="btn btn-outline-secondary btn-sm">

                            <i id="themeIcon" class="bi bi-moon-fill"></i>

                            <span id="themeText">
                                Mode Gelap
                            </span>

                        </button>

                    </li>


                    <!-- LOGOUT -->

                    <li class="nav-item ms-lg-2">

                        <a href="logout.php" class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Yakin ingin logout?')">

                            <i class="bi bi-box-arrow-right"></i>

                            Logout

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </nav>



    <!-- ==================================================
     CONTENT
================================================== -->

    <div class="container">


        <!-- HEADER -->

        <div class="page-header">

            <div class="d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-3">

                <div>

                    <h1 class="page-title mb-1">

                        <i class="bi bi-clock-history text-primary"></i>

                        Riwayat Transaksi

                    </h1>

                    <p class="page-subtitle mb-0">

                        Lihat semua transaksi yang pernah kamu lakukan.

                    </p>

                </div>


                <a href="profile.php" class="btn btn-outline-primary">

                    <i class="bi bi-arrow-left"></i>

                    Kembali ke Profile

                </a>

            </div>

        </div>



        <!-- ==================================================
         TRANSAKSI
    ================================================== -->

        <?php if (mysqli_num_rows($queryTransaksi) > 0): ?>


            <?php while ($transaksi = mysqli_fetch_assoc($queryTransaksi)): ?>


                <div class="transaction-card">


                    <div class="row align-items-center g-3">


                        <!-- ICON -->

                        <div class="col-auto">

                            <div class="transaction-icon">

                                <i class="bi bi-receipt"></i>

                            </div>

                        </div>


                        <!-- INFORMASI -->

                        <div class="col">


                            <div class="transaction-id">

                                Transaksi
                                #<?= $transaksi['id_transaksi']; ?>

                            </div>


                            <div class="transaction-date">

                                <i class="bi bi-calendar3"></i>

                                <?= date(
                                    'd F Y',
                                    strtotime($transaksi['tanggal'])
                                ); ?>

                            </div>


                        </div>


                        <!-- TOTAL -->

                        <div class="col-md-auto">


                            <div class="small text-muted">

                                Total Pembayaran

                            </div>


                            <div class="transaction-total">

                                Rp
                                <?= number_format(
                                    $transaksi['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </div>


                        </div>


                        <!-- DETAIL -->

                        <div class="col-md-auto">


                            <a href="invoice.php?id=<?= $transaksi['id_transaksi']; ?>" class="btn btn-primary">

                                <i class="bi bi-eye"></i>

                                Lihat Detail

                            </a>


                        </div>


                    </div>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- ==================================================
             BELUM ADA TRANSAKSI
        ================================================== -->

            <div class="empty-card">


                <div class="empty-icon">

                    <i class="bi bi-receipt"></i>

                </div>


                <h4 class="mt-3 fw-bold">

                    Belum Ada Transaksi

                </h4>


                <p class="text-muted">

                    Kamu belum melakukan transaksi apa pun.

                </p>


                <a href="france.php" class="btn btn-primary mt-2">

                    <i class="bi bi-shop"></i>

                    Mulai Belanja

                </a>


            </div>


        <?php endif; ?>


    </div>



    <!-- ==================================================
     FOOTER
================================================== -->

    <footer class="mt-5 py-4">

        <div class="container text-center">

            <p class="mb-0">

                © <?= date('Y'); ?>

                UMKM Digital.

                All Rights Reserved.

            </p>

        </div>

    </footer>



    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>


</body>

</html>