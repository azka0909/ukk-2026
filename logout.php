<?php
session_start(); // Memulai session

// Menghapus semua data session
session_unset();
session_destroy();

// Mengarahkan kembali ke halaman pilihan utama (index.php)
header("Location: index.php");
exit();
?>