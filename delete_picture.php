<?php
include('config/conn.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Change MySQL query to PostgreSQL query
$sql = "UPDATE users SET profile_picture = $1 WHERE user_id = $2";
$stmt = pg_prepare($con, "update_profile_picture", $sql);
$result = pg_execute($con, "update_profile_picture", array('uploads/default_profile_picture.jpg', $user_id));

if ($result) {
    header('Location: profile.php?message=profile_picture_deleted');
} else {
    header('Location: profile.php?message=error_deleting_picture');
}
exit;
?>
