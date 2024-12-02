<?php
session_start();
include('config/conn.php');
$source_page = $_GET['source_page'];

if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    die('Invalid post_id.');
}
if (!isset($_SESSION['user_id'])) {
    die('Session user_id is missing.');
}
if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    echo 'error';
    exit;
}

$post_id = (int)$_GET['post_id'];

$sql = "DELETE FROM posts WHERE id = $1 AND user_id = $2";
$result = pg_query_params($con, $sql, array($post_id, $_SESSION['user_id']));

if ($result) {
    if (isset($source_page)) {
        if ($source_page === 'profile') {
            header('Location: profile.php');
        } elseif ($source_page === 'community') {
            header('Location: community.php');
        } else {
            header('Location: index.php'); 
        }
    } else {
        header('Location: index.php');
    }
} else {
    echo "Error: " . pg_last_error($con);
}

exit;
?>