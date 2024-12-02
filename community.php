<?php
session_start();
include('config/conn.php');
include('php_tools/event_rrs.php');

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($con, $_GET['keyword']) : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

$profilePicture = 'uploads/default_profile_picture.jpg';

$rss_data->registerXPathNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
$events_by_month = [];

foreach ($rss_data->channel->item as $item) {
    $date = date_create((string)$item->pubDate);
    $month = date_format($date, 'F'); 
    $day = date_format($date, 'd'); 

    $event_name = (string)$item->title;

    if (!isset($events_by_month[$month])) {
        $events_by_month[$month] = [];
    }
    $events_by_month[$month][] = [
        'date' => $day,
        'name' => $event_name,
    ];
}


if (isset($_SESSION['user_id']) && isset($con)) {
    $sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $profilePicture = $user['profile_picture'] ?? $profilePicture;
        }
        $stmt->close();
    } else {
        error_log("Error preparing SQL statement: " . $con->error);
    }
}

if ($user_id !== NULL) {
    // Pengguna login
    if ($keyword != '') {
        $query = "SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
                (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = $user_id
                LEFT JOIN post_comments pc ON pc.post_id = p.id
                WHERE p.content LIKE '%$keyword%' OR u.username LIKE '%$keyword%'
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        echo "<h6>Result search for : '$keyword'</h6>";
    } else {
        $query = "SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
                (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) AS is_liked,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN post_likes l ON l.post_id = p.id AND l.user_id = $user_id
                LEFT JOIN post_comments pc ON pc.post_id = p.id
                GROUP BY p.id
                ORDER BY p.created_at DESC";
    }
} else {
    // Pengguna tidak login
    if ($keyword != '') {
        $query = "SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN post_likes pl ON pl.post_id = p.id
                LEFT JOIN post_comments pc ON pc.post_id = p.id
                WHERE p.content LIKE '%$keyword%' OR u.username LIKE '%$keyword%'
                GROUP BY p.id
                ORDER BY p.created_at DESC";
    echo "<h6>Result search for : '$keyword'</h6>";
    } else {
        $query = "SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username, u.profile_picture,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                LEFT JOIN post_likes pl ON pl.post_id = p.id
                LEFT JOIN post_comments pc ON pc.post_id = p.id
                GROUP BY p.id
                ORDER BY p.created_at DESC";
    }
}
$result = mysqli_query($con, $query);

function getComments($post_id, $con) {
    $query = "SELECT c.comment_text, c.created_at, u.username, u.profile_picture 
            FROM post_comments c
            JOIN users u ON c.user_id = u.user_id
            WHERE c.post_id = $post_id
            ORDER BY c.created_at DESC";
$result = mysqli_query($con, $query);
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['profile_picture'] = !empty($row['profile_picture']) ?$row['profile_picture'] : 'uploads/default_profile_picture.jpg';
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
    <link href="css/community.css" rel="stylesheet">

    <!-- Google Fonts for Material Icons -->
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

    <title>WikiTrip Community</title>
</head>


<body>
    <?php include'navbar.php'; ?>

    <div class="content">
        <div class="left-panel">
            <div class="search-container">
                <form method="GET" action="">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Search posts..." value="<?php if(isset($_GET['keyword'])) echo $_GET['keyword']; ?>">
                </form>
            </div>
            <a href="index.php#nature-destination" style="text-decoration: none; color: inherit;">
                <div class="option"><i class="fas fa-tree"></i> Natural Destination</div>
            </a>
            <a href="index.php#culinary-destination" style="text-decoration: none; color: inherit;">
                <div class="option"><i class="fas fa-utensils"></i> Culinary Destination</div>
            </a>
            <a href="index.php#cultural-destination" style="text-decoration: none; color: inherit;">
                <div class="option"><i class="fas fa-landmark"></i> Cultural Destination</div>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="post-box">
        <form action="post_submit.php" method="POST" enctype="multipart/form-data">
                <div class="post-header">
                <img alt="Profile Picture" src="<?= $profilePicture; ?>" height="40" width="40" >
                    <input type="text" name="content" placeholder="What's on your mind?" required>
                </div>
                <div class="post-actions" style="margin-top: 20px;">
                    <div>
                        <label for="image-upload"><i class="fas fa-camera"></i> Photo</label>
                        <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;">
                    </div>
                    <button type="submit" style="background:none; border:none; color:inherit;">
                        <div><i class="fas fa-pencil-alt"></i> Post</div>
                    </button>
                </div>
            </form>
        </div>
            <?php
            $source_page = 'community'; 
            include 'post.php'; 
            ?>
        <div class="right-panel">
            <div class="event-card">
                <div class="event-header">
                    <h4><b>Upcoming Events</b></h4>
                    <a href="index.php#Event" class="see-all">See All</a>
                </div>
                <?php
                if ($rss_data) {
                    $count = 0;
                    foreach ($rss_data->channel->item as $item) {
                        if ($count >= 1) break;

                        $title = $item->title;
                        $link = $item->link;
                        $description = strip_tags($item->description);
                        $pubDate = date("d M Y", strtotime($item->pubDate));
                        $content_encoded = (string)$item->children('content', true)->encoded;
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content_encoded, $matches);
                        $image_url = isset($matches[1]) ? $matches[1] : 'image/event.jpg';

                        echo '<div class="month-card">';
                        echo '<div class="month-header">';
                        echo '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($title) . '" />';
                        echo '</div>';
                        echo '<div class="event-list">';
                        echo '<h4><a href="' . htmlspecialchars($link) . '" target="_blank">' . htmlspecialchars($title) . '</a></h4>';
                        echo '<p>' . htmlspecialchars($pubDate) . '</p>';
                        echo '<p>' . htmlspecialchars($description) . '</p>';
                        echo '</div></div>';

                        $count++;
                    }
                } else {
                    echo "<p>Tidak ada event yang tersedia saat ini.</p>";
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="js/post_action.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
    
</body>
</html>
