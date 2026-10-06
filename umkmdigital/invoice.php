<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ID TRANSAKSI
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: france.php");
    exit;
}

$id_transaksi = (int) $_GET['id'];

/* =========================
   AMBIL DATA TRANSAKSI
========================= */
$queryTransaksi = mysqli_query(
    $koneksi,
    "SELECT 
        tb_transaksi.*,
        tb_user.nama,
        tb_user.email,
        tb_user.hp,
        tb_user.alamat,
        tb_user.username
     FROM tb_transaksi
     JOIN tb_user 
        ON tb_transaksi.id_pelanggan = tb_user.id
     WHERE tb_transaksi.id_transaksi = $id_transaksi"
);

if (mysqli_num_rows($queryTransaksi) == 0) {
    echo "<script>
            alert('Transaksi tidak ditemukan!');
            window.location='france.php';
          </script>";
    exit;
}

$transaksi = mysqli_fetch_assoc($queryTransaksi);

/* =========================
   CEK HAK AKSES
   ADMIN BOLEH LIHAT SEMUA
   PELANGGAN HANYA TRANSAKSINYA
========================= */
if (
    $_SESSION['role'] !== 'admin' &&
    $transaksi['username'] !== $_SESSION['username']
) {
    echo "<script>
            alert('Anda tidak memiliki akses ke invoice ini!');
            window.location='france.php';
          </script>";
    exit;
}

/* =========================
   AMBIL DETAIL TRANSAKSI
========================= */
$queryDetail = mysqli_query(
    $koneksi,
    "SELECT 
        tb_detail.*,
        tb_produk.nama,
        tb_produk.kategori
     FROM tb_detail
     JOIN tb_produk
        ON tb_detail.id_produk = tb_produk.id
     WHERE tb_detail.id_transaksi = $id_transaksi"
);

if (!$queryDetail) {
    die("Gagal mengambil detail transaksi: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoice #<?= $transaksi['id_transaksi']; ?></title>

    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="/assets/dist/css/theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f5f7fb;
            color: #212529;
        }

        .invoice-wrapper {
            max-width: 900px;
            margin: 40px auto;
        }

        .invoice-card {
            background: white;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 35px;
        }

        .brand {
            font-size: 26px;
            font-weight: 700;
            color: #0d6efd;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .invoice-number {
            color: #6c757d;
        }

        .customer-box {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .customer-box h5 {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .customer-data {
            margin-bottom: 6px;
        }

        .table {
            vertical-align: middle;
        }

        .table thead th {
            background: #f1f3f5;
            border: none;
            padding: 14px;
        }

        .table tbody td {
            padding: 14px;
        }

        .total-box {
            max-width: 350px;
            margin-left: auto;
            margin-top: 25px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .grand-total {
            border-top: 2px solid #dee2e6;
            margin-top: 8px;
            padding-top: 15px;
            font-size: 21px;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            background: #e7f1ff;
            color: #0d6efd;
            padding: 7px 14px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
        }

        .invoice-footer {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
        }

        .action-buttons {
            max-width: 900px;
            margin: 0 auto 30px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        /* DARK MODE */

        body.dark-mode {
            background: #121212;
            color: #f1f1f1;
        }

        body.dark-mode .invoice-card {
            background: #1e1e1e;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .customer-box {
            background: #292929;
        }

        body.dark-mode .table {
            color: #f1f1f1;
        }

        body.dark-mode .table thead th {
            background: #292929;
        }

        body.dark-mode .invoice-number,
        body.dark-mode .invoice-footer {
            color: #aaa;
        }

        body.dark-mode .grand-total {
            border-color: #444;
        }

        /* PRINT */

        @media print {

            body {
                background: white !important;
                color: black !important;
            }

            .action-buttons {
                display: none !important;
            }

            .invoice-wrapper {
                margin: 0;
                max-width: 100%;
            }

            .invoice-card {
                box-shadow: none !important;
                border-radius: 0;
                padding: 20px;
            }

            .customer-box {
                background: #f8f9fa !important;
            }

            .table thead th {
                background: #f1f3f5 !important;
            }

            .invoice-footer {
                color: #666 !important;
            }
        }

        @media (max-width: 768px) {

            .invoice-card {
                padding: 20px;
            }

            .invoice-header {
                flex-direction: column;
            }

            .invoice-title {
                text-align: left;
            }

            .action-buttons {
                flex-direction: column;
            }

            .table {
                font-size: 14px;
            }

        }
    </style>

</head>

<body>

    <div class="container">

        <!-- BUTTON -->

        <div class="action-buttons mt-4">

            <a href="france.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>

            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i>
                Cetak Invoice
            </button>

        </div>


        <!-- INVOICE -->

        <div class="invoice-wrapper">

            <div class="invoice-card">

                <!-- HEADER -->

                <div class="invoice-header">

                    <div>

                        <div class="brand">
                            UMKM Digital
                        </div>

                        <div class="text-secondary">
                            Jasa Keterampilan Digital
                        </div>

                    </div>


                    <div class="invoice-title">

                        <h1>INVOICE</h1>

                        <div class="invoice-number">
                            #<?= str_pad($transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT); ?>
                        </div>

                        <div class="mt-2">
                            <span class="status">
                                Pesanan Berhasil
                            </span>
                        </div>

                    </div>

                </div>


                <!-- CUSTOMER -->

                <div class="customer-box">

                    <h5>
                        <i class="bi bi-person-circle"></i>
                        Data Pelanggan
                    </h5>

                    <div class="customer-data">
                        <strong>Nama:</strong>
                        <?= htmlspecialchars($transaksi['nama']); ?>
                    </div>

                    <div class="customer-data">
                        <strong>Email:</strong>
                        <?= htmlspecialchars($transaksi['email']); ?>
                    </div>

                    <div class="customer-data">
                        <strong>No. HP:</strong>
                        <?= htmlspecialchars($transaksi['hp']); ?>
                    </div>

                    <div class="customer-data">
                        <strong>Alamat:</strong>
                        <?= htmlspecialchars($transaksi['alamat']); ?>
                    </div>

                    <div class="customer-data mt-3">

                        <strong>Tanggal Transaksi:</strong>

                        <?= date(
                            'd F Y',
                            strtotime($transaksi['tanggal'])
                        ); ?>

                    </div>

                </div>


                <!-- DETAIL PESANAN -->

                <h5 class="fw-bold mb-3">
                    Detail Pesanan
                </h5>

                <div class="table-responsive">

                    <table class="table">

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Layanan</th>

                                <th>Kategori</th>

                                <th class="text-center">
                                    Jumlah
                                </th>

                                <th class="text-end">
                                    Harga
                                </th>

                                <th class="text-end">
                                    Subtotal
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $no = 1;
                            $totalDetail = 0;

                            while ($detail = mysqli_fetch_assoc($queryDetail)):

                                $subtotal =
                                    $detail['jumlah'] *
                                    $detail['harga_satuan'];

                                $totalDetail += $subtotal;

                                ?>

                                <tr>

                                    <td>
                                        <?= $no++; ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $detail['nama']
                                            ); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $detail['kategori']
                                        ); ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $detail['jumlah']; ?>
                                    </td>

                                    <td class="text-end">
                                        Rp
                                        <?= number_format(
                                            $detail['harga_satuan'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>
                                    </td>

                                    <td class="text-end">

                                        Rp
                                        <?= number_format(
                                            $subtotal,
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>


                <!-- TOTAL -->

                <div class="total-box">

                    <div class="total-row">

                        <span>
                            Total Item
                        </span>

                        <strong>
                            <?= $no - 1; ?> jenis
                        </strong>

                    </div>

                    <div class="total-row">

                        <span>
                            Total Harga
                        </span>

                        <strong>
                            Rp
                            <?= number_format(
                                $totalDetail,
                                0,
                                ',',
                                '.'
                            ); ?>
                        </strong>

                    </div>

                    <div class="total-row grand-total">

                        <span>
                            TOTAL
                        </span>

                        <span>
                            Rp
                            <?= number_format(
                                $transaksi['total_harga'],
                                0,
                                ',',
                                '.'
                            ); ?>
                        </span>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="invoice-footer">

                    <strong>Terima kasih telah menggunakan UMKM Digital.</strong>

                    <br>

                    Invoice ini dibuat secara otomatis oleh sistem.

                </div>

            </div>

        </div>

    </div>


    <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

</body>

</html>