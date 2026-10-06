<?php
session_start();


// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}


// Kalau keranjang belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


// Cek ID produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: keranjang.php");
    exit;
}


$id = (int) $_GET['id'];


// Hapus produk dari keranjang
if (isset($_SESSION['keranjang'][$id])) {

    unset($_SESSION['keranjang'][$id]);

}


// Kembali ke keranjang
header("Location: keranjang.php");
exit;

?>