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

$username_session = $_SESSION['username'];

/* =========================
   PROSES UPDATE PROFILE
========================= */
if (isset($_POST['update_profile'])) {

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $hp = mysqli_real_escape_string($koneksi, $_POST['hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $update = mysqli_query(
        $koneksi,
        "UPDATE tb_user SET
            nama = '$nama',
            email = '$email',
            hp = '$hp',
            alamat = '$alamat'
         WHERE username = '$username_session'"
    );

    if ($update) {
        echo "<script>
                alert('Profile berhasil diperbarui!');
                window.location='profile.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Profile gagal diperbarui!');
              </script>";
    }
}

/* =========================
   AMBIL DATA USER
========================= */
$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_user
     WHERE username = '$username_session'"
);

if (mysqli_num_rows($queryUser) == 0) {
    echo "<script>
            alert('Data user tidak ditemukan!');
            window.location='france.php';
          </script>";
    exit;
}

$user = mysqli_fetch_assoc($queryUser);

/* =========================
   HITUNG JUMLAH TRANSAKSI
========================= */
$id_user = $user['id'];

$queryJumlahTransaksi = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM tb_transaksi
     WHERE id_pelanggan = $id_user"
);

$dataJumlah = mysqli_fetch_assoc($queryJumlahTransaksi);
$jumlahTransaksi = $dataJumlah['jumlah'];

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profile - UMKM Digital</title>

    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            color: #172033;
            font-family: Arial, sans-serif;
            transition: 0.3s;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e9edf3;
            padding: 14px 0;
        }

        .navbar-brand {
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            font-size: 20px;
        }

        .nav-link {
            color: #596579;
            font-weight: 500;
            margin: 0 3px;
            padding: 8px 12px !important;
            border-radius: 9px;
            transition: 0.2s;
        }

        .nav-link:hover {
            background: #f1f5fb;
            color: #0d6efd;
        }

        .nav-link.active {
            background: #edf4ff;
            color: #0d6efd !important;
            font-weight: 600;
        }

        /* =========================
           PAGE
        ========================= */

        .profile-page {
            max-width: 1100px;
            margin: 45px auto 70px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading small {
            color: #0d6efd;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 12px;
        }

        .page-heading h1 {
            margin: 7px 0 5px;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .page-heading p {
            margin: 0;
            color: #718096;
        }

        /* =========================
           PROFILE HERO
        ========================= */

        .profile-hero {
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 22px;
            border: 1px solid #e9edf3;
            box-shadow: 0 12px 35px rgba(30, 50, 80, 0.06);
        }

        .profile-hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: #edf4ff;
            right: -100px;
            top: -130px;
        }

        .profile-hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .profile-identity {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            min-width: 90px;
            border-radius: 22px;
            background: linear-gradient(135deg, #0d6efd, #4d9aff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.25);
        }

        .profile-name {
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -0.7px;
        }

        .profile-username {
            color: #718096;
            margin-top: 3px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eaf7ef;
            color: #198754;
            padding: 7px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 10px;
        }

        .profile-badge span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #20c997;
        }

        /* =========================
           STATS
        ========================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            background: white;
            border: 1px solid #e9edf3;
            border-radius: 18px;
            padding: 21px;
            box-shadow: 0 8px 25px rgba(30, 50, 80, 0.04);
            transition: 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 13px 30px rgba(30, 50, 80, 0.08);
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 43px;
            height: 43px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf4ff;
            color: #0d6efd;
            font-size: 20px;
        }

        .stat-label {
            color: #718096;
            font-size: 13px;
            margin-top: 17px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            margin-top: 2px;
        }

        /* =========================
           MAIN CONTENT
        ========================= */

        .content-grid {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 22px;
        }

        .content-card {
            background: white;
            border: 1px solid #e9edf3;
            border-radius: 20px;
            padding: 27px;
            box-shadow: 0 8px 25px rgba(30, 50, 80, 0.04);
        }

        .card-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .heading-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf4ff;
            color: #0d6efd;
            font-size: 18px;
        }

        .card-heading h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 750;
        }

        .card-heading p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #8a94a6;
        }

        /* =========================
           FORM
        ========================= */

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #465267;
            margin-bottom: 7px;
        }

        .form-control {
            border: 1px solid #dfe5ed;
            border-radius: 11px;
            padding: 11px 13px;
            min-height: 45px;
            font-size: 14px;
            background: #fbfcfe;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: #86b7fe;
            background: white;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .readonly-input {
            background: #f1f3f6 !important;
            color: #7b8494;
        }

        .save-btn {
            border: none;
            border-radius: 11px;
            padding: 11px 18px;
            font-weight: 650;
            box-shadow: 0 7px 18px rgba(13, 110, 253, 0.18);
        }

        /* =========================
           SIDE CARD
        ========================= */

        .quick-card {
            background: #0d6efd;
            color: white;
            border-radius: 20px;
            padding: 27px;
            position: relative;
            overflow: hidden;
            min-height: 100%;
        }

        .quick-card::before {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border: 35px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            right: -70px;
            bottom: -80px;
        }

        .quick-card h3 {
            position: relative;
            z-index: 2;
            font-size: 19px;
            font-weight: 750;
            margin-bottom: 8px;
        }

        .quick-card p {
            position: relative;
            z-index: 2;
            color: rgba(255, 255, 255, 0.78);
            font-size: 13px;
            line-height: 1.6;
        }

        .quick-item {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 22px;
        }

        .quick-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.13);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quick-text strong {
            display: block;
            font-size: 13px;
        }

        .quick-text span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.68);
        }

        .history-btn {
            position: relative;
            z-index: 2;
            width: 100%;
            margin-top: 27px;
            border-radius: 11px;
            padding: 11px;
            background: white;
            color: #0d6efd;
            border: none;
            font-weight: 700;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: 0.2s;
        }

        .history-btn:hover {
            background: #f2f6ff;
            color: #0a58ca;
        }

        /* =========================
           DARK MODE
        ========================= */

        body.dark-mode {
            background: #111827;
            color: #f1f5f9;
        }

        body.dark-mode .navbar {
            background: rgba(17, 24, 39, 0.94);
            border-color: #273244;
        }

        body.dark-mode .nav-link {
            color: #aeb8c7;
        }

        body.dark-mode .nav-link:hover {
            background: #1d2939;
            color: #70a9ff;
        }

        body.dark-mode .nav-link.active {
            background: #1b3154;
            color: #70a9ff !important;
        }

        body.dark-mode .profile-hero,
        body.dark-mode .stat-card,
        body.dark-mode .content-card {
            background: #1a2332;
            border-color: #293548;
            box-shadow: none;
        }

        body.dark-mode .profile-hero::after {
            background: #20324e;
        }

        body.dark-mode .profile-username,
        body.dark-mode .page-heading p,
        body.dark-mode .stat-label,
        body.dark-mode .card-heading p {
            color: #9aa7b8;
        }

        body.dark-mode .stat-icon,
        body.dark-mode .heading-icon {
            background: #203653;
            color: #70a9ff;
        }

        body.dark-mode .form-label {
            color: #c6cfdb;
        }

        body.dark-mode .form-control {
            background: #111827;
            color: #f1f5f9;
            border-color: #344154;
        }

        body.dark-mode .form-control:focus {
            background: #111827;
            color: white;
        }

        body.dark-mode .readonly-input {
            background: #252f3e !important;
            color: #8793a5;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .content-grid {
                grid-template-columns: 1fr;
            }

            .quick-card {
                min-height: auto;
            }

        }

        @media (max-width: 700px) {

            .profile-page {
                margin: 30px auto 50px;
            }

            .page-heading h1 {
                font-size: 28px;
            }

            .profile-hero {
                padding: 24px;
            }

            .profile-hero-content {
                align-items: flex-start;
            }

            .profile-identity {
                align-items: flex-start;
            }

            .profile-avatar {
                width: 70px;
                height: 70px;
                min-width: 70px;
                border-radius: 18px;
                font-size: 29px;
            }

            .profile-name {
                font-size: 22px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-card {
                padding: 21px;
            }

        }

        @media (max-width: 500px) {

            .profile-identity {
                gap: 13px;
            }

            .profile-avatar {
                width: 60px;
                height: 60px;
                min-width: 60px;
                font-size: 24px;
            }

            .profile-name {
                font-size: 19px;
            }

            .profile-username {
                font-size: 12px;
            }

            .profile-hero {
                padding: 20px;
            }

        }
    </style>

</head>

<body>


    <!-- =========================
     NAVBAR
========================= -->

    <nav class="navbar navbar-expand-lg">

        <div class="container">

            <a class="navbar-brand text-primary" href="france.php">

                <i class="bi bi-grid-1x2-fill"></i>
                UMKM Digital

            </a>


            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">

                <span class="navbar-toggler-icon"></span>

            </button>


            <div class="collapse navbar-collapse" id="navbarMenu">

                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">

                        <a class="nav-link" href="france.php">

                            <i class="bi bi-house"></i>
                            Home

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" href="france.php">

                            <i class="bi bi-grid"></i>
                            Layanan

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" href="keranjang.php">

                            <i class="bi bi-cart3"></i>
                            Keranjang

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link active" href="profile.php">

                            <i class="bi bi-person-circle"></i>
                            Profile

                        </a>

                    </li>

                    <li class="nav-item ms-lg-2">

                        <button id="themeToggle" class="btn btn-outline-secondary btn-sm">

                            <i id="themeIcon" class="bi bi-moon-fill"></i>

                        </button>

                    </li>

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


    <!-- =========================
     CONTENT
========================= -->

    <div class="container">

        <div class="profile-page">


            <!-- PAGE HEADING -->

            <div class="page-heading">

                <small>Account Center</small>

                <h1>Profile Saya</h1>

                <p>
                    Kelola informasi akun dan lihat aktivitas transaksi kamu.
                </p>

            </div>


            <!-- PROFILE HERO -->

            <div class="profile-hero">

                <div class="profile-hero-content">

                    <div class="profile-identity">

                        <div class="profile-avatar">

                            <i class="bi bi-person-fill"></i>

                        </div>

                        <div>

                            <div class="profile-name">

                                <?= htmlspecialchars($user['nama']); ?>

                            </div>

                            <div class="profile-username">

                                @<?= htmlspecialchars($user['username']); ?>

                            </div>

                            <div class="profile-badge">

                                <span></span>

                                Akun Aktif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Total Transaksi
                            </div>

                            <div class="stat-value">
                                <?= $jumlahTransaksi; ?>
                            </div>

                        </div>

                        <div class="stat-icon">

                            <i class="bi bi-receipt"></i>

                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Status Akun
                            </div>

                            <div class="stat-value">
                                Aktif
                            </div>

                        </div>

                        <div class="stat-icon">

                            <i class="bi bi-person-check"></i>

                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div>

                            <div class="stat-label">
                                Role
                            </div>

                            <div class="stat-value">
                                <?= ucfirst($user['role']); ?>
                            </div>

                        </div>

                        <div class="stat-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>

                    </div>

                </div>

            </div>


            <!-- MAIN CONTENT -->

            <div class="content-grid">


                <!-- EDIT PROFILE -->

                <div class="content-card">

                    <div class="card-heading">

                        <div class="heading-icon">

                            <i class="bi bi-person-lines-fill"></i>

                        </div>

                        <div>

                            <h3>Informasi Pribadi</h3>

                            <p>
                                Pastikan informasi akun kamu selalu terbaru.
                            </p>

                        </div>

                    </div>


                    <form method="POST">


                        <div class="row g-3">


                            <!-- NAMA -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Nama Lengkap
                                </label>

                                <input type="text" name="nama" class="form-control"
                                    value="<?= htmlspecialchars($user['nama']); ?>" required>

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($user['email']); ?>" required>

                            </div>


                            <!-- USERNAME -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Username
                                </label>

                                <input type="text" class="form-control readonly-input"
                                    value="<?= htmlspecialchars($user['username']); ?>" readonly>

                            </div>


                            <!-- HP -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    No. HP
                                </label>

                                <input type="text" name="hp" class="form-control"
                                    value="<?= htmlspecialchars($user['hp']); ?>">

                            </div>


                            <!-- ALAMAT -->

                            <div class="col-12">

                                <label class="form-label">
                                    Alamat
                                </label>

                                <textarea name="alamat" class="form-control"
                                    rows="3"><?= htmlspecialchars($user['alamat']); ?></textarea>

                            </div>


                            <!-- BUTTON -->

                            <div class="col-12 pt-2">

                                <button type="submit" name="update_profile" class="btn btn-primary save-btn">

                                    <i class="bi bi-check2-circle"></i>

                                    Simpan Perubahan

                                </button>

                            </div>

                        </div>

                    </form>

                </div>


                <!-- QUICK ACCESS -->

                <div class="quick-card">

                    <h3>
                        Aktivitas Akun
                    </h3>

                    <p>
                        Semua aktivitas pembelian dan layanan kamu
                        tersimpan dengan aman di akun ini.
                    </p>


                    <div class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-receipt"></i>

                        </div>

                        <div class="quick-text">

                            <strong>
                                <?= $jumlahTransaksi; ?> Transaksi
                            </strong>

                            <span>
                                Total transaksi yang pernah dibuat
                            </span>

                        </div>

                    </div>


                    <div class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-file-earmark-text"></i>

                        </div>

                        <div class="quick-text">

                            <strong>
                                Invoice
                            </strong>

                            <span>
                                Lihat invoice dari transaksi kamu
                            </span>

                        </div>

                    </div>


                    <div class="quick-item">

                        <div class="quick-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>

                        <div class="quick-text">

                            <strong>
                                Akun Terlindungi
                            </strong>

                            <span>
                                Data akun tersimpan di sistem
                            </span>

                        </div>

                    </div>


                    <a href="riwayat_transaksi.php" class="history-btn">

                        <i class="bi bi-clock-history"></i>

                        Lihat Riwayat Transaksi

                    </a>

                </div>

            </div>

        </div>

    </div>


    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

</body>

</html>