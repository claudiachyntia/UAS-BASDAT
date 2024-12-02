<?php
session_start();
include('config/conn.php');
header('Content-Type: application/json');

// Mendapatkan data JSON dari request
$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['postId'];
$userId = $_SESSION['user_id'];
$isLiked = $data['like'];

if ($isLiked) {
    // Menyimpan like ke database
    $query = "INSERT INTO post_likes (post_id, user_id) VALUES ($1, $2)";
    $result = pg_query_params($con, $query, array($postId, $userId));
    if ($result) {
        echo json_encode(["message" => "Post liked successfully."]);
    } else {
        echo json_encode(["message" => "Error liking post."]);
    }
} else {
    // Menghapus like dari database
    $query = "DELETE FROM post_likes WHERE post_id = $1 AND user_id = $2";
    $result = pg_query_params($con, $query, array($postId, $userId));
    if ($result) {
        echo json_encode(["message" => "Post unliked successfully."]);
    } else {
        echo json_encode(["message" => "Error unliking post."]);
    }
}

// Menghitung jumlah likes
$count_query = "SELECT COUNT(*) AS like_count FROM post_likes WHERE post_id = $1";
$count_result = pg_query_params($con, $count_query, array($postId));
if ($count_result) {
    $like_count = pg_fetch_assoc($count_result)['like_count'];
    echo json_encode(['success' => true, 'like_count' => $like_count]);
} else {
    echo json_encode(['success' => false, 'message' => "Error fetching like count."]);
}

header("Location: community.php");
exit();
?>
