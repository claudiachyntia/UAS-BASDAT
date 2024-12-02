<?php
session_start();
include('config/conn.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Cek apakah file valid
    $file = $_FILES['profile_picture'];
    $fileTmpName = $file['tmp_name'];
    $fileName = basename($file['name']);
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo "Invalid file type. Please upload a JPG, PNG, or GIF image.";
        exit();
    }
    if ($fileError !== 0) {
        echo "There was an error uploading your file.";
        exit();
    }
    if ($fileSize > 2097152) {
        echo "File is too large. Maximum size is 2MB.";
        exit();
    }
    $uploadDir = 'uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . uniqid() . '_' . $fileName;

    if (move_uploaded_file($fileTmpName, $filePath)) {

        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param('si', $filePath, $userId); 

            if ($stmt->execute()) {
                echo "Profile picture updated successfully!";
                header("Location: profile.php"); 
            } else {
                echo "Failed to update profile picture.";
            }
        } else {
            echo "You need to be logged in to update your profile picture.";
        }
    } else {
        echo "Error uploading the file.";
    }
}
?>
