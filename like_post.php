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
    $query = "INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $postId, $userId);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Post liked successfully."]);
    } else {
        echo json_encode(["message" => "Error liking post."]);
    }
} else {
    // Menghapus like dari database
    $query = "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $postId, $userId);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Post unliked successfully."]);
    } else {
        echo json_encode(["message" => "Error unliking post."]);
    }
}
$count_query = "SELECT COUNT(*) AS like_count FROM post_likes WHERE post_id = ?";
$count_stmt = $con->prepare($count_query);
$count_stmt->bind_param("i", $postId);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$like_count = $count_result->fetch_assoc()['like_count'];
echo json_encode(['success' => true, 'like_count' => $like_count]);
header("Location: community.php");
exit();
?>
