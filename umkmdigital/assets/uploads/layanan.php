<?php
session_start();
include '../../koneksi.php';

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

$username_admin = $_SESSION['username'];


/*
|--------------------------------------------------------------------------
| FUNGSI REDIRECT
|--------------------------------------------------------------------------
*/

function redirect_layanan($status)
{
    header("Location: layanan.php?status=" . $status);
    exit;
}


/*
|--------------------------------------------------------------------------
| TAMBAH LAYANAN
|--------------------------------------------------------------------------
*/

if (isset($_POST['tambah_layanan'])) {

    $nama = mysqli_real_escape_string(
        $GLOBALS['koneksi'],
        trim($_POST['nama'])
    );

    $harga = (int) $_POST['harga'];

    $stok = (int) $_POST['stok'];

    $kategori = mysqli_real_escape_string(
        $GLOBALS['koneksi'],
        trim($_POST['kategori'])
    );

    $deskripsi = mysqli_real_escape_string(
        $GLOBALS['koneksi'],
        trim($_POST['deskripsi'])
    );

    $foto = '';


    /*
    |--------------------------------------------------------------
    | UPLOAD FOTO
    |--------------------------------------------------------------
    */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] === UPLOAD_ERR_OK
    ) {

        $folder = 'assets/uploads/layanan/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $nama_file = $_FILES['foto']['name'];
        $tmp_file = $_FILES['foto']['tmp_name'];

        $ext = strtolower(
            pathinfo($nama_file, PATHINFO_EXTENSION)
        );

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $nama_baru =
                'layanan_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $ext;

            $tujuan = $folder . $nama_baru;

            if (move_uploaded_file($tmp_file, $tujuan)) {
                $foto = $nama_baru;
            }
        }
    }


    /*
    |--------------------------------------------------------------
    | INSERT DATABASE
    |--------------------------------------------------------------
    */

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO tb_produk
        (nama, harga, stok, foto, kategori, deskripsi)
        VALUES
        ('$nama', $harga, $stok, '$foto', '$kategori', '$deskripsi')"
    );


    if ($query) {
        redirect_layanan('tambah');
    } else {
        redirect_layanan('gagal');
    }
}


/*
|--------------------------------------------------------------------------
| EDIT LAYANAN
|--------------------------------------------------------------------------
*/

if (isset($_POST['edit_layanan'])) {

    $id = (int) $_POST['id'];

    $nama = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama'])
    );

    $harga = (int) $_POST['harga'];

    $stok = (int) $_POST['stok'];

    $kategori = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['kategori'])
    );

    $deskripsi = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['deskripsi'])
    );


    /*
    |--------------------------------------------------------------
    | AMBIL FOTO LAMA
    |--------------------------------------------------------------
    */

    $query_foto = mysqli_query(
        $koneksi,
        "SELECT foto
         FROM tb_produk
         WHERE id = $id"
    );

    $data_foto = mysqli_fetch_assoc($query_foto);

    $foto = $data_foto['foto'] ?? '';


    /*
    |--------------------------------------------------------------
    | FOTO BARU
    |--------------------------------------------------------------
    */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] === UPLOAD_ERR_OK
    ) {

        $folder = 'assets/uploads/layanan/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $nama_file = $_FILES['foto']['name'];
        $tmp_file = $_FILES['foto']['tmp_name'];

        $ext = strtolower(
            pathinfo($nama_file, PATHINFO_EXTENSION)
        );

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $nama_baru =
                'layanan_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $ext;

            $tujuan = $folder . $nama_baru;

            if (move_uploaded_file($tmp_file, $tujuan)) {

                /*
                | Hapus foto lama
                */

                if (
                    $foto !== '' &&
                    file_exists($folder . $foto)
                ) {
                    unlink($folder . $foto);
                }

                $foto = $nama_baru;
            }
        }
    }


    /*
    |--------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------
    */

    $update = mysqli_query(
        $koneksi,
        "UPDATE tb_produk SET
            nama = '$nama',
            harga = $harga,
            stok = $stok,
            foto = '$foto',
            kategori = '$kategori',
            deskripsi = '$deskripsi'
         WHERE id = $id"
    );


    if ($update) {
        redirect_layanan('edit');
    } else {
        redirect_layanan('gagal');
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS LAYANAN
|--------------------------------------------------------------------------
*/

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    if ($id > 0) {

        /*
        |--------------------------------------------------------------
        | AMBIL FOTO
        |--------------------------------------------------------------
        */

        $query_foto = mysqli_query(
            $koneksi,
            "SELECT foto
             FROM tb_produk
             WHERE id = $id"
        );

        $data_foto = mysqli_fetch_assoc($query_foto);

        $foto = $data_foto['foto'] ?? '';


        /*
        |--------------------------------------------------------------
        | HAPUS DATA
        |--------------------------------------------------------------
        */

        $hapus = mysqli_query(
            $koneksi,
            "DELETE FROM tb_produk
             WHERE id = $id"
        );


        if ($hapus) {

            if (
                $foto !== '' &&
                file_exists(
                    'assets/uploads/layanan/' . $foto
                )
            ) {

                unlink(
                    'assets/uploads/layanan/' . $foto
                );
            }

            redirect_layanan('hapus');

        } else {

            redirect_layanan('gagal');
        }
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$search_safe = mysqli_real_escape_string(
    $koneksi,
    $search
);


/*
|--------------------------------------------------------------------------
| TOTAL LAYANAN
|--------------------------------------------------------------------------
*/

$query_total = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM tb_produk"
);

$data_total = mysqli_fetch_assoc($query_total);

$total_layanan = (int) $data_total['total'];


/*
|--------------------------------------------------------------------------
| TOTAL KATEGORI
|--------------------------------------------------------------------------
*/

$query_kategori = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT kategori) AS total
     FROM tb_produk
     WHERE kategori IS NOT NULL
     AND kategori != ''"
);

$data_kategori = mysqli_fetch_assoc($query_kategori);

$total_kategori = (int) $data_kategori['total'];


/*
|--------------------------------------------------------------------------
| TOTAL STOK
|--------------------------------------------------------------------------
*/

$query_stok = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(stok), 0) AS total
     FROM tb_produk"
);

$data_stok = mysqli_fetch_assoc($query_stok);

$total_stok = (int) $data_stok['total'];


/*
|--------------------------------------------------------------------------
| DATA LAYANAN
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $query_layanan = mysqli_query(
        $koneksi,
        "SELECT *
         FROM tb_produk
         WHERE
            nama LIKE '%$search_safe%'
            OR kategori LIKE '%$search_safe%'
            OR deskripsi LIKE '%$search_safe%'
         ORDER BY id DESC"
    );

} else {

    $query_layanan = mysqli_query(
        $koneksi,
        "SELECT *
         FROM tb_produk
         ORDER BY id DESC"
    );
}

?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Layanan - UMKM Digital</title>

    <link href="../../assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <style>
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
            transition: .3s;
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
           PAGE HEADER
        ===================================================== */

        .page-header {
            display: flex;
            justify-content: space-between;
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

        .stat-box {
            background: #ffffff;
            border: 1px solid #edf0f3;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
        }


        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 11px;
            background: #eaf2ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }


        .stat-label {
            color: #8a9199;
            font-size: 12px;
            margin-top: 12px;
        }


        .stat-number {
            font-size: 25px;
            font-weight: 700;
            margin-top: 3px;
        }


        /* =====================================================
           MAIN CARD
        ===================================================== */

        .service-card {
            background: #ffffff;
            border: 1px solid #edf0f3;
            border-radius: 16px;
            overflow: hidden;
        }


        .service-header {
            padding: 20px 22px;
            border-bottom: 1px solid #edf0f3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }


        .service-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }


        .service-subtitle {
            color: #8a9199;
            font-size: 11px;
            margin-top: 4px;
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .search-box {
            position: relative;
            width: 280px;
        }


        .search-box i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }


        .search-box input {
            width: 100%;
            height: 40px;
            border: 1px solid #dee2e6;
            border-radius: 9px;
            padding: 0 14px 0 38px;
            font-size: 12px;
            outline: none;
        }


        .search-box input:focus {
            border-color: #0d6efd;
            box-shadow:
                0 0 0 3px rgba(13, 110, 253, .08);
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
            letter-spacing: .4px;
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


        /* =====================================================
           SERVICE
        ===================================================== */

        .service-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 250px;
        }


        .service-image {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            object-fit: cover;
            background: #f1f5ff;
            flex-shrink: 0;
        }


        .service-image-empty {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            background: #eaf2ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }


        .service-name {
            font-weight: 600;
            color: #212529;
        }


        .service-description {
            font-size: 11px;
            color: #8a9199;
            margin-top: 3px;
            max-width: 300px;
        }


        .category-badge {
            display: inline-block;
            background: #f1f5ff;
            color: #0d6efd;
            border-radius: 30px;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: 600;
        }


        .price {
            font-weight: 700;
            color: #212529;
            white-space: nowrap;
        }


        .stock {
            font-size: 12px;
            color: #495057;
        }


        .status-badge {
            display: inline-block;
            background: #e9f8ef;
            color: #198754;
            border-radius: 30px;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: 600;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .action-buttons {
            display: flex;
            gap: 6px;
        }


        .action-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .2s;
        }


        .edit-btn {
            background: #eaf2ff;
            color: #0d6efd;
        }


        .edit-btn:hover {
            background: #0d6efd;
            color: white;
        }


        .delete-btn {
            background: #fff1f2;
            color: #dc3545;
        }


        .delete-btn:hover {
            background: #dc3545;
            color: white;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }


        .empty-icon {
            font-size: 50px;
            color: #ced4da;
            margin-bottom: 12px;
        }


        .empty-title {
            font-size: 15px;
            font-weight: 600;
        }


        .empty-text {
            font-size: 12px;
            color: #8a9199;
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
                    10px 0 30px rgba(0, 0, 0, .12);
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


            .service-header {
                flex-direction: column;
                align-items: stretch;
            }


            .search-box {
                width: 100%;
            }


            .page-header {
                align-items: stretch;
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


            .page-header-title {
                font-size: 21px;
            }

        }


        /* =====================================================
           DARK MODE
        ===================================================== */

        body.dark-mode {
            background: #111827;
            color: #f9fafb;
        }


        body.dark-mode .sidebar {
            background: #1f2937;
            border-color: #374151;
        }


        body.dark-mode .topbar {
            background: #1f2937;
            border-color: #374151;
        }


        body.dark-mode .brand-text,
        body.dark-mode .page-title,
        body.dark-mode .page-header-title,
        body.dark-mode .service-title,
        body.dark-mode .stat-number,
        body.dark-mode .service-name,
        body.dark-mode .price {
            color: #f9fafb;
        }


        body.dark-mode .brand-text span,
        body.dark-mode .page-subtitle,
        body.dark-mode .page-header-description,
        body.dark-mode .service-subtitle,
        body.dark-mode .stat-label,
        body.dark-mode .service-description {
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


        body.dark-mode .stat-box,
        body.dark-mode .service-card {
            background: #1f2937;
            border-color: #374151;
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


        body.dark-mode .search-box input {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }


        body.dark-mode .search-box input::placeholder {
            color: #6b7280;
        }


        body.dark-mode .modal-content {
            background: #1f2937;
            color: #f9fafb;
        }


        body.dark-mode .modal-header,
        body.dark-mode .modal-footer {
            border-color: #374151;
        }


        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }


        body.dark-mode .form-label {
            color: #d1d5db;
        }


        body.dark-mode .btn-close {
            filter: invert(1);
        }
    </style>

</head>


<body>


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <img src="../../assets/brand/umkmlogoo.png" class="brand-logo" alt="UMKM Digital">

            <div class="brand-text">

                UMKM Digital

                <span>Admin Panel</span>

            </div>

        </div>


        <div class="sidebar-content">

            <div class="menu-title">
                Menu Utama
            </div>


            <ul class="sidebar-menu">

                <li>
                    <a href="../../dashboard/dashboard.php">

                        <i class="bi bi-grid-1x2-fill"></i>

                        Dashboard

                    </a>
                </li>

                <li>
                    <a href="layanan.php" class="active">

                        <i class="bi bi-briefcase-fill"></i>

                        Layanan

                    </a>
                </li>


                <li>
                    <a href="../../pelanggan.php">

                        <i class="bi bi-people-fill"></i>

                        Pelanggan

                    </a>
                </li>

                <li>
                    <a href="../../transaksi.php">

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

                    <a href="../../dashboard/dashboard.php?logout=true" class="logout-link"
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
                        Layanan
                    </h1>

                    <p class="page-subtitle">
                        Kelola layanan UMKM Digital
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


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div>

                    <h2 class="page-header-title">
                        Kelola Layanan
                    </h2>

                    <p class="page-header-description">
                        Tambahkan dan kelola seluruh layanan UMKM Digital.
                    </p>

                </div>


                <div>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">

                        <i class="bi bi-plus-lg"></i>

                        Tambah Layanan

                    </button>

                </div>

            </div>


            <!-- =================================================
             STATISTIK
        ================================================== -->

            <div class="row g-3 mb-4">


                <div class="col-12 col-md-4">

                    <div class="stat-box">

                        <div class="stat-icon">

                            <i class="bi bi-briefcase-fill"></i>

                        </div>

                        <div class="stat-label">
                            Total Layanan
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_layanan); ?>
                        </div>

                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="stat-box">

                        <div class="stat-icon">

                            <i class="bi bi-grid-fill"></i>

                        </div>

                        <div class="stat-label">
                            Total Kategori
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_kategori); ?>
                        </div>

                    </div>

                </div>


                <div class="col-12 col-md-4">

                    <div class="stat-box">

                        <div class="stat-icon">

                            <i class="bi bi-box-seam-fill"></i>

                        </div>

                        <div class="stat-label">
                            Total Ketersediaan
                        </div>

                        <div class="stat-number">
                            <?= number_format($total_stok); ?>
                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
             TABLE
        ================================================== -->

            <div class="service-card">


                <div class="service-header">

                    <div>

                        <h3 class="service-title">
                            Daftar Layanan
                        </h3>

                        <div class="service-subtitle">
                            Semua layanan yang tersedia di UMKM Digital
                        </div>

                    </div>


                    <!-- SEARCH -->

                    <form method="GET">

                        <div class="search-box">

                            <i class="bi bi-search"></i>

                            <input type="text" name="search" placeholder="Cari layanan..."
                                value="<?= htmlspecialchars($search); ?>">

                        </div>

                    </form>

                </div>


                <?php if (
                    $query_layanan &&
                    mysqli_num_rows($query_layanan) > 0
                ): ?>


                    <div class="table-responsive">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>
                                        Layanan
                                    </th>

                                    <th>
                                        Kategori
                                    </th>

                                    <th>
                                        Harga
                                    </th>

                                    <th>
                                        Ketersediaan
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $layanan =
                                    mysqli_fetch_assoc($query_layanan)
                                ): ?>


                                    <tr>


                                        <!-- LAYANAN -->

                                        <td>

                                            <div class="service-info">


                                                <?php if (
                                                    !empty($layanan['foto']) &&
                                                    file_exists(
                                                        'assets/uploads/layanan/' .
                                                        $layanan['foto']
                                                    )
                                                ): ?>

                                                    <img src="assets/uploads/layanan/<?= htmlspecialchars($layanan['foto']); ?>"
                                                        class="service-image" alt="Foto layanan">

                                                <?php else: ?>

                                                    <div class="service-image-empty">

                                                        <i class="bi bi-briefcase-fill"></i>

                                                    </div>

                                                <?php endif; ?>


                                                <div>

                                                    <div class="service-name">

                                                        <?= htmlspecialchars(
                                                            $layanan['nama'] ?: '-'
                                                        ); ?>

                                                    </div>


                                                    <div class="service-description">

                                                        <?= htmlspecialchars(
                                                            mb_strimwidth(
                                                                $layanan['deskripsi'] ?: '-',
                                                                0,
                                                                70,
                                                                '...'
                                                            )
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- KATEGORI -->

                                        <td>

                                            <span class="category-badge">

                                                <?= htmlspecialchars(
                                                    $layanan['kategori'] ?: '-'
                                                ); ?>

                                            </span>

                                        </td>


                                        <!-- HARGA -->

                                        <td>

                                            <span class="price">

                                                Rp <?= number_format(
                                                    $layanan['harga'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </span>

                                        </td>


                                        <!-- STOK -->

                                        <td>

                                            <span class="stock">

                                                <?= number_format(
                                                    $layanan['stok']
                                                ); ?>

                                            </span>

                                        </td>


                                        <!-- STATUS -->

                                        <!-- STATUS -->

                                        <td>

                                            <?php if ($layanan['ketersediaan'] == 'Tersedia'): ?>

                                                <span class="status-badge">
                                                    Tersedia
                                                </span>

                                            <?php else: ?>

                                                <span class="status-badge" style="
                                                    background:#fff1f2;
                                                    color:#dc3545;
                                                ">
                                                    Habis
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- AKSI -->

                                        <td>

                                            <div class="action-buttons">


                                                <!-- EDIT -->

                                                <button type="button" class="action-btn edit-btn" data-bs-toggle="modal"
                                                    data-bs-target="#editModal<?= $layanan['id']; ?>">

                                                    <i class="bi bi-pencil-fill"></i>

                                                </button>


                                                <!-- DELETE -->

                                                <a href="layanan.php?hapus=<?= $layanan['id']; ?>" class="action-btn delete-btn"
                                                    onclick="return confirm('Yakin ingin menghapus layanan ini?')">

                                                    <i class="bi bi-trash-fill"></i>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>


                                    <!-- =================================================
                                 EDIT MODAL
                            ================================================== -->

                                    <div class="modal fade" id="editModal<?= $layanan['id']; ?>" tabindex="-1">

                                        <div class="modal-dialog modal-dialog-centered modal-lg">

                                            <div class="modal-content">

                                                <form method="POST" enctype="multipart/form-data">

                                                    <div class="modal-header">

                                                        <h5 class="modal-title">
                                                            Edit Layanan
                                                        </h5>

                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>

                                                    </div>


                                                    <div class="modal-body">


                                                        <input type="hidden" name="id" value="<?= $layanan['id']; ?>">


                                                        <div class="row g-3">


                                                            <!-- NAMA -->

                                                            <div class="col-12">

                                                                <label class="form-label">
                                                                    Nama Layanan
                                                                </label>

                                                                <input type="text" name="nama" class="form-control"
                                                                    value="<?= htmlspecialchars($layanan['nama']); ?>" required>

                                                            </div>


                                                            <!-- HARGA -->

                                                            <div class="col-md-6">

                                                                <label class="form-label">
                                                                    Harga
                                                                </label>

                                                                <input type="number" name="harga" class="form-control"
                                                                    value="<?= $layanan['harga']; ?>" min="0" required>

                                                            </div>


                                                            <!-- STOK -->

                                                            <div class="col-md-6">

                                                                <label class="form-label">
                                                                    Ketersediaan
                                                                </label>

                                                                <input type="number" name="stok" class="form-control"
                                                                    value="<?= $layanan['stok']; ?>" min="0" required>

                                                            </div>


                                                            <!-- KATEGORI -->

                                                            <div class="col-12">

                                                                <label class="form-label">
                                                                    Kategori
                                                                </label>

                                                                <input type="text" name="kategori" class="form-control"
                                                                    value="<?= htmlspecialchars($layanan['kategori']); ?>"
                                                                    placeholder="Contoh: Desain Grafis Promosi" required>

                                                            </div>


                                                            <!-- FOTO -->

                                                            <div class="col-12">

                                                                <label class="form-label">
                                                                    Foto Layanan
                                                                </label>

                                                                <input type="file" name="foto" class="form-control"
                                                                    accept=".jpg,.jpeg,.png,.webp">

                                                                <small class="text-secondary">
                                                                    Kosongkan jika tidak ingin mengganti foto.
                                                                </small>

                                                            </div>


                                                            <!-- DESKRIPSI -->

                                                            <div class="col-12">

                                                                <label class="form-label">
                                                                    Deskripsi
                                                                </label>

                                                                <textarea name="deskripsi" class="form-control" rows="4"
                                                                    required><?= htmlspecialchars($layanan['deskripsi']); ?></textarea>

                                                            </div>

                                                        </div>

                                                    </div>


                                                    <div class="modal-footer">

                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                            Batal
                                                        </button>


                                                        <button type="submit" name="edit_layanan" class="btn btn-primary">

                                                            <i class="bi bi-check-lg"></i>

                                                            Simpan Perubahan

                                                        </button>

                                                    </div>

                                                </form>

                                            </div>

                                        </div>

                                    </div>


                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">

                            <i class="bi bi-briefcase"></i>

                        </div>


                        <div class="empty-title">

                            <?php if ($search !== ''): ?>

                                Layanan tidak ditemukan

                            <?php else: ?>

                                Belum ada layanan

                            <?php endif; ?>

                        </div>


                        <p class="empty-text">

                            <?php if ($search !== ''): ?>

                                Tidak ada layanan yang cocok dengan pencarian
                                "<?= htmlspecialchars($search); ?>".

                            <?php else: ?>

                                Tambahkan layanan pertama untuk mulai mengelola jasa.

                            <?php endif; ?>

                        </p>

                    </div>


                <?php endif; ?>


            </div>


            <!-- FOOTER -->

            <div class="text-center mt-4">

                <small class="text-secondary">

                    © 2026 UMKM Digital · Admin Panel

                </small>

            </div>


        </main>

    </div>


    <!-- =========================================================
     TAMBAH MODAL
========================================================= -->

    <div class="modal fade" id="tambahModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

                <form method="POST" enctype="multipart/form-data">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Tambah Layanan
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>


                    <div class="modal-body">

                        <div class="row g-3">


                            <!-- NAMA -->

                            <div class="col-12">

                                <label class="form-label">
                                    Nama Layanan
                                </label>

                                <input type="text" name="nama" class="form-control"
                                    placeholder="Contoh: Jasa Desain Grafis Promosi" required>

                            </div>


                            <!-- HARGA -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Harga
                                </label>

                                <input type="number" name="harga" class="form-control" placeholder="50000" min="0"
                                    required>

                            </div>


                            <!-- STOK -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Ketersediaan
                                </label>

                                <input type="number" name="stok" class="form-control" value="1" min="0" required>

                            </div>


                            <!-- KATEGORI -->

                            <div class="col-12">

                                <label class="form-label">
                                    Kategori
                                </label>

                                <select name="kategori" class="form-select" required>

                                    <option value="">
                                        -- Pilih Kategori --
                                    </option>

                                    <option value="Desain Grafis Promosi">
                                        Desain Grafis Promosi
                                    </option>

                                    <option value="Edit Video Pendek">
                                        Edit Video Pendek
                                    </option>

                                    <option value="Pembuatan Website">
                                        Pembuatan Website
                                    </option>

                                </select>

                            </div>


                            <!-- FOTO -->

                            <div class="col-12">

                                <label class="form-label">
                                    Foto Layanan
                                </label>

                                <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp">

                                <small class="text-secondary">
                                    Format: JPG, JPEG, PNG, WEBP.
                                </small>

                            </div>


                            <!-- DESKRIPSI -->

                            <div class="col-12">

                                <label class="form-label">
                                    Deskripsi
                                </label>

                                <textarea name="deskripsi" class="form-control" rows="4"
                                    placeholder="Masukkan deskripsi layanan..." required></textarea>

                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Batal
                        </button>


                        <button type="submit" name="tambah_layanan" class="btn btn-primary">

                            <i class="bi bi-plus-lg"></i>

                            Tambah Layanan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script src="../../assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../../assets/js/theme.js"></script>


    <script>

        function toggleSidebar() {

            const sidebar =
                document.getElementById("sidebar");

            sidebar.classList.toggle("show");

        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE SIDEBAR
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