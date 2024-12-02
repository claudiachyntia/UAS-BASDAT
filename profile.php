<?php
session_start();
include('config/conn.php');
//choose account for profil page
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id']; 
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: login.php');
    exit;
}
// Profil Picture
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $profilePicture = $user['profile_picture'];
} else {
    $profilePicture = 'uploads/default_profile_picture.jpg';
}
$sql_user = "
    SELECT users.username,
           (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.user_id) AS post_count,
           (SELECT COUNT(*) FROM post_likes WHERE post_likes.user_id = users.user_id) AS like_count
    FROM users
    WHERE users.user_id = ?";
$stmt_user = $con->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $row = $result_user->fetch_assoc();
    $username = $row['username'];
    $post_count = $row['post_count'];
    $like_count = $row['like_count'];
} else {
    $username = 'Guest';
    $post_count = 0;
    $like_count = 0;
}

// Query untuk Likes
$sql_likes = "
    SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
        (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = ?
    LEFT JOIN post_comments pc ON pc.post_id = p.id
    WHERE EXISTS (
        SELECT 1 
        FROM post_likes 
        WHERE post_likes.post_id = p.id AND post_likes.user_id = ?
    )
    GROUP BY p.id
    ORDER BY p.created_at DESC";
$stmt_likes = $con->prepare($sql_likes);
$stmt_likes->bind_param("ii", $_SESSION['user_id'], $user_id);
$stmt_likes->execute();
$likes_result = $stmt_likes->get_result(); 

$sql_posts = "
    SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
           (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
           (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = ?
    LEFT JOIN post_comments pc ON pc.post_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC";
$stmt_posts = $con->prepare($sql_posts);
$stmt_posts->bind_param("ii", $_SESSION['user_id'], $user_id);
$stmt_posts->execute();
$posts_result = $stmt_posts->get_result();

function getComments($post_id, $con) {
    $query = "SELECT c.comment_text, c.created_at, u.username, u.profile_picture 
              FROM post_comments c
              JOIN users u ON c.user_id = u.user_id
              WHERE c.post_id = $post_id
              ORDER BY c.created_at DESC";
    $result = mysqli_query($con, $query);
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['profile_picture'] = !empty($row['profile_picture']) ? $row['profile_picture'] : 'uploads/default_profile_picture.jpg';
        $comments[] = $row;
    }
    return $comments;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>

    <!-- Custom CSS File -->
    <link href="css/profile.css" rel="stylesheet">
    <link href="css/community.css" rel="stylesheet">

    <!-- Google Fonts for Material Icons -->
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

    <title>WikiTrip Community</title>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-picture-container">
                <img alt="Profile Picture" src="<?= $profilePicture; ?>" width="80" class="profile-picture"/>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                    <a href="delete_picture.php?user_id=<?= $user_id; ?>" class="delete-icon" onclick="return confirm('Are you sure you want to delete your profile picture?');">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                    <a href="#" class="edit-icon" onclick="document.getElementById('uploadModal').style.display='block'; return false;">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <div class="delete-account">
                        <!-- Tombol Delete Account -->
                        <a href="#" onclick="document.getElementById('confirmDelete').style.display='block';" 
                        style="font-size: 10px; color: red; text-decoration: none;">
                            Delete Account
                        </a>
                    </div>

                    <!-- Modal (Pop-up) untuk konfirmasi password -->
                    <div id="confirmDelete" class="modal" style="display:none;">
                        <div class="modal-content">
                            <span class="close" onclick="document.getElementById('confirmDelete').style.display='none';">&times;</span>
                            <h3>Delete Account</h3>
                            <p>To delete your account, please enter your password for confirmation:</p>
                            <form action="delete_account.php" method="POST">
                                <input type="password" name="password" required placeholder="Enter your password" />
                                <button type="submit" name="confirm_delete">Confirm Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div id="uploadModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="document.getElementById('uploadModal').style.display='none';">&times;</span>
                    <h2>Change Profile Picture</h2>
                    <form action="submit_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_picture" id="profile_picture" accept="" required>
                        <button type="submit">Upload</button>
                    </form>
                </div>
            </div>
            <div class="profile-details">
                <h2><?= htmlspecialchars($username); ?></h2>
                <div class="profile-stats">
                    <div>
                        <p>Activities</p>
                        <span><?= $post_count ?> Posts  <?= $like_count ?> Likes</span>
                    </div>   
                </div>
            </div>
        </div>        
        <div class="profile-nav">
            <a href="#" id="show-posts">Posts</a>
            <a href="#" id="show-likes">Likes</a>
        </div>
    </div>
    
    <div id="activities" class="content" style="display: none;">
        <div class="section" >
                <i class="bi bi-file-earmark-text">
                    <p>Posts</p>
                </i> 
                <h3 class="divider"></h3>
            <div class="activity">
                <?php if ($posts_result->num_rows > 0): ?>
                    <?php $result = $posts_result; ?>
                    <?php
                    $source_page = 'profile'; 
                    include 'post.php';
                    ?>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="reviews" class="content">
        <div class="section" >
                <i class="bi bi-file-earmark-text">
                    <p>Likes</p>
                </i> 
                <h3 class="divider"></h3>
            <div class="activity">
                <?php if ($likes_result->num_rows > 0): ?>
                    <?php $result = $likes_result; ?>
                    <?php
                    $source_page = 'profile'; 
                    include 'post.php';
                    ?>
                <?php else: ?>
                    <p>No likes found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="js/profile.js"></script>
    <script src="js/post_action.js"></script>
</body>
</html>
