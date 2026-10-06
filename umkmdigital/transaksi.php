<?php
session_start();
include 'koneksi.php';


/* =========================================================
   PROTEKSI ADMIN
========================================================= */

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: france.php");
    exit;
}

$username_admin = $_SESSION['username'];

/* =========================================================
   HAPUS TRANSAKSI
========================================================= */

if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {

    $id_hapus = (int) $_GET['hapus'];

    // Hapus detail transaksi terlebih dahulu
    mysqli_query(
        $koneksi,
        "DELETE FROM tb_detail
         WHERE id_transaksi = $id_hapus"
    );

    // Hapus transaksi
    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM tb_transaksi
         WHERE id_transaksi = $id_hapus"
    );

    if ($hapus) {

        echo "<script>
                alert('Transaksi berhasil dihapus!');
                window.location='transaksi.php';
              </script>";

        exit;

    } else {

        echo "<script>
                alert('Transaksi gagal dihapus!');
                window.location='transaksi.php';
              </script>";

        exit;
    }
}


/* =========================================================
   DATA TRANSAKSI
========================================================= */

$query = mysqli_query(
    $koneksi,
    "SELECT
        t.id_transaksi,
        t.id_pelanggan,
        t.tanggal,
        t.total_harga,
        u.nama,
        u.username

     FROM tb_transaksi t

     LEFT JOIN tb_user u
        ON t.id_pelanggan = u.id

     ORDER BY t.id_transaksi DESC"
);


/* =========================================================
   STATISTIK
========================================================= */

$queryTotal = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM tb_transaksi"
);

$dataTotal = mysqli_fetch_assoc($queryTotal);

$total_transaksi = $dataTotal['total'];


/* TOTAL PENDAPATAN */

$queryPendapatan = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(total_harga), 0) AS total
     FROM tb_transaksi"
);

$dataPendapatan = mysqli_fetch_assoc($queryPendapatan);

$total_pendapatan = $dataPendapatan['total'];


/* TRANSAKSI HARI INI */

$queryHariIni = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM tb_transaksi
     WHERE tanggal = CURDATE()"
);

$dataHariIni = mysqli_fetch_assoc($queryHariIni);

$transaksi_hari_ini = $dataHariIni['total'];


/* =========================================================
   FORMAT RUPIAH
========================================================= */

function rupiah($angka)
{
    return 'Rp ' . number_format(
        $angka,
        0,
        ',',
        '.'
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Transaksi | UMKM Digital</title>


    <!-- BOOTSTRAP -->

    <link
        href="assets/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- BOOTSTRAP ICON -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


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

            transition: .3s;

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

            transition: .3s;

        }


        .brand {

            height: 75px;

            display: flex;

            align-items: center;

            padding: 0 25px;

            border-bottom:
                1px solid #f0f1f3;

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

            letter-spacing: .7px;

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

            transition: .2s;

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
                0 6px 15px
                rgba(13, 110, 253, .20);

        }


        .sidebar-divider {

            border: 0;

            border-top:
                1px solid #eeeeee;

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

            border-bottom:
                1px solid #e9ecef;

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

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #6f42c1
                );

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
           MOBILE MENU
        ===================================================== */

        .mobile-menu-btn {

            display: none;

            border: none;

            background: transparent;

            font-size: 24px;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {

            padding: 30px;

        }


        .page-header {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        .page-header-title {

            font-size: 25px;

            font-weight: 700;

            margin: 0;

            color: #111827;

        }


        .page-header-description {

            font-size: 13px;

            color: #8a9199;

            margin: 5px 0 0;

        }


        /* =====================================================
           STAT CARD
        ===================================================== */

        .stat-card {

            background: white;

            border: 1px solid #edf0f3;

            border-radius: 16px;

            padding: 21px;

            height: 100%;

            transition: .25s;

        }


        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 12px 28px
                rgba(0, 0, 0, .07);

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


        .icon-purple {

            background: #f1ebff;

            color: #6f42c1;

        }


        .stat-label {

            color: #8a9199;

            font-size: 13px;

            margin-top: 15px;

        }


        .stat-number {

            font-size: 25px;

            font-weight: 700;

            color: #212529;

            margin-top: 4px;

        }


        /* =====================================================
           TABLE CARD
        ===================================================== */

        .section-card {

            background: white;

            border: 1px solid #edf0f3;

            border-radius: 16px;

            overflow: hidden;

        }


        .section-header {

            padding: 20px 22px;

            border-bottom:
                1px solid #edf0f3;

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


        .table {

            margin: 0;

        }


        .table thead th {

            background: #fafbfc;

            color: #7a828a;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .4px;

            border-bottom:
                1px solid #edf0f3;

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


        .transaction-id {

            font-weight: 700;

            color: #212529;

        }


        .customer-name {

            font-weight: 600;

            color: #212529;

        }


        .customer-username {

            font-size: 11px;

            color: #8a9199;

        }


        .transaction-badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 50px;

            background: #eaf2ff;

            color: #0d6efd;

            font-size: 11px;

            font-weight: 700;

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


        body.dark-mode .page-title {

            color: #f9fafb;

        }


        body.dark-mode .page-subtitle {

            color: #9ca3af;

        }


        body.dark-mode .admin-name {

            color: #f9fafb;

        }


        body.dark-mode .admin-role {

            color: #9ca3af;

        }


        body.dark-mode .stat-card,

        body.dark-mode .section-card {

            background: #1f2937;

            border-color: #374151;

        }


        body.dark-mode .stat-number,

        body.dark-mode .section-title,

        body.dark-mode .transaction-id,

        body.dark-mode .customer-name {

            color: #f9fafb;

        }


        body.dark-mode .stat-label,

        body.dark-mode .section-subtitle,

        body.dark-mode .customer-username {

            color: #9ca3af;

        }


        body.dark-mode .table thead th {

            background: #263244;

            color: #9ca3af;

            border-color: #374151;

        }


        body.dark-mode .table tbody td {

            color: #d1d5db;

            border-color: #374151;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .sidebar {

                transform:
                    translateX(-100%);

            }


            .sidebar.show {

                transform:
                    translateX(0);

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

        }


        @media (max-width: 576px) {

            .content {

                padding: 20px 15px;

            }


            .admin-info {

                display: none;

            }


            .page-header {

                align-items:
                    flex-start;

                flex-direction:
                    column;

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

        <img
            src="assets/brand/umkmlogoo.png"
            class="brand-logo"
            alt="UMKM Digital">

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


            <li>

                <a href="dashboard/dashboard.php">

                    <i class="bi bi-grid-1x2-fill"></i>

                    Dashboard

                </a>

            </li>

            <li>

                <a href="assets/uploads/layanan.php">

                    <i class="bi bi-briefcase-fill"></i>

                    Layanan

                </a>

            </li>


            <li>

                <a href="pelanggan.php">

                    <i class="bi bi-people-fill"></i>

                    Pelanggan

                </a>

            </li>


            <li>

                <a href="transaksi.php"
                   class="active">

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

                <a
                    href="javascript:void(0)"
                    id="themeBtn">

                    <i
                        class="bi bi-moon-fill"
                        id="themeIcon"></i>

                    <span id="themeText">
                        Mode Gelap
                    </span>

                </a>

            </li>


            <!-- LOGOUT -->

            <li>

                <a
                    href="dashboard/dashboard.php?logout=true"
                    class="logout-link"
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


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="d-flex align-items-center gap-3">


            <button
                class="mobile-menu-btn"
                onclick="toggleSidebar()"
                type="button">

                <i class="bi bi-list"></i>

            </button>


            <div>

                <h1 class="page-title">

                    Transaksi

                </h1>


                <p class="page-subtitle">

                    Kelola transaksi pelanggan UMKM Digital

                </p>

            </div>

        </div>


        <!-- ADMIN PROFILE -->

        <div class="admin-profile">


            <div class="admin-avatar">

                <?= strtoupper(
                    substr($username_admin, 0, 1)
                ); ?>

            </div>


            <div class="admin-info">

                <div class="admin-name">

                    <?= htmlspecialchars(
                        $username_admin
                    ); ?>

                </div>


                <div class="admin-role">

                    Administrator

                </div>

            </div>

        </div>


    </header>



    <!-- CONTENT -->

    <main class="content">


        <!-- HEADER -->

        <div class="page-header">


            <div>

                <h2 class="page-header-title">

                    <i class="bi bi-receipt text-primary"></i>

                    Data Transaksi

                </h2>


                <p class="page-header-description">

                    Lihat seluruh transaksi yang dilakukan pelanggan.

                </p>

            </div>


        </div>



        <!-- STATISTIK -->

        <div class="row g-3 mb-4">


            <div class="col-12 col-md-4">

                <div class="stat-card">

                    <div class="stat-icon icon-blue">

                        <i class="bi bi-receipt"></i>

                    </div>


                    <div class="stat-label">

                        Total Transaksi

                    </div>


                    <div class="stat-number">

                        <?= number_format(
                            $total_transaksi
                        ); ?>

                    </div>

                </div>

            </div>


            <div class="col-12 col-md-4">

                <div class="stat-card">

                    <div class="stat-icon icon-green">

                        <i class="bi bi-cash-stack"></i>

                    </div>


                    <div class="stat-label">

                        Total Pendapatan

                    </div>


                    <div class="stat-number">

                        <?= rupiah(
                            $total_pendapatan
                        ); ?>

                    </div>

                </div>

            </div>


            <div class="col-12 col-md-4">

                <div class="stat-card">

                    <div class="stat-icon icon-purple">

                        <i class="bi bi-calendar-check"></i>

                    </div>


                    <div class="stat-label">

                        Transaksi Hari Ini

                    </div>


                    <div class="stat-number">

                        <?= number_format(
                            $transaksi_hari_ini
                        ); ?>

                    </div>

                </div>

            </div>


        </div>



        <!-- TABLE -->

        <div class="section-card">


            <div class="section-header">


                <div>

                    <h3 class="section-title">

                        Daftar Transaksi

                    </h3>


                    <div class="section-subtitle">

                        Semua transaksi pelanggan

                    </div>

                </div>


                <span class="badge text-bg-primary">

                    <?= number_format(
                        $total_transaksi
                    ); ?>

                    transaksi

                </span>


            </div>


            <div class="table-responsive">


                <table class="table align-middle">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Total
                            </th>

                            <th class="text-end">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php if (
                            mysqli_num_rows($query) > 0
                        ): ?>


                            <?php while (
                                $transaksi =
                                mysqli_fetch_assoc($query)
                            ): ?>


                                <tr>


                                    <td>

                                        <span class="transaction-id">

                                            #<?= $transaksi[
                                                'id_transaksi'
                                            ]; ?>

                                        </span>

                                    </td>


                                    <td>

                                        <div class="customer-name">

                                            <?= htmlspecialchars(
                                                $transaksi['nama']
                                                    ?: 'Pelanggan'
                                            ); ?>

                                        </div>


                                        <div class="customer-username">

                                            @<?= htmlspecialchars(
                                                $transaksi['username']
                                                    ?: '-'
                                            ); ?>

                                        </div>

                                    </td>


                                    <td>

                                        <i class="bi bi-calendar3 me-1"></i>

                                        <?= date(
                                            'd-m-Y',
                                            strtotime(
                                                $transaksi['tanggal']
                                            )
                                        ); ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= rupiah(
                                                $transaksi['total_harga']
                                            ); ?>

                                        </strong>

                                    </td>


                                    <td class="text-end">

                                        <a href="detail_transaksi.php?id=<?= $transaksi['id_transaksi']; ?>"
                                                class="btn btn-sm btn-outline-primary">

                                            <i class="bi bi-eye"></i>

                                            Detail

                                        </a>

                                        <a href="transaksi.php?hapus=<?= $transaksi['id_transaksi']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin ingin menghapus transaksi ini? Data detail transaksi juga akan dihapus.');">

                                            <i class="bi bi-trash"></i>

                                                Hapus

                                            </a>

                                    </td>
                                    
                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center py-5">

                                    <i
                                        class="bi bi-receipt"
                                        style="
                                            font-size:50px;
                                            color:#9ca3af;
                                        "></i>


                                    <div class="mt-3">

                                        Belum ada transaksi.

                                    </div>

                                </td>

                            </tr>


                        <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </main>


</div>



<!-- BOOTSTRAP -->

<script src="assets/dist/js/bootstrap.bundle.min.js"></script>


<!-- THEME -->

<script src="assets/js/theme.js"></script>


<script>

function toggleSidebar()
{
    const sidebar =
        document.getElementById("sidebar");

    sidebar.classList.toggle("show");
}

</script>


</body>

</html>