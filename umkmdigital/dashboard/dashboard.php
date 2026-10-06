<?php
session_start();
include '../koneksi.php';

/*
|--------------------------------------------------------------------------
| PROTEKSI ADMIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../france.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();

    header("Location: ../login.php");
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
| DATA PROFIL ADMIN
|--------------------------------------------------------------------------
*/

$admin_data = null;

$query_admin = mysqli_query(
    $koneksi,
    "SELECT id, nama, email, username, hp, alamat, role
     FROM tb_user
     WHERE username = '" . mysqli_real_escape_string($koneksi, $username_admin) . "'
     AND role = 'admin'
     LIMIT 1"
);

if ($query_admin && mysqli_num_rows($query_admin) > 0) {
    $admin_data = mysqli_fetch_assoc($query_admin);
}

/*
|--------------------------------------------------------------------------
| STATISTIK USER
|--------------------------------------------------------------------------
*/

$total_pelanggan = 0;
$total_admin = 0;
$total_user = 0;

$query_user = mysqli_query(
    $koneksi,
    "SELECT role, COUNT(*) AS jumlah
     FROM tb_user
     GROUP BY role"
);

if ($query_user) {

    while ($row = mysqli_fetch_assoc($query_user)) {

        $jumlah = (int) $row['jumlah'];

        if ($row['role'] === 'pelanggan') {
            $total_pelanggan = $jumlah;
        }

        if ($row['role'] === 'admin') {
            $total_admin = $jumlah;
        }
    }
}

$total_user = $total_pelanggan + $total_admin;

$persentase_pelanggan = 0;

if ($total_user > 0) {
    $persentase_pelanggan = round(
        ($total_pelanggan / $total_user) * 100
    );
}

/*
|--------------------------------------------------------------------------
| DATA PELANGGAN TERBARU
|--------------------------------------------------------------------------
*/

$pelanggan_terbaru = mysqli_query(
    $koneksi,
    "SELECT id, nama, email, username
     FROM tb_user
     WHERE role = 'pelanggan'
     ORDER BY id DESC
     LIMIT 5"
);

?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard Admin - UMKM Digital</title>

    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/dist/css/theme.css">

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
            font-family:
                Arial,
                Helvetica,
                sans-serif;
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
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.20);
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

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .page-subtitle {
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
            background: linear-gradient(135deg,
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
           WELCOME
        ===================================================== */

        .welcome-card {
            background:
                linear-gradient(135deg,
                    #0d6efd,
                    #5b4ce2);

            border-radius: 18px;
            padding: 28px 30px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow:
                0 10px 30px rgba(13, 110, 253, 0.16);
        }

        .welcome-card::before {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            right: -80px;
            top: -100px;
        }

        .welcome-card::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            right: 120px;
            bottom: -100px;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .welcome-description {
            font-size: 14px;
            opacity: 0.88;
            margin: 0;
            max-width: 650px;
        }

        .welcome-icon {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 75px;
            opacity: 0.12;
        }


        /* =====================================================
           STAT CARDS
        ===================================================== */

        .stat-card {
            background: white;
            border: 1px solid #edf0f3;
            border-radius: 16px;
            padding: 21px;
            height: 100%;
            transition: 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 12px 28px rgba(0, 0, 0, 0.07);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 17px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .icon-blue {
            background: #eaf2ff;
            color: #0d6efd;
        }

        .icon-green {
            background: #e9f8ef;
            color: #198754;
        }

        .icon-orange {
            background: #fff4e5;
            color: #fd7e14;
        }

        .icon-purple {
            background: #f1ebff;
            color: #6f42c1;
        }

        .stat-label {
            color: #8a9199;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 25px;
            font-weight: 700;
            color: #212529;
        }

        .stat-note {
            font-size: 11px;
            color: #8a9199;
        }


        /* =====================================================
           SECTION CARD
        ===================================================== */

        .section-card {
            background: white;
            border: 1px solid #edf0f3;
            border-radius: 16px;
            overflow: hidden;
        }

        .section-header {
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f3;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #212529;
        }

        .section-subtitle {
            font-size: 11px;
            color: #8a9199;
            margin-top: 4px;
        }

        .view-all {
            font-size: 12px;
            font-weight: 600;
            color: #0d6efd;
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
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #edf0f3;
            padding: 14px 20px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 20px;
            font-size: 13px;
            color: #495057;
            vertical-align: middle;
            border-color: #f0f1f3;
        }

        .customer-name {
            font-weight: 600;
            color: #212529;
        }

        .customer-email {
            font-size: 11px;
            color: #8a9199;
            margin-top: 2px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #eaf2ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .username {
            font-weight: 600;
            color: #495057;
        }

        .status-badge {
            background: #e9f8ef;
            color: #198754;
            border-radius: 30px;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: 600;
        }


        /* =====================================================
           QUICK MENU
        ===================================================== */

        .quick-menu {
            padding: 20px;
        }

        .quick-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px;
            border-radius: 11px;
            border: 1px solid #edf0f3;
            margin-bottom: 10px;
            color: #495057;
            transition: 0.2s;
        }

        .quick-item:last-child {
            margin-bottom: 0;
        }

        .quick-item:hover {
            border-color: #cfe0ff;
            background: #f7faff;
            color: #0d6efd;
        }

        .quick-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #f1f5ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quick-title {
            font-size: 13px;
            font-weight: 600;
        }

        .quick-description {
            font-size: 10px;
            color: #8a9199;
            margin-top: 2px;
        }

        .quick-arrow {
            margin-left: auto;
            color: #adb5bd;
        }


        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .empty-state {
            text-align: center;
            padding: 45px 20px;
        }

        .empty-icon {
            font-size: 45px;
            color: #ced4da;
            margin-bottom: 12px;
        }

        .empty-title {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .empty-text {
            font-size: 12px;
            color: #8a9199;
            margin: 0;
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
           MOBILE
        ===================================================== */

        .mobile-menu-btn {
            display: none;
            border: none;
            background: transparent;
            font-size: 24px;
        }

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

            .welcome-icon {
                display: none;
            }
        }

        @media (max-width: 576px) {

            .admin-info {
                display: none;
            }

            .page-subtitle {
                display: none;
            }

            .content {
                padding: 15px;
            }

            .welcome-card {
                padding: 23px;
            }

            .welcome-title {
                font-size: 20px;
            }

            .stat-number {
                font-size: 22px;
            }

            .table-responsive {
                border-radius: 12px;
            }
        }

        /* =====================================================
   THEME BUTTON
===================================================== */

        .theme-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #ffffff;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
            transition: 0.25s;
        }

        .theme-btn:hover {
            background: #f1f5ff;
            color: #0d6efd;
            border-color: #cfe0ff;
        }


        /* =====================================================
   DARK THEME
===================================================== */

        body.dark-mode {
            background: #111827;
            color: #e5e7eb;
        }


        /* SIDEBAR */

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


        /* TOPBAR */

        body.dark-mode .topbar {
            background: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .page-title {
            color: #f9fafb;
        }

        body.dark-mode .page-subtitle {
            color: #9ca3af;
        }


        /* THEME BUTTON */

        body.dark-mode .theme-btn {
            background: #374151;
            border-color: #4b5563;
            color: #facc15;
        }

        body.dark-mode .theme-btn:hover {
            background: #4b5563;
            color: #fde68a;
        }


        /* ADMIN */

        body.dark-mode .admin-name {
            color: #f9fafb;
        }

        body.dark-mode .admin-role {
            color: #9ca3af;
        }


        /* STAT CARD */

        body.dark-mode .stat-card {
            background: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .stat-label {
            color: #9ca3af;
        }

        body.dark-mode .stat-number {
            color: #f9fafb;
        }

        body.dark-mode .stat-note {
            color: #9ca3af;
        }


        /* SECTION CARD */

        body.dark-mode .section-card {
            background: #1f2937;
            border-color: #374151;
        }

        body.dark-mode .section-header {
            border-color: #374151;
        }

        body.dark-mode .section-title {
            color: #f9fafb;
        }

        body.dark-mode .section-subtitle {
            color: #9ca3af;
        }


        /* TABLE */

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

        body.dark-mode .customer-name {
            color: #f9fafb;
        }

        body.dark-mode .customer-email {
            color: #9ca3af;
        }

        body.dark-mode .username {
            color: #d1d5db;
        }


        /* QUICK MENU */

        body.dark-mode .quick-item {
            border-color: #374151;
            color: #d1d5db;
        }

        body.dark-mode .quick-item:hover {
            background: #374151;
            border-color: #4b5563;
            color: #60a5fa;
        }

        body.dark-mode .quick-description {
            color: #9ca3af;
        }

        body.dark-mode .quick-arrow {
            color: #6b7280;
        }


        /* FOOTER */

        body.dark-mode .footer {
            color: #6b7280;
        }

        /* =====================================================
   ADMIN INFORMATION
===================================================== */

        .admin-information {
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .admin-info-avatar {
            width: 75px;
            height: 75px;
            min-width: 75px;
            border-radius: 18px;

            background: linear-gradient(135deg,
                    #0d6efd,
                    #6f42c1);

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 28px;
            font-weight: 700;

            box-shadow:
                0 8px 20px rgba(13, 110, 253, 0.18);
        }

        .admin-details {
            flex: 1;

            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;
        }

        .admin-detail-item {
            min-width: 0;
        }

        .admin-detail-label {
            color: #8a9199;
            font-size: 11px;
            margin-bottom: 5px;

            display: flex;
            align-items: center;
            gap: 6px;
        }

        .admin-detail-label i {
            font-size: 12px;
        }

        .admin-detail-value {
            color: #212529;
            font-size: 13px;
            font-weight: 600;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            background: #e9f8ef;
            color: #198754;

            border-radius: 30px;

            padding: 6px 11px;

            font-size: 10px;
            font-weight: 600;
        }


        /* =====================================================
   DARK MODE - ADMIN INFORMATION
===================================================== */

        body.dark-mode .admin-detail-value {
            color: #f9fafb;
        }

        body.dark-mode .admin-detail-label {
            color: #9ca3af;
        }

        body.dark-mode .admin-status-badge {
            background: #123b29;
            color: #6ee7a0;
        }


        /* =====================================================
   RESPONSIVE ADMIN INFORMATION
===================================================== */

        @media (max-width: 991px) {

            .admin-details {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 576px) {

            .admin-information {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-info-avatar {
                width: 60px;
                height: 60px;
                min-width: 60px;
                border-radius: 15px;
                font-size: 23px;
            }

            .admin-details {
                width: 100%;

                grid-template-columns:
                    1fr;

                gap: 14px;
            }

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

            <img src="../assets/brand/umkmlogoo.png" class="brand-logo" alt="UMKM Digital">

            <div class="brand-text">

                UMKM Digital

                <span>Admin Panel</span>

            </div>

        </div>


        <!-- SIDEBAR CONTENT -->

        <div class="sidebar-content">

            <div class="menu-title">
                Menu Utama
            </div>

            <ul class="sidebar-menu">

                <li>
                    <a href="dashboard.php" class="active">

                        <i class="bi bi-grid-1x2-fill"></i>

                        Dashboard

                    </a>
                </li>

                <li>
                    <a href="..//assets/uploads/layanan.php">

                        <i class="bi bi-briefcase-fill"></i>

                        Layanan

                    </a>
                </li>

                <li>
                    <a href="../pelanggan.php">

                        <i class="bi bi-people-fill"></i>

                        Pelanggan

                    </a>
                </li>

                <li>
                    <a href="../transaksi.php">

                        <i class="bi bi-receipt"></i>

                        Transaksi
                    </a>
                </li>

            </ul>


            <hr class="sidebar-divider">


            <div class="menu-title">
                Pengaturan
            </div>

            <ul class="sidebar-menu">

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
                    <a href="dashboard.php?logout=true" class="logout-link"
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

                    <h1 class="page-title">
                        Dashboard
                    </h1>

                    <p class="page-subtitle">
                        Kelola website UMKM Digital kamu
                    </p>

                </div>

            </div>

            <!-- ADMIN PROFILE -->
            <div class="admin-profile">

                <div class="admin-avatar">

                    <?= strtoupper(substr($username_admin, 0, 1)); ?>

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


            <!-- =================================================
             WELCOME
        ================================================== -->

            <section class="welcome-card">

                <div class="welcome-content">

                    <div class="welcome-title">

                        Selamat datang, <?= htmlspecialchars($username_admin); ?> 👋

                    </div>

                    <p class="welcome-description">

                        Pantau dan kelola aktivitas UMKM Digital
                        melalui halaman administrator ini.

                    </p>

                </div>

                <i class="bi bi-speedometer2 welcome-icon"></i>

            </section>


            <!-- =================================================
             STATISTICS
        ================================================== -->

            <div class="row g-3 mb-4">


                <!-- TOTAL USER -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon icon-blue">

                                <i class="bi bi-people-fill"></i>

                            </div>

                        </div>

                        <div class="stat-label">
                            Total Pengguna
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_user); ?>
                        </div>

                        <div class="stat-note">
                            Semua akun terdaftar
                        </div>

                    </div>

                </div>


                <!-- PELANGGAN -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon icon-green">

                                <i class="bi bi-person-check-fill"></i>

                            </div>

                        </div>

                        <div class="stat-label">
                            Total Pelanggan
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_pelanggan); ?>
                        </div>

                        <div class="stat-note">
                            Akun pelanggan
                        </div>

                    </div>

                </div>


                <!-- ADMIN -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon icon-purple">

                                <i class="bi bi-shield-check"></i>

                            </div>

                        </div>

                        <div class="stat-label">
                            Total Admin
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_admin); ?>
                        </div>

                        <div class="stat-note">
                            Administrator sistem
                        </div>

                    </div>

                </div>


                <!-- PERSENTASE PELANGGAN -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="stat-card">

                        <div class="stat-top">

                            <div class="stat-icon icon-orange">

                                <i class="bi bi-graph-up-arrow"></i>

                            </div>

                        </div>

                        <div class="stat-label">
                            Persentase Pelanggan
                        </div>

                        <div class="stat-number">
                            <?= $persentase_pelanggan; ?>%
                        </div>

                        <div class="stat-note">
                            Dari seluruh pengguna
                        </div>

                    </div>

                </div>


                <!-- =================================================
             LOWER CONTENT
        ================================================== -->

                <!-- =================================================
     ADMIN INFORMATION
================================================= -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <div>

                            <h2 class="section-title">
                                Informasi Akun Admin
                            </h2>

                            <div class="section-subtitle">
                                Informasi akun administrator yang sedang login
                            </div>

                        </div>

                        <div class="admin-status-badge">
                            <i class="bi bi-check-circle-fill"></i>
                            Aktif
                        </div>

                    </div>


                    <div class="admin-information">

                        <!-- ADMIN AVATAR -->

                        <div class="admin-info-avatar">

                            <?= strtoupper(
                                substr(
                                    $admin_data['nama'] ?: $admin_data['username'],
                                    0,
                                    1
                                )
                            ); ?>

                        </div>


                        <!-- ADMIN DATA -->

                        <div class="admin-details">

                            <div class="admin-detail-item">

                                <div class="admin-detail-label">
                                    <i class="bi bi-person-fill"></i>
                                    Nama
                                </div>

                                <div class="admin-detail-value">

                                    <?= htmlspecialchars(
                                        $admin_data['nama'] ?: '-'
                                    ); ?>

                                </div>

                            </div>


                            <div class="admin-detail-item">

                                <div class="admin-detail-label">
                                    <i class="bi bi-at"></i>
                                    Username
                                </div>

                                <div class="admin-detail-value">

                                    @<?= htmlspecialchars(
                                        $admin_data['username'] ?? $username_admin
                                    ); ?>

                                </div>

                            </div>


                            <div class="admin-detail-item">

                                <div class="admin-detail-label">
                                    <i class="bi bi-envelope-fill"></i>
                                    Email
                                </div>

                                <div class="admin-detail-value">

                                    <?= htmlspecialchars(
                                        $admin_data['email'] ?: '-'
                                    ); ?>

                                </div>

                            </div>


                            <div class="admin-detail-item">

                                <div class="admin-detail-label">
                                    <i class="bi bi-shield-fill-check"></i>
                                    Role
                                </div>

                                <div class="admin-detail-value">

                                    Administrator

                                </div>

                            </div>


                            <div class="admin-detail-item">

                                <div class="admin-detail-label">
                                    <i class="bi bi-fingerprint"></i>
                                    ID Admin
                                </div>

                                <div class="admin-detail-value">

                                    #<?= htmlspecialchars(
                                        $admin_data['id'] ?? '-'
                                    ); ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="row g-4">


                    <!-- =============================================
                 PELANGGAN TERBARU
            ============================================== -->

                    <div class="col-12 col-xl-8">

                        <div class="section-card">

                            <div class="section-header">

                                <div>

                                    <h2 class="section-title">
                                        Pelanggan Terbaru
                                    </h2>

                                    <div class="section-subtitle">
                                        Daftar pelanggan yang baru terdaftar
                                    </div>

                                </div>

                                <a href="../pelanggan.php" class="view-all">
                                    Lihat Semua
                                </a>

                            </div>


                            <?php if ($pelanggan_terbaru && mysqli_num_rows($pelanggan_terbaru) > 0): ?>

                                        <div class="table-responsive">

                                            <table class="table">

                                                <thead>

                                                    <tr>

                                                        <th>
                                                            Pelanggan
                                                        </th>

                                                        <th>
                                                            Username
                                                        </th>

                                                        <th>
                                                            Status
                                                        </th>

                                                    </tr>

                                                </thead>


                                                <tbody>

                                                    <?php while ($pelanggan = mysqli_fetch_assoc($pelanggan_terbaru)): ?>

                                                                <tr>

                                                                    <td>

                                                                        <div class="d-flex align-items-center gap-3">

                                                                            <div class="user-avatar">

                                                                                <?= strtoupper(
                                                                                    substr(
                                                                                        $pelanggan['nama'] ?: $pelanggan['username'],
                                                                                        0,
                                                                                        1
                                                                                    )
                                                                                ); ?>

                                                                            </div>


                                                                            <div>

                                                                                <div class="customer-name">

                                                                                    <?= htmlspecialchars(
                                                                                        $pelanggan['nama']
                                                                                    ); ?>

                                                                                </div>

                                                                                <div class="customer-email">

                                                                                    <?= htmlspecialchars(
                                                                                        $pelanggan['email']
                                                                                    ); ?>

                                                                                </div>

                                                                            </div>

                                                                        </div>

                                                                    </td>


                                                                    <td>

                                                                        <span class="username">

                                                                            @<?= htmlspecialchars(
                                                                                $pelanggan['username']
                                                                            ); ?>

                                                                        </span>

                                                                    </td>


                                                                    <td>

                                                                        <span class="status-badge">

                                                                            Aktif

                                                                        </span>

                                                                    </td>

                                                                </tr>

                                                    <?php endwhile; ?>

                                                </tbody>

                                            </table>

                                        </div>

                            <?php else: ?>

                                        <div class="empty-state">

                                            <div class="empty-icon">

                                                <i class="bi bi-people"></i>

                                            </div>

                                            <div class="empty-title">

                                                Belum ada pelanggan

                                            </div>

                                            <p class="empty-text">

                                                Data pelanggan akan muncul di sini
                                                setelah ada pengguna yang melakukan registrasi.

                                            </p>

                                        </div>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- =============================================
                 QUICK MENU
            ============================================== -->

                    <div class="col-12 col-xl-4">

                        <div class="section-card">

                            <div class="section-header">

                                <div>

                                    <h2 class="section-title">
                                        Menu Cepat
                                    </h2>

                                    <div class="section-subtitle">
                                        Akses fitur administrator
                                    </div>

                                </div>

                            </div>


                            <div class="quick-menu">


                                <a href="#" class="quick-item">

                                    <div class="quick-icon">

                                        <i class="bi bi-bag-plus-fill"></i>

                                    </div>

                                    <div>

                                        <div class="quick-title">
                                            Kelola Pesanan
                                        </div>

                                        <div class="quick-description">
                                            Lihat dan proses pesanan pelanggan
                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right quick-arrow"></i>

                                </a>


                                <a href="../assets/uploads/layanan.php" class="quick-item">

                                    <div class="quick-icon">

                                        <i class="bi bi-briefcase-fill"></i>

                                    </div>

                                    <div>

                                        <div class="quick-title">
                                            Kelola Layanan
                                        </div>

                                        <div class="quick-description">
                                            Atur layanan UMKM Digital
                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right quick-arrow"></i>

                                </a>


                                <a href="../pelanggan.php" class="quick-item">

                                    <div class="quick-icon">

                                        <i class="bi bi-person-lines-fill"></i>

                                    </div>

                                    <div>

                                        <div class="quick-title">
                                            Data Pelanggan
                                        </div>

                                        <div class="quick-description">
                                            Lihat data seluruh pelanggan
                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right quick-arrow"></i>

                                </a>


                                <a href="#" class="quick-item">

                                    <div class="quick-icon">

                                        <i class="bi bi-bar-chart-line-fill"></i>

                                    </div>

                                    <div>

                                        <div class="quick-title">
                                            Laporan
                                        </div>

                                        <div class="quick-description">
                                            Pantau perkembangan bisnis
                                        </div>

                                    </div>

                                    <i class="bi bi-chevron-right quick-arrow"></i>

                                </a>


                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
             FOOTER
        ================================================== -->

                <div class="footer">

                    © 2026 UMKM Digital · Admin Dashboard

                </div>


        </main>
    </div>


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script src="assets/dist/js/bootstrap.bundle.min.js"></s>
    <script src="assets/js/theme.js"></script>

    <script>

        function toggleSidebar() {

            const sidebar =
                document.getElementById("sidebar");

            sidebar.classList.toggle("show");

        }

        const themeBtn = document.getElementById("themeBtn");
        const themeIcon = document.getElementById("themeIcon");

        // Cek theme yang tersimpan
        const savedTheme = localStorage.getItem("dashboardTheme");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");

            themeIcon.classList.remove("bi-moon-fill");
            themeIcon.classList.add("bi-sun-fill");
        }


        // Tombol ganti theme
        themeBtn.addEventListener("click", function () {

            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {

                localStorage.setItem("dashboardTheme", "dark");

                themeIcon.classList.remove("bi-moon-fill");
                themeIcon.classList.add("bi-sun-fill");

            } else {

                localStorage.setItem("dashboardTheme", "light");

                themeIcon.classList.remove("bi-sun-fill");
                themeIcon.classList.add("bi-moon-fill");

            }

        });

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
                window.innerWidth <= 991 && sidebar.classList.contains("show") && !sidebar.contains(event.target) &&
                !button.contains(event.target)) {sidebar.classList.remove("show");}
        }); </script>


</body>

</html>