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
$sql = "INSERT INTO ratings (user_id, tourism_id, rating_value, time) 
        VALUES ($1, $2, $3, NOW())
        ON CONFLICT (user_id, tourism_id) DO UPDATE SET rating_value = EXCLUDED.rating_value, time = NOW()";

$result = pg_query_params($con, $sql, array($userId, $tourismId, $ratingValue));

if ($result) {
    echo 'success';
} else {
    echo 'error: ' . pg_last_error($con);
}

// Ambil data rating
$query = "SELECT rating_value FROM ratings WHERE tourism_id = $1";
$result = pg_query_params($con, $query, array($tourismId));

$totalRating = 0;
$count = 0;

while ($row = pg_fetch_assoc($result)) {
    $totalRating += $row['rating_value'];
    $count++;
}

// Hitung rata-rata rating
$averageRating = $count > 0 ? round($totalRating / $count, 1) : 0;

echo $averageRating;
?>
