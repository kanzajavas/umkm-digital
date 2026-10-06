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

if (
    !isset($_SESSION['keranjang']) ||
    empty($_SESSION['keranjang'])
) {

    header("Location: keranjang.php");
    exit;

}


// =========================
// AMBIL USER
// =========================

$username = mysqli_real_escape_string(
    $koneksi,
    $_SESSION['username']
);

$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_user
     WHERE username = '$username'"
);

if (mysqli_num_rows($queryUser) == 0) {

    echo "<script>
            alert('Data pelanggan tidak ditemukan!');
            window.location='france.php';
          </script>";

    exit;

}

$user = mysqli_fetch_assoc($queryUser);

$id_pelanggan = $user['id'];


// =========================
// AMBIL DATA CHECKOUT
// =========================

$pembayaran = isset($_POST['pembayaran'])
    ? $_POST['pembayaran']
    : 'Transfer Bank';

$pengiriman = isset($_POST['pengiriman'])
    ? $_POST['pengiriman']
    : 'Digital / Online';


// =========================
// AMBIL KERANJANG
// =========================

$keranjang = $_SESSION['keranjang'];


// =========================
// HITUNG TOTAL
// =========================

$total_harga = 0;

foreach ($keranjang as $item) {

    $total_harga +=
        $item['harga'] * $item['jumlah'];

}


// =========================
// TANGGAL TRANSAKSI
// =========================

$tanggal = date('Y-m-d');


// =========================
// SIMPAN TRANSAKSI
// =========================

$queryTransaksi = mysqli_query(
    $koneksi,
    "INSERT INTO tb_transaksi
    (
        id_pelanggan,
        tanggal,
        total_harga
    )
    VALUES
    (
        $id_pelanggan,
        '$tanggal',
        $total_harga
    )"
);


// =========================
// CEK TRANSAKSI
// =========================

if (!$queryTransaksi) {

    die(
        "Gagal menyimpan transaksi: "
        . mysqli_error($koneksi)
    );

}


// =========================
// AMBIL ID TRANSAKSI
// =========================

$id_transaksi = mysqli_insert_id($koneksi);


// =========================
// SIMPAN DETAIL TRANSAKSI
// =========================

$berhasil = true;

foreach ($keranjang as $item) {

    $id_produk = (int) $item['id'];
    $jumlah = (int) $item['jumlah'];
    $harga_satuan = (int) $item['harga'];

    $queryDetail = mysqli_query(
        $koneksi,
        "INSERT INTO tb_detail
        (
            id_transaksi,
            id_produk,
            jumlah,
            harga_satuan
        )
        VALUES
        (
            $id_transaksi,
            $id_produk,
            $jumlah,
            $harga_satuan
        )"
    );


    if (!$queryDetail) {

        $berhasil = false;
        break;

    }

}


// =========================
// CEK DETAIL
// =========================

if (!$berhasil) {

    echo "<script>

            alert('Detail transaksi gagal disimpan!');

            window.location='keranjang.php';

          </script>";

    exit;

}


// =========================
// SIMPAN INFORMASI CHECKOUT
// =========================

$_SESSION['checkout'] = [

    'id_transaksi' => $id_transaksi,

    'pembayaran' => $pembayaran,

    'pengiriman' => $pengiriman

];


// =========================
// KOSONGKAN KERANJANG
// =========================

unset($_SESSION['keranjang']);


// =========================
// KE INVOICE
// =========================

header(
    "Location: invoice.php?id=$id_transaksi"
);

exit;

?>