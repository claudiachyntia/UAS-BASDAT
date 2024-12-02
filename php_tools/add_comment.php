<?php
session_start();
include('../config/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source_page = $_GET['source_page'];
    $postId = $_POST['post_id'];
    $commentText = $_POST['comment_text'];
    $userId = $_SESSION['user_id'];

    // Menggunakan query parameterized 
    $query = "INSERT INTO post_comments (post_id, user_id, comment_text, created_at) VALUES ($1, $2, $3, NOW())";
    $result = pg_query_params($con, $query, array($postId, $userId, $commentText));

    $open_comments = 'true';
    if (isset($source_page)) {
        $redirectUrl = '../index.php';
        if ($source_page === 'profile') {
            $redirectUrl = '../profile.php#post-' . $postId;
        } elseif ($source_page === 'community') {
            $redirectUrl = '../community.php#post-' . $postId;
        }
        $redirectUrl .= strpos($redirectUrl, '?') === false ? '?open_comments=' . $open_comments : '&open_comments=' . $open_comments;
        header('Location: ' . $redirectUrl);
        exit;
    } else {
        header('Location: ../index.php');
        exit;
    }
}
?>
