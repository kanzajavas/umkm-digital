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
   RESET PASSWORD PELANGGAN
========================================================= */

if (isset($_GET['reset_password']) && is_numeric($_GET['reset_password'])) {

    $id_reset = (int) $_GET['reset_password'];
    $password_baru = "123456";

    $password_safe = mysqli_real_escape_string(
        $koneksi,
        $password_baru
    );

    $reset = mysqli_query(
        $koneksi,
        "UPDATE tb_user
         SET password = '$password_safe'
         WHERE id = $id_reset
         AND role = 'pelanggan'"
    );

    if ($reset) {
        echo "<script>
                alert('Password pelanggan berhasil direset menjadi 123456');
                window.location='pelanggan.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Gagal mereset password pelanggan.');
                window.location='pelanggan.php';
              </script>";
        exit;
    }
}


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$where = "WHERE u.role = 'pelanggan'";


if ($search != '') {

    $search_safe =
        mysqli_real_escape_string(
            $koneksi,
            $search
        );

    $where .= "
        AND (
            u.nama LIKE '%$search_safe%'
            OR u.username LIKE '%$search_safe%'
            OR u.email LIKE '%$search_safe%'
            OR u.hp LIKE '%$search_safe%'
        )
    ";

}


/* =========================================================
   DATA PELANGGAN
========================================================= */

$query = mysqli_query(
    $koneksi,

    "SELECT

        u.id,
        u.nama,
        u.username,
        u.email,
        u.hp,
        u.alamat,

        (
            SELECT COUNT(*)
            FROM tb_transaksi t
            WHERE t.id_pelanggan = u.id
        ) AS jumlah_transaksi

     FROM tb_user u

     $where

     ORDER BY u.id DESC"
);


/* =========================================================
   TOTAL PELANGGAN
========================================================= */

$queryTotal = mysqli_query(
    $koneksi,

    "SELECT COUNT(*) AS total
     FROM tb_user
     WHERE role = 'pelanggan'"
);

$dataTotal =
    mysqli_fetch_assoc($queryTotal);

$total_pelanggan =
    $dataTotal['total'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pelanggan | UMKM Digital</title>


    <!-- BOOTSTRAP -->

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- ICON -->

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

            border-right:
                1px solid #e9ecef;

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
                0 6px 15px rgba(13, 110, 253, .20);

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
           MOBILE
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
           STAT
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

            transform:
                translateY(-4px);

            box-shadow:
                0 12px 28px rgba(0, 0, 0, .07);

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
           SEARCH
        ===================================================== */

        .search-card {

            background: white;

            border:
                1px solid #edf0f3;

            border-radius: 16px;

            padding: 20px;

            margin-bottom: 20px;

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .section-card {

            background: white;

            border:
                1px solid #edf0f3;

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


        .customer-avatar {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            background: #eaf2ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: 700;

        }


        .customer-name {

            font-weight: 600;

            color: #212529;

        }


        .customer-username {

            font-size: 11px;

            color: #8a9199;

            margin-top: 2px;

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

        body.dark-mode .search-card,

        body.dark-mode .section-card {

            background: #1f2937;

            border-color: #374151;

        }


        body.dark-mode .stat-number,

        body.dark-mode .section-title,

        body.dark-mode .customer-name,

        body.dark-mode .page-header-title {

            color: #f9fafb;

        }


        body.dark-mode .stat-label,

        body.dark-mode .section-subtitle,

        body.dark-mode .customer-username,

        body.dark-mode .page-header-description {

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


        body.dark-mode .form-control,

        body.dark-mode .input-group-text {

            background: #263244;

            color: #f9fafb;

            border-color: #374151;

        }


        body.dark-mode .form-control::placeholder {

            color: #9ca3af;

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

            <img src="assets/brand/umkmlogoo.png" class="brand-logo" alt="UMKM Digital">

            <div class="brand-text">

                UMKM Digital

                <span>
                    Admin Panel
                </span>

            </div>

        </div>


        <!-- CONTENT -->

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

                    <a href="pelanggan.php" class="active">

                        <i class="bi bi-people-fill"></i>

                        Pelanggan

                    </a>

                </li>


                <li>

                    <a href="transaksi.php">

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
                            Mode Gelap
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


        <!-- TOPBAR -->

        <header class="topbar">


            <div class="d-flex align-items-center gap-3">


                <button class="mobile-menu-btn" onclick="toggleSidebar()" type="button">

                    <i class="bi bi-list"></i>

                </button>


                <div>

                    <h1 class="page-title">

                        Pelanggan

                    </h1>


                    <p class="page-subtitle">

                        Kelola data pelanggan UMKM Digital

                    </p>

                </div>

            </div>


            <!-- ADMIN -->

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

                        <i class="bi bi-people-fill text-primary"></i>

                        Kelola Pelanggan

                    </h2>


                    <p class="page-header-description">

                        Lihat dan kelola seluruh akun pelanggan yang terdaftar.

                    </p>

                </div>


            </div>



            <!-- STAT -->

            <div class="row g-3 mb-4">


                <div class="col-12 col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon icon-blue">

                            <i class="bi bi-people-fill"></i>

                        </div>


                        <div class="stat-label">

                            Total Pelanggan

                        </div>


                        <div class="stat-number">

                            <?= number_format(
                                $total_pelanggan
                            ); ?>

                        </div>


                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon icon-blue">

                            <i class="bi bi-person-check-fill"></i>

                        </div>


                        <div class="stat-label">

                            Status Akun

                        </div>


                        <div class="stat-number">

                            Aktif

                        </div>


                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon icon-blue">

                            <i class="bi bi-person-vcard-fill"></i>

                        </div>


                        <div class="stat-label">

                            Role

                        </div>


                        <div class="stat-number">

                            Pelanggan

                        </div>


                    </div>

                </div>


            </div>



            <!-- SEARCH -->

            <div class="search-card">


                <form method="GET" action="pelanggan.php">


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>


                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nama, username, email, atau HP..." value="<?= htmlspecialchars(
                                $search
                            ); ?>">


                        <button type="submit" class="btn btn-primary">

                            <i class="bi bi-search"></i>

                            Cari

                        </button>


                        <?php if ($search != ''): ?>

                            <a href="pelanggan.php" class="btn btn-outline-secondary">

                                <i class="bi bi-x-lg"></i>

                            </a>

                        <?php endif; ?>


                    </div>


                </form>


            </div>



            <!-- TABLE -->

            <div class="section-card">


                <div class="section-header">


                    <div>

                        <h3 class="section-title">

                            Daftar Pelanggan

                        </h3>


                        <div class="section-subtitle">

                            Data seluruh pelanggan yang terdaftar

                        </div>

                    </div>


                    <span class="badge text-bg-primary">

                        <?= mysqli_num_rows($query); ?>

                        pelanggan

                    </span>


                </div>



                <div class="table-responsive">


                    <table class="table align-middle">


                        <thead>

                            <tr>

                                <th>
                                    Pelanggan
                                </th>

                                <th>
                                    Kontak
                                </th>

                                <th>
                                    Alamat
                                </th>

                                <th>
                                    Transaksi
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
                                    $pelanggan =
                                    mysqli_fetch_assoc($query)
                                ): ?>


                                    <tr>


                                        <!-- PELANGGAN -->

                                        <td>

                                            <div class="d-flex align-items-center gap-3">


                                                <div class="customer-avatar">

                                                    <?= strtoupper(
                                                        substr(
                                                            $pelanggan['nama']
                                                            ?: $pelanggan['username'],
                                                            0,
                                                            1
                                                        )
                                                    ); ?>

                                                </div>


                                                <div>

                                                    <div class="customer-name">

                                                        <?= htmlspecialchars(
                                                            $pelanggan['nama']
                                                            ?: 'Belum diisi'
                                                        ); ?>

                                                    </div>


                                                    <div class="customer-username">

                                                        @<?= htmlspecialchars(
                                                            $pelanggan['username']
                                                        ); ?>

                                                    </div>

                                                </div>


                                            </div>

                                        </td>


                                        <!-- KONTAK -->

                                        <td>


                                            <?php if (
                                                !empty(
                                                $pelanggan['email']
                                            )
                                            ): ?>

                                                <div>

                                                    <i class="bi bi-envelope text-primary me-1"></i>

                                                    <?= htmlspecialchars(
                                                        $pelanggan['email']
                                                    ); ?>

                                                </div>

                                            <?php endif; ?>


                                            <?php if (
                                                !empty(
                                                $pelanggan['hp']
                                            )
                                            ): ?>

                                                <div class="customer-username">

                                                    <i class="bi bi-telephone me-1"></i>

                                                    <?= htmlspecialchars(
                                                        $pelanggan['hp']
                                                    ); ?>

                                                </div>

                                            <?php endif; ?>


                                            <?php if (
                                                empty(
                                                $pelanggan['email']
                                            ) &&
                                                empty(
                                                $pelanggan['hp']
                                            )
                                            ): ?>

                                                <span class="text-muted">

                                                    Belum diisi

                                                </span>

                                            <?php endif; ?>


                                        </td>


                                        <!-- ALAMAT -->

                                        <td>


                                            <?php if (
                                                !empty(
                                                $pelanggan['alamat']
                                            )
                                            ): ?>

                                                <?= htmlspecialchars(
                                                    $pelanggan['alamat']
                                                ); ?>

                                            <?php else: ?>

                                                <span class="text-muted">

                                                    Belum diisi

                                                </span>

                                            <?php endif; ?>


                                        </td>


                                        <!-- TRANSAKSI -->

                                        <td>

                                            <span class="transaction-badge">

                                                <i class="bi bi-receipt me-1"></i>

                                                <?= $pelanggan[
                                                    'jumlah_transaksi'
                                                ]; ?>

                                                transaksi

                                            </span>

                                        </td>


                                        <!-- AKSI -->

                                        <td class="text-end">

                                            <a href="detail_pelanggan.php?id=<?= $pelanggan['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">

                                                <i class="bi bi-eye"></i>

                                                Detail

                                            </a>

                                            <a href="pelanggan.php?reset_password=<?= $pelanggan['id']; ?>"
                                                class="btn btn-sm btn-outline-warning"
                                                onclick="return confirm('Yakin ingin mereset password pelanggan ini menjadi 123456?');">

                                                <i class="bi bi-key-fill"></i>

                                                Reset Password

                                            </a>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="5" class="text-center py-5">


                                        <i class="bi bi-person-x" style="
                                            font-size:50px;
                                            color:#9ca3af;
                                        "></i>


                                        <div class="mt-3">

                                            Pelanggan tidak ditemukan.

                                        </div>


                                        <?php if ($search != ''): ?>

                                            <a href="pelanggan.php" class="btn btn-primary mt-3">

                                                Tampilkan Semua

                                            </a>

                                        <?php endif; ?>


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

        function toggleSidebar() {
            const sidebar =
                document.getElementById("sidebar");

            sidebar.classList.toggle("show");
        }

    </script>


</body>

</html>