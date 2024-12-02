<?php
session_start();
include('config/conn.php');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Jika form dikirim, proses konfirmasi password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    // Query untuk mendapatkan password yang terenkripsi dari database
    $query = "SELECT password_hash FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    // Verifikasi password
    if (password_verify($password, $password_hash)) {
        // Jika password cocok, hapus akun
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $delete_stmt = $con->prepare($delete_query);
        $delete_stmt->bind_param("i", $user_id);
        if ($delete_stmt->execute()) {
            // Hapus session dan redirect ke halaman login
            session_destroy();
            header("Location: index.php");
            exit();
        } else {
            header("Location: profile.php");
        }
    } else {
        header("Location: profile.php");
    }
}
?>
