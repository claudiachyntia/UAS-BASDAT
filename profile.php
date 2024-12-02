<?php
session_start();
include('config/conn.php'); // Assuming you're using pg_connect for PostgreSQL

// Choose account for profile page
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
    $sql = "SELECT profile_picture FROM users WHERE user_id = $1";
    $stmt = pg_prepare($con, "profile_picture_query", $sql);
    $result = pg_execute($con, "profile_picture_query", array($user_id));
    $user = pg_fetch_assoc($result);
    $profilePicture = $user['profile_picture'];
} else {
    $profilePicture = 'uploads/default_profile_picture.jpg';
}

$sql_user = "
    SELECT users.username,
           (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.user_id) AS post_count,
           (SELECT COUNT(*) FROM post_likes WHERE post_likes.user_id = users.user_id) AS like_count
    FROM users
    WHERE users.user_id = $1";
$stmt_user = pg_prepare($con, "user_query", $sql_user);
$result_user = pg_execute($con, "user_query", array($user_id));

// Instead of checking for num_rows, use pg_fetch_assoc to directly get the result
if ($row = pg_fetch_assoc($result_user)) {
    $username = $row['username'];
    $post_count = $row['post_count'];
    $like_count = $row['like_count'];
} else {
    $username = 'Guest';
    $post_count = 0;
    $like_count = 0;
}

// Query for Likes
$sql_likes = "
    SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
        (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = $1
    LEFT JOIN post_comments pc ON pc.post_id = p.id
    WHERE EXISTS (
        SELECT 1 
        FROM post_likes 
        WHERE post_likes.post_id = p.id AND post_likes.user_id = $2
    )
    GROUP BY p.id, u.username, u.profile_picture, l.id
    ORDER BY p.created_at DESC";
$stmt_likes = pg_prepare($con, "likes_query", $sql_likes);
$likes_result = pg_execute($con, "likes_query", array($_SESSION['user_id'], $user_id));

$sql_posts = "
    SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
           (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
           (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = $1
    LEFT JOIN post_comments pc ON pc.post_id = p.id
    WHERE p.user_id = $2
    GROUP BY p.id, u.username, u.profile_picture, l.id
    ORDER BY p.created_at DESC";
$stmt_posts = pg_prepare($con, "posts_query", $sql_posts);
$posts_result = pg_execute($con, "posts_query", array($_SESSION['user_id'], $user_id));

function getComments($post_id, $con) {
    $query = "SELECT c.comment_text, c.created_at, u.username, u.profile_picture 
              FROM post_comments c
              JOIN users u ON c.user_id = u.user_id
              WHERE c.post_id = $1
              ORDER BY c.created_at DESC";
    $stmt = pg_prepare($con, "comments_query", $query);
    $result = pg_execute($con, "comments_query", array($post_id));
    
    $comments = [];
    while ($row = pg_fetch_assoc($result)) {
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
    <div class="section">
            <i class="bi bi-file-earmark-text">
                <p>Posts</p>
            </i> 
            <h3 class="divider"></h3>
            <div class="activity">
                <?php
                // Check if there are any posts results
                $posts_found = false;
                while ($post = pg_fetch_assoc($posts_result)) {
                    $posts_found = true;
                    break; // Exit the loop after the first post is found
                }

                if ($posts_found):
                    // If posts are found, show the posts
                    $result = $posts_result;
                    $source_page = 'profile';
                    include 'post.php';
                else:
                    // If no posts, show a message
                    echo "<p>No posts found.</p>";
                endif;
                ?>
            </div>
        </div>
    </div>

    <div id="reviews" class="content">
        <div class="section">
            <i class="bi bi-file-earmark-text">
                <p>Likes</p>
            </i> 
            <h3 class="divider"></h3>
            <div class="activity">
                <?php
                // Check if there are any likes results
                $likes_found = false;
                while ($like = pg_fetch_assoc($likes_result)) {
                    $likes_found = true;
                    break; // Exit the loop after the first like is found
                }

                if ($likes_found):
                    // If likes are found, show the posts
                    $result = $likes_result;
                    $source_page = 'profile';
                    include 'post.php';
                else:
                    // If no likes, show a message
                    echo "<p>No likes found.</p>";
                endif;
                ?>
            </div>
        </div>
    </div>


    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="js/profile.js"></script>
    <script src="js/post_action.js"></script>
</body>
</html>
