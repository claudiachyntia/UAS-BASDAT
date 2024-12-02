<?php
session_start();
include 'config/conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$content = $_POST['content'];
$image = NULL;

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "uploads/";
    $image = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
}

$query = "INSERT INTO posts (user_id, content, image) VALUES ('$user_id', '$content', '$image')";
mysqli_query($con, $query);

header("Location: community.php");
exit();
?>
