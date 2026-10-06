<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


/* =========================
   CEK DATA
========================= */

if (
    !isset($_POST['id_produk']) ||
    !isset($_POST['jumlah'])
) {
    header("Location: keranjang.php");
    exit;
}


$id = (int) $_POST['id_produk'];
$jumlah = (int) $_POST['jumlah'];


/* =========================
   VALIDASI JUMLAH
========================= */

if ($jumlah < 1) {
    $jumlah = 1;
}


/* =========================
   UPDATE KERANJANG
========================= */

if (isset($_SESSION['keranjang'][$id])) {

    $_SESSION['keranjang'][$id]['jumlah'] = $jumlah;

}


/* =========================
   KEMBALI
========================= */

header("Location: keranjang.php");
exit;

?>