<?php
session_start();
include('config/conn.php');

if (!isset($_SESSION['user_id'])) {
    echo 'not_logged_in';
    exit();
}

$userId = $_SESSION['user_id'];
$tourismId = $_POST['tourism_id'];
$action = $_POST['action'];

if ($action === 'save') {
    // Simpan data ke wishlist
    $query = "INSERT INTO wishlist (user_id, tourism_id) VALUES ($userId, $tourismId)";
    if (pg_query($con, $query)) {
        echo 'saved';
    } else {
        echo 'error';
    }
} elseif ($action === 'unsave') {
    // Hapus data dari wishlist
    $query = "DELETE FROM wishlist WHERE user_id = $userId AND tourism_id = $tourismId";
    if (pg_query($con, $query)) {
        echo 'unsaved';
    } else {
        echo 'error';
    }
}
?>
