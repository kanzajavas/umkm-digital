<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CEK ID
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: france.php");
    exit;
}

$id = (int) $_GET['id'];


/* =========================
   JUMLAH
========================= */

$jumlah = 1;

if (isset($_GET['jumlah']) && is_numeric($_GET['jumlah'])) {
    $jumlah = (int) $_GET['jumlah'];
}

if ($jumlah < 1) {
    $jumlah = 1;
}


/* =========================
   AMBIL PRODUK
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tb_produk WHERE id = $id"
);

if (mysqli_num_rows($query) == 0) {

    echo "<script>
            alert('Produk/jasa tidak ditemukan!');
            window.location='france.php';
          </script>";

    exit;
}

$produk = mysqli_fetch_assoc($query);


/* =========================
   CEK KETERSEDIAAN
========================= */

if ($produk['ketersediaan'] == 'Habis') {

    echo "<script>
            alert('Produk/jasa sedang tidak tersedia!');
            history.back();
          </script>";

    exit;
}


/* =========================
   BUAT KERANJANG
========================= */

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


/* =========================
   TAMBAH / UPDATE
========================= */

if (isset($_SESSION['keranjang'][$id])) {

    $_SESSION['keranjang'][$id]['jumlah'] += $jumlah;

} else {

    $_SESSION['keranjang'][$id] = [

        'id' => $produk['id'],

        'nama' => $produk['nama'],

        'harga' => $produk['harga'],

        'foto' => $produk['foto'],

        'kategori'  => $produk['kategori'],

        'jumlah' => $jumlah

    ];
}


/* =========================
   KEMBALI
========================= */

header("Location: keranjang.php");
exit;

?>