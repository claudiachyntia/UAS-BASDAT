<?php
include('config/conn.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "UPDATE users SET profile_picture = 'uploads/default_profile_picture.jpg' WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header('Location: profile.php?message=profile_picture_deleted');
} else {
    header('Location: profile.php?message=error_deleting_picture');
}
exit;
?>
