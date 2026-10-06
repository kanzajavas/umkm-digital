<?php
session_start();
include 'koneksi.php';

/*
|--------------------------------------------------------------------------
| PROTEKSI ADMIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: france.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DATA ADMIN
|--------------------------------------------------------------------------
*/

$username_admin = $_SESSION['username'];

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout'])) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK ID TRANSAKSI
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: transaksi.php");
    exit;
}

$id_transaksi = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| DATA TRANSAKSI
|--------------------------------------------------------------------------
*/

$queryTransaksi = mysqli_query(
    $koneksi,
    "SELECT
        tb_transaksi.id_transaksi,
        tb_transaksi.id_pelanggan,
        tb_transaksi.tanggal,
        tb_transaksi.total_harga,
        tb_user.nama,
        tb_user.username,
        tb_user.email,
        tb_user.hp,
        tb_user.alamat

     FROM tb_transaksi

     INNER JOIN tb_user
        ON tb_transaksi.id_pelanggan = tb_user.id

     WHERE tb_transaksi.id_transaksi = $id_transaksi

     LIMIT 1"
);

/*
|--------------------------------------------------------------------------
| CEK TRANSAKSI
|--------------------------------------------------------------------------
*/

if (!$queryTransaksi || mysqli_num_rows($queryTransaksi) == 0) {

    echo "<script>
            alert('Transaksi tidak ditemukan!');
            window.location='transaksi.php';
          </script>";

    exit;
}

$transaksi = mysqli_fetch_assoc($queryTransaksi);

/*
|--------------------------------------------------------------------------
| DETAIL TRANSAKSI
|--------------------------------------------------------------------------
*/

$queryDetail = mysqli_query(
    $koneksi,
    "SELECT
        tb_detail.id_detail,
        tb_detail.id_produk,
        tb_detail.jumlah,
        tb_detail.harga_satuan,
        tb_produk.nama,
        tb_produk.kategori

     FROM tb_detail

     INNER JOIN tb_produk
        ON tb_detail.id_produk = tb_produk.id

     WHERE tb_detail.id_transaksi = $id_transaksi

     ORDER BY tb_detail.id_detail ASC"
);

$jumlah_jenis_layanan = 0;

if ($queryDetail) {
    $jumlah_jenis_layanan = mysqli_num_rows($queryDetail);
}

/*
|--------------------------------------------------------------------------
| DATA UNTUK AVATAR
|--------------------------------------------------------------------------
*/

$avatar_letter = strtoupper(
    substr($username_admin, 0, 1)
);

?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        Detail Transaksi #<?= $transaksi['id_transaksi']; ?>
        - UMKM Digital
    </title>

    <!-- BOOTSTRAP -->
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- THEME GLOBAL -->
    <link rel="stylesheet" href="assets/dist/css/theme.css">

    <!-- BOOTSTRAP ICON -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =====================================================
           GLOBAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f6f8fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #212529;
        }

        a {
            text-decoration: none;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e9ecef;

            position: fixed;
            left: 0;
            top: 0;

            z-index: 1000;

            transition: 0.3s;
        }

        .brand {
            height: 75px;

            display: flex;
            align-items: center;

            padding: 0 25px;

            border-bottom: 1px solid #f0f1f3;
        }

        .brand-logo {
            width: 42px;
            height: 42px;

            object-fit: contain;

            margin-right: 11px;
        }

        .brand-text {
            font-size: 18px;
            font-weight: 700;

            color: #111827;

            line-height: 1.1;
        }

        .brand-text span {
            display: block;

            font-size: 11px;
            font-weight: 500;

            color: #6c757d;

            margin-top: 3px;
        }

        .sidebar-content {
            padding: 25px 15px;
        }

        .menu-title {
            font-size: 11px;
            font-weight: 700;

            color: #9ca3af;

            text-transform: uppercase;

            letter-spacing: 0.7px;

            padding: 0 12px;

            margin-bottom: 10px;
        }

        .sidebar-menu {
            list-style: none;

            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 13px;

            border-radius: 10px;

            color: #5f6368;

            font-size: 14px;
            font-weight: 500;

            transition: 0.2s;
        }

        .sidebar-menu a i {
            font-size: 18px;
        }

        .sidebar-menu a:hover {
            background: #f1f5ff;
            color: #0d6efd;
        }

        .sidebar-menu a.active {
            background: #0d6efd;
            color: white;

            box-shadow:
                0 6px 15px rgba(13, 110, 253, 0.20);
        }

        .sidebar-divider {
            border: 0;
            border-top: 1px solid #eeeeee;

            margin: 25px 10px;
        }

        .logout-link {
            color: #dc3545 !important;
        }

        .logout-link:hover {
            background: #fff1f2 !important;
            color: #dc3545 !important;
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {
            margin-left: 260px;
            min-height: 100vh;
        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {
            height: 75px;

            background: #ffffff;

            border-bottom: 1px solid #e9ecef;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 30px;

            position: sticky;

            top: 0;

            z-index: 900;
        }

        .page-title-top {
            margin: 0;

            font-size: 20px;

            font-weight: 700;

            color: #111827;
        }

        .page-subtitle-top {
            margin: 3px 0 0;

            color: #8a9199;

            font-size: 12px;
        }

        .admin-profile {
            display: flex;

            align-items: center;

            gap: 11px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            background:
                linear-gradient(135deg,
                    #0d6efd,
                    #6f42c1);

            display: flex;

            align-items: center;
            justify-content: center;

            color: white;

            font-weight: 700;

            font-size: 15px;
        }

        .admin-info {
            line-height: 1.2;
        }

        .admin-name {
            font-size: 14px;

            font-weight: 700;

            color: #212529;
        }

        .admin-role {
            font-size: 11px;

            color: #8a9199;

            margin-top: 3px;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {
            padding: 30px;
        }


        /* =====================================================
           BREADCRUMB / PAGE HEADER
        ===================================================== */

        .detail-page-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            flex-wrap: wrap;

            margin-bottom: 25px;
        }

        .detail-title {
            margin: 0;

            font-size: 25px;

            font-weight: 700;

            color: #111827;
        }

        .detail-subtitle {
            margin: 5px 0 0;

            font-size: 13px;

            color: #8a9199;
        }


        /* =====================================================
           DETAIL CARD
        ===================================================== */

        .detail-card {
            background: #ffffff;

            border: 1px solid #edf0f3;

            border-radius: 16px;

            overflow: hidden;

            margin-bottom: 20px;
        }

        .detail-card-header {
            padding: 20px 22px;

            border-bottom: 1px solid #edf0f3;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            flex-wrap: wrap;
        }

        .transaction-number {
            font-size: 21px;

            font-weight: 700;

            color: #0d6efd;
        }

        .transaction-label {
            font-size: 11px;

            color: #8a9199;

            margin-top: 3px;
        }

        .transaction-date {
            text-align: right;

            font-size: 13px;

            font-weight: 600;

            color: #495057;
        }

        .transaction-date small {
            display: block;

            margin-top: 3px;

            font-size: 11px;

            color: #8a9199;
        }

        .detail-card-body {
            padding: 22px;
        }


        /* =====================================================
           SECTION TITLE
        ===================================================== */

        .content-section-title {
            font-size: 15px;

            font-weight: 700;

            color: #212529;

            margin-bottom: 14px;
        }


        /* =====================================================
           CUSTOMER BOX
        ===================================================== */

        .customer-box {
            background: #f8fafc;

            border: 1px solid #edf0f3;

            border-radius: 13px;

            padding: 18px;

            margin-bottom: 25px;
        }

        .customer-icon {
            width: 52px;
            height: 52px;

            min-width: 52px;

            border-radius: 13px;

            background: #eaf2ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 23px;
        }

        .customer-name {
            font-size: 16px;

            font-weight: 700;

            color: #212529;
        }

        .customer-username {
            font-size: 12px;

            color: #8a9199;

            margin-top: 2px;
        }

        .customer-detail {
            font-size: 12px;

            color: #6c757d;

            margin-top: 5px;
        }

        .customer-detail i {
            width: 18px;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table {
            margin: 0;
        }

        .table thead th {
            background: #fafbfc;

            color: #7a828a;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.4px;

            border-bottom: 1px solid #edf0f3;

            padding: 13px 16px;

            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 16px;

            font-size: 13px;

            color: #495057;

            vertical-align: middle;

            border-color: #f0f1f3;
        }

        .product-name {
            font-weight: 600;

            color: #212529;
        }

        .product-category {
            font-size: 11px;

            color: #8a9199;

            margin-top: 3px;
        }

        .price {
            font-weight: 600;
        }

        .subtotal {
            font-weight: 700;

            color: #0d6efd;
        }

        .quantity-badge {
            background: #eaf2ff;

            color: #0d6efd;

            border-radius: 20px;

            padding: 5px 10px;

            font-size: 11px;

            font-weight: 600;
        }


        /* =====================================================
           TOTAL
        ===================================================== */

        .total-box {
            margin-top: 20px;

            background: #f1f5ff;

            border: 1px solid #dbe7ff;

            border-radius: 13px;

            padding: 18px 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;
        }

        .total-label {
            font-size: 12px;

            color: #6c757d;
        }

        .total-count {
            font-size: 11px;

            color: #8a9199;

            margin-top: 3px;
        }

        .total-price {
            font-size: 23px;

            font-weight: 700;

            color: #0d6efd;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-detail {
            text-align: center;

            padding: 45px 20px;

            color: #8a9199;
        }

        .empty-detail i {
            font-size: 45px;

            color: #ced4da;
        }

        .empty-detail h5 {
            font-size: 14px;

            font-weight: 600;

            color: #495057;
        }

        .empty-detail p {
            font-size: 12px;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .action-area {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            flex-wrap: wrap;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            padding: 25px 0 10px;

            text-align: center;

            color: #9ca3af;

            font-size: 11px;
        }


        /* =====================================================
           MOBILE MENU
        ===================================================== */

        .mobile-menu-btn {
            display: none;

            border: none;

            background: transparent;

            font-size: 24px;

            color: #495057;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);

                box-shadow:
                    10px 0 30px rgba(0, 0, 0, 0.12);
            }

            .main {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 20px;
            }

        }


        @media (max-width: 576px) {

            .admin-info {
                display: none;
            }

            .content {
                padding: 15px;
            }

            .detail-title {
                font-size: 21px;
            }

            .detail-card-body {
                padding: 17px;
            }

            .transaction-date {
                text-align: left;
            }

            .total-box {
                align-items: flex-start;

                flex-direction: column;
            }

            .total-price {
                font-size: 21px;
            }

        }


        /* =====================================================
           DARK MODE
        ===================================================== */

        body.dark-mode {
            background: #111827;

            color: #e5e7eb;
        }

        body.dark-mode .sidebar {
            background: #1f2937;

            border-color: #374151;
        }

        body.dark-mode .brand {
            border-color: #374151;
        }

        body.dark-mode .brand-text {
            color: #f9fafb;
        }

        body.dark-mode .brand-text span {
            color: #9ca3af;
        }

        body.dark-mode .menu-title {
            color: #9ca3af;
        }

        body.dark-mode .sidebar-menu a {
            color: #d1d5db;
        }

        body.dark-mode .sidebar-menu a:hover {
            background: #374151;

            color: #60a5fa;
        }

        body.dark-mode .sidebar-divider {
            border-color: #374151;
        }

        body.dark-mode .topbar {
            background: #1f2937;

            border-color: #374151;
        }

        body.dark-mode .page-title-top {
            color: #f9fafb;
        }

        body.dark-mode .page-subtitle-top {
            color: #9ca3af;
        }

        body.dark-mode .admin-name {
            color: #f9fafb;
        }

        body.dark-mode .admin-role {
            color: #9ca3af;
        }

        body.dark-mode .detail-title {
            color: #f9fafb;
        }

        body.dark-mode .detail-subtitle {
            color: #9ca3af;
        }

        body.dark-mode .detail-card {
            background: #1f2937;

            border-color: #374151;
        }

        body.dark-mode .detail-card-header {
            border-color: #374151;
        }

        body.dark-mode .transaction-date {
            color: #d1d5db;
        }

        body.dark-mode .transaction-date small {
            color: #9ca3af;
        }

        body.dark-mode .content-section-title {
            color: #f9fafb;
        }

        body.dark-mode .customer-box {
            background: #111827;

            border-color: #374151;
        }

        body.dark-mode .customer-name {
            color: #f9fafb;
        }

        body.dark-mode .customer-username {
            color: #9ca3af;
        }

        body.dark-mode .customer-detail {
            color: #9ca3af;
        }

        body.dark-mode .table {
            --bs-table-bg: #1f2937;

            --bs-table-color: #d1d5db;
        }

        body.dark-mode .table thead th {
            background: #111827;

            color: #9ca3af;

            border-color: #374151;
        }

        body.dark-mode .table tbody td {
            color: #d1d5db;

            border-color: #374151;
        }

        body.dark-mode .product-name {
            color: #f9fafb;
        }

        body.dark-mode .product-category {
            color: #9ca3af;
        }

        body.dark-mode .total-box {
            background: #172554;

            border-color: #1e40af;
        }

        body.dark-mode .total-label {
            color: #cbd5e1;
        }

        body.dark-mode .total-count {
            color: #94a3b8;
        }

        body.dark-mode .empty-detail h5 {
            color: #d1d5db;
        }

        body.dark-mode .empty-detail p {
            color: #9ca3af;
        }

        body.dark-mode .footer {
            color: #6b7280;
        }
    </style>

</head>

<body>


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="sidebar" id="sidebar">

        <!-- BRAND -->

        <div class="brand">

            <img src="assets/brand/umkmlogoo.png" class="brand-logo" alt="UMKM Digital">

            <div class="brand-text">

                UMKM Digital

                <span>
                    Admin Panel
                </span>

            </div>

        </div>


        <!-- SIDEBAR CONTENT -->

        <div class="sidebar-content">

            <div class="menu-title">
                Menu Utama
            </div>


            <ul class="sidebar-menu">

                <!-- DASHBOARD -->

                <li>

                    <a href="dashboard/dashboard.php">

                        <i class="bi bi-grid-1x2-fill"></i>

                        Dashboard

                    </a>

                </li>


                <!-- PESANAN -->

                <li>

                    <a href="#">

                        <i class="bi bi-bag-check-fill"></i>

                        Pesanan

                    </a>

                </li>


                <!-- LAYANAN -->

                <li>

                    <a href="assets/uploads/layanan.php">

                        <i class="bi bi-briefcase-fill"></i>

                        Layanan

                    </a>

                </li>


                <!-- PELANGGAN -->

                <li>

                    <a href="pelanggan.php">

                        <i class="bi bi-people-fill"></i>

                        Pelanggan

                    </a>

                </li>


                <!-- TRANSAKSI -->

                <li>

                    <a href="transaksi.php" class="active">

                        <i class="bi bi-receipt"></i>

                        Transaksi

                    </a>

                </li>


                <!-- LAPORAN -->

                <li>

                    <a href="#">

                        <i class="bi bi-bar-chart-fill"></i>

                        Laporan

                    </a>

                </li>

            </ul>


            <hr class="sidebar-divider">


            <div class="menu-title">
                Pengaturan
            </div>


            <ul class="sidebar-menu">

                <!-- PENGATURAN -->

                <li>

                    <a href="#">

                        <i class="bi bi-gear-fill"></i>

                        Pengaturan

                    </a>

                </li>


                <!-- THEME -->

                <li>

                    <a href="javascript:void(0)" id="themeBtn">

                        <i class="bi bi-moon-fill" id="themeIcon"></i>

                        <span id="themeText">
                            Mode Terang
                        </span>

                    </a>

                </li>


                <!-- LOGOUT -->

                <li>

                    <a href="dashboard/dashboard.php?logout=true" class="logout-link"
                        onclick="return confirm('Yakin ingin logout?')">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </aside>


    <!-- =========================================================
     MAIN
========================================================= -->

    <div class="main">


        <!-- =====================================================
         TOPBAR
    ====================================================== -->

        <header class="topbar">

            <div class="d-flex align-items-center gap-3">

                <button class="mobile-menu-btn" onclick="toggleSidebar()" type="button">

                    <i class="bi bi-list"></i>

                </button>


                <div>

                    <h1 class="page-title-top">
                        Detail Transaksi
                    </h1>

                    <p class="page-subtitle-top">
                        Informasi lengkap transaksi pelanggan
                    </p>

                </div>

            </div>


            <!-- ADMIN PROFILE -->

            <div class="admin-profile">

                <div class="admin-avatar">

                    <?= $avatar_letter; ?>

                </div>

                <div class="admin-info">

                    <div class="admin-name">

                        <?= htmlspecialchars($username_admin); ?>

                    </div>

                    <div class="admin-role">

                        Administrator

                    </div>

                </div>

            </div>

        </header>


        <!-- =====================================================
         CONTENT
    ====================================================== -->

        <main class="content">


            <!-- PAGE HEADER -->

            <div class="detail-page-header">

                <div>

                    <h2 class="detail-title">

                        <i class="bi bi-receipt text-primary"></i>

                        Detail Transaksi

                    </h2>

                    <p class="detail-subtitle">

                        Lihat informasi lengkap transaksi pelanggan.

                    </p>

                </div>


                <a href="transaksi.php" class="btn btn-outline-primary">

                    <i class="bi bi-arrow-left"></i>

                    Kembali ke Transaksi

                </a>

            </div>


            <!-- =================================================
             TRANSACTION CARD
        ================================================== -->

            <div class="detail-card">


                <!-- HEADER -->

                <div class="detail-card-header">

                    <div>

                        <div class="transaction-number">

                            #<?= $transaksi['id_transaksi']; ?>

                        </div>

                        <div class="transaction-label">

                            ID Transaksi

                        </div>

                    </div>


                    <div class="transaction-date">

                        <i class="bi bi-calendar3"></i>

                        <?= date(
                            'd F Y',
                            strtotime($transaksi['tanggal'])
                        ); ?>

                        <small>
                            Tanggal Transaksi
                        </small>

                    </div>

                </div>


                <!-- BODY -->

                <div class="detail-card-body">


                    <!-- =================================================
                     PELANGGAN
                ================================================== -->

                    <div class="content-section-title">

                        <i class="bi bi-person-circle text-primary"></i>

                        Informasi Pelanggan

                    </div>


                    <div class="customer-box">

                        <div class="d-flex align-items-start gap-3">

                            <div class="customer-icon">

                                <i class="bi bi-person"></i>

                            </div>


                            <div>

                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $transaksi['nama']
                                    ); ?>

                                </div>


                                <div class="customer-username">

                                    @<?= htmlspecialchars(
                                        $transaksi['username']
                                    ); ?>

                                </div>


                                <?php if (!empty($transaksi['email'])): ?>

                                    <div class="customer-detail">

                                        <i class="bi bi-envelope"></i>

                                        <?= htmlspecialchars(
                                            $transaksi['email']
                                        ); ?>

                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($transaksi['hp'])): ?>

                                    <div class="customer-detail">

                                        <i class="bi bi-telephone"></i>

                                        <?= htmlspecialchars(
                                            $transaksi['hp']
                                        ); ?>

                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($transaksi['alamat'])): ?>

                                    <div class="customer-detail">

                                        <i class="bi bi-geo-alt"></i>

                                        <?= htmlspecialchars(
                                            $transaksi['alamat']
                                        ); ?>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                     DETAIL LAYANAN
                ================================================== -->

                    <div class="content-section-title">

                        <i class="bi bi-bag-check text-primary"></i>

                        Layanan yang Dibeli

                    </div>


                    <?php if ($queryDetail && mysqli_num_rows($queryDetail) > 0): ?>

                        <div class="table-responsive">

                            <table class="table align-middle">

                                <thead>

                                    <tr>

                                        <th>
                                            Layanan
                                        </th>

                                        <th class="text-center">
                                            Harga Satuan
                                        </th>

                                        <th class="text-center">
                                            Jumlah
                                        </th>

                                        <th class="text-end">
                                            Subtotal
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php while (
                                        $detail = mysqli_fetch_assoc($queryDetail)
                                    ): ?>

                                        <?php

                                        $subtotal =
                                            (int) $detail['harga_satuan']
                                            *
                                            (int) $detail['jumlah'];

                                        ?>

                                        <tr>

                                            <!-- NAMA -->

                                            <td>

                                                <div class="product-name">

                                                    <?= htmlspecialchars(
                                                        $detail['nama']
                                                    ); ?>

                                                </div>


                                                <?php if (!empty($detail['kategori'])): ?>

                                                    <div class="product-category">

                                                        <?= htmlspecialchars(
                                                            $detail['kategori']
                                                        ); ?>

                                                    </div>

                                                <?php endif; ?>

                                            </td>


                                            <!-- HARGA -->

                                            <td class="text-center">

                                                <span class="price">

                                                    Rp
                                                    <?= number_format(
                                                        $detail['harga_satuan'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </span>

                                            </td>


                                            <!-- JUMLAH -->

                                            <td class="text-center">

                                                <span class="quantity-badge">

                                                    <?= (int) $detail['jumlah']; ?>

                                                </span>

                                            </td>


                                            <!-- SUBTOTAL -->

                                            <td class="text-end">

                                                <span class="subtotal">

                                                    Rp
                                                    <?= number_format(
                                                        $subtotal,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="empty-detail">

                            <i class="bi bi-bag-x"></i>

                            <h5 class="mt-3">

                                Detail Layanan Tidak Ditemukan

                            </h5>

                            <p class="mb-0">

                                Tidak ada data detail untuk transaksi ini.

                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                     TOTAL
                ================================================== -->

                    <div class="total-box">

                        <div>

                            <div class="total-label">

                                Total Transaksi

                            </div>

                            <div class="total-count">

                                <?= $jumlah_jenis_layanan; ?>

                                jenis layanan

                            </div>

                        </div>


                        <div class="total-price">

                            Rp
                            <?= number_format(
                                $transaksi['total_harga'],
                                0,
                                ',',
                                '.'
                            ); ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
             ACTION
        ================================================== -->

            <div class="action-area">

                <a href="transaksi.php" class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Kembali

                </a>


                <a href="invoice.php?id=<?= $transaksi['id_transaksi']; ?>" class="btn btn-primary">

                    <i class="bi bi-file-earmark-text"></i>

                    Lihat Invoice

                </a>

            </div>


            <!-- FOOTER -->

            <div class="footer">

                © <?= date('Y'); ?>

                UMKM Digital · Admin Dashboard

            </div>

        </main>

    </div>


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

    <script>

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR MOBILE
        |--------------------------------------------------------------------------
        */

        function toggleSidebar() {

            const sidebar =
                document.getElementById("sidebar");

            sidebar.classList.toggle("show");

        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE SIDEBAR SAAT CLICK DI LUAR
        |--------------------------------------------------------------------------
        */

        document.addEventListener("click", function (event) {

            const sidebar =
                document.getElementById("sidebar");

            const button =
                document.querySelector(".mobile-menu-btn");

            if (
                window.innerWidth <= 991 &&
                sidebar.classList.contains("show") &&
                !sidebar.contains(event.target) &&
                !button.contains(event.target)
            ) {

                sidebar.classList.remove("show");

            }

        });

    </script>


</body>

</html>