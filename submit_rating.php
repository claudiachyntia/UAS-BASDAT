<?php
session_start();
include('config/conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$tourismId = $_POST['tourism_id'];
$ratingValue = $_POST['rating'];

// Cek validitas input
if (empty($tourismId) || empty($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
    echo 'invalid_input';
    exit();
}

// Masukkan rating ke database
$sql = "INSERT INTO ratings (user_id, tourism_id, rating_value, time) VALUES ('$userId', '$tourismId', '$ratingValue', NOW())
        ON DUPLICATE KEY UPDATE rating_value='$ratingValue', time=NOW()";

if ($con->query($sql) === TRUE) {
    echo 'success';
} else {
    echo 'error: ' . $con->error;
}

$query = "SELECT rating_value FROM ratings WHERE tourism_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $tourism_id);
$stmt->execute();
$result = $stmt->get_result();

$totalRating = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $totalRating += $row['rating_value'];
    $count++;
}

// Hitung rata-rata rating
$averageRating = $count > 0 ? round($totalRating / $count, 1) : 0;

// Kembalikan rata-rata baru sebagai respons
echo $averageRating;

$con->close();
?>
