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
    $query = "SELECT password_hash FROM users WHERE user_id = $1";
    $stmt = pg_prepare($con, "get_password_hash", $query);
    $result = pg_execute($con, "get_password_hash", array($user_id));

    if ($result) {
        $row = pg_fetch_assoc($result);
        $password_hash = $row['password_hash'];

        // Verifikasi password
        if (password_verify($password, $password_hash)) {
            // Jika password cocok, hapus akun
            $delete_query = "DELETE FROM users WHERE user_id = $1";
            $delete_stmt = pg_prepare($con, "delete_user", $delete_query);
            $delete_result = pg_execute($con, "delete_user", array($user_id));
            
            if ($delete_result) {
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
    } else {
        header("Location: profile.php");
    }
}
?>