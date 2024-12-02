<?php
session_start();
include('config/conn.php');
include('php_tools/event_rrs.php');

$isLoggedIn = isset($_SESSION['user_id']);

$query = "
SELECT tp.tourism_name, tp.image_url, tp.tourism_id, COALESCE(avg_ratings.average_rating, 0) AS average_rating
FROM tourismplaces tp
LEFT JOIN (
    SELECT tourism_id, AVG(rating_value) AS average_rating
    FROM ratings
    GROUP BY tourism_id
) AS avg_ratings ON tp.tourism_id = avg_ratings.tourism_id
ORDER BY average_rating DESC, tp.tourism_id ASC
LIMIT 5";
$result = pg_query($con, $query);

// for event calendar
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
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- style css -->
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
        integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <title>WikiTrip</title>

</head>

<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="main-nav">
        <div class="container">
            <a class="navbar-brand logo fw-bold fs-4 d-flex align-items-center" href="#page-top">
                <img src="image/logo_wikitrip.png" alt="Logo" class="logo-img me-2">
                <span class="text-logo1">WIKI</span><span class="text-logo2">TRIP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active text-white" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#about">About</a>
                    </li>
                    <!-- Destination Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Destination
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#nature-destination">Nature destinations</a></li>
                            <li><a class="dropdown-item" href="#cultural-destination">Cultural destinations</a></li>
                            <li><a class="dropdown-item" href="#culinary-destination">Culinary destinations</a></li>
                        </ul>
                    </li>
                    <!-- Event Dropdown -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#Event">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="community.php">Community</a>
                    </li>
                </ul>
                <div>
                    <ul>
                        <!-- Profile Dropdown -->
                        <li class="nav-item profile-dropdown" >
                            <div class="bi bi-person-circle text-white fs-4 me-2"></div>
                            <ul>
                                <li class="sub-item">
                                    <a href="profile.php" class="profile-link" style="text-decoration: none; display: flex; align-items: center;">
                                        <i class="bi bi-person-circle material-icons-outlined"></i>
                                        <p style="margin-left: 8px" >Profile</p>
                                    </a>
                                </li>
                                <li class="sub-item">
                                    <a href="bookmark.php" class="bookmark-link" style="text-decoration: none; display: flex; align-items: center;">
                                        <i class="bi bi-bookmark material-icons-outlined"></i>
                                        <p style="margin-left: 8px;">Bookmark</p>
                                    </a>
                                </li>
                                <li class="sub-item">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="php_tools/logout.php" style="text-decoration: none; display: flex; align-items: center;">
                                            <i class="bi bi-box-arrow-left material-icons-outlined"></i>
                                            <p style="margin-left: 8px;">Logout</p>
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" style="text-decoration: none; display: flex; align-items: center;">
                                            <i class="bi bi-box-arrow-left material-icons-outlined"></i>
                                            <p style="margin-left: 8px;">Login</p>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- slider -->
    <div class="slider">
        <div class="list">
            <?php
            if (pg_num_rows($result) > 0) {
                $active = ' active';
                while ($row = pg_fetch_assoc($result)) {
                    $tourism_name = $row['tourism_name'];
                    $image_url = $row['image_url'];
                    ?>
                    <div class="item<?php echo $active; ?>" data-id="<?php echo $row['tourism_id']; ?>">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo $tourism_name; ?>">
                        <div class="content" style="margin-left: -30px;" >
                            <p>Welcome To <span style="color: #f8b200;">SUMATERA UTARA</span></p>
                            <h1>Discover</h1>
                            <h2><span style="color:#f8b200;"><?php echo $tourism_name; ?></span></h2>
                        </div>
                    </div>
                    <?php
                    $active = ''; 
                }
            } else {
                echo "<p>No data available</p>";
            }
            ?>
        </div>

        <!-- button arrows -->
        <div class="arrows">
            <button id="prev"></button>
            <button id="next"></button>
        </div>

        <!-- thumbnail -->
        <div class="thumbnail">
            <?php
            pg_result_seek($result, 0);
            $active = ' active';
            while ($row = pg_fetch_assoc($result)) {
                $tourism_name = $row['tourism_name'];
                $image_url = $row['image_url'];
                ?>
                <div class="item <?php echo $active; ?>">
                    <img src="<?php echo $image_url; ?>" alt="<?php echo $tourism_name; ?>">
                    <div class="content">
                        <?php echo $tourism_name; ?>
                    </div>
                </div>
                <?php
                $active = '';
            }
            ?>
        </div>
    </div>

    <!-- About Section Start-->
    <section id="about" class="about">
        <h1 class="heading">
            <span style="color: #1c98ed;">A</span>bout
            <span style="color: #1c98ed;">U</span>s
        </h1>
        <div class="wrapper">
            <img src="image/1.png" alt="">
            <div class="text-box">
                <h2>Why Choose Us?</h2>
                <p>We are dedicated to help you explore amazing destinations. Our goal is to provide you with useful
                information. Whether you want to relax on the beach, hike in the mountains, or discover new
                cultures, we are here to guide you. Join us in exploring the world!</p> 
            </div>
        </div>
    </section>
    <!-- About Section End -->

  <!-- <--card slider nature start --> 
    <?php
        $categoryQuery = "SELECT category_id, category_name, little_desc FROM tourismcategories";
        $category_result = pg_query($con, $categoryQuery);

        if (pg_num_rows($category_result) > 0) {
            while ($category = pg_fetch_assoc($category_result)) {
                $categoryId = $category['category_id'];
                $categoryName = $category['category_name'];
                $description = $category['little_desc'];

                echo '
                <div id="' . strtolower(str_replace(' ', '-', $categoryName)) . '" class="carousel-header">
                    <h2>' . htmlspecialchars($categoryName) . '</h2>
                    <p>' . htmlspecialchars($description) . '</p>
                    <a href="category.php?category_id=' . $categoryId . '" class="read-more-btn">Read More</a> 
                </div>
                <div class="wikit-carousel">
                    <div class="wikit-carousel__wrapper">
                        <i id="left-' . strtolower(str_replace(' ', '-', $categoryName)) . '" class="fa fa-angle-left"></i> 
                        <div class="wikit-carousel__carousel">';

                $cityQuery = "
                SELECT c.city_id, c.city_name, MIN(t.image_url) AS image_url
                FROM cities c
                LEFT JOIN tourismplaces t ON c.city_id = t.city_id
                LEFT JOIN tourismcategories tc ON t.category_id = tc.category_id
                WHERE tc.category_id = $categoryId
                GROUP BY c.city_id, c.city_name
                ";
                $city_result = pg_query($con, $cityQuery);

                if (pg_num_rows($city_result) > 0) {
                    while ($row = pg_fetch_assoc($city_result)) {
                        $city_id = $row['city_id'];
                        $cityName = htmlspecialchars($row['city_name']);
                        $imageUrl = $row['image_url'] ? htmlspecialchars($row['image_url']) : 'image/background_nature.jpg';

                        echo '
                        <div class="card">
                            <a href="citycategory.php?city_id=' . $city_id . '&category_id=' . $categoryId . '&image_url=' . $imageUrl . '" class="discover__card">
                                <img src="' . $imageUrl . '" alt="img" draggable="false">
                                <div class="city-name">' . $cityName . '</div>
                            </a>
                        </div>';
                    }
                } else {
                    echo '<div class="no-cities">No cities found for this category.</div>';
                }

                echo '
                        </div>
                        <i id="right-' . strtolower(str_replace(' ', '-', $categoryName)) . '" class="fa fa-angle-right"></i> 
                    </div>
                </div>';
            }
        } else {
            echo "<div class='no-categories'>No categories found.</div>";
        }
    ?>

<!-- Event section start -->
<div class="container-event" id="Event">
    <div class="event-banner">
        <img src="image/pikaso_edit.jpeg" alt="Event Banner" class="event-img">
        <div class="event-text">
            <h2>Best Event in <span style="font-weight: 600;">North Sumatera</span></h2>
            <p>Explore the Events in North Sumatera!</p>
        </div>
    </div>

<!-- Calendar Event Section Start -->
<div class="event-calendar">
    <h3>Calendar Event in 2024</h3>
    <div class="calendar-container">
        <button class="nav-button left" onclick="slideLeft()">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="slider-calendar">
            <!-- Repeat month-card div for each month -->
                <?php
                // Generate HTML untuk setiap bulan
                foreach ($events_by_month as $month => $events) {
                    echo '<div class="month-card">';
                    echo '<div class="month-header"><h4>' . htmlspecialchars($month) . '</h4></div>';
                    echo '<div class="event-list">';
                    $limited_events = array_slice($events, 0, 2);
                    foreach ($limited_events as $event) {
                        echo '<div class="event-item">';
                        echo '<span class="event-date">' . htmlspecialchars($event['date']) . '</span>';
                        echo '<span class="event-name">' . htmlspecialchars($event['name']) . '</span>';
                        echo '</div>';
                    }

                    // Tampilkan "more" jika ada lebih dari 3 acara
                    if (count($events) > 3) {
                        echo '<div class="more-events">...and more events</div>';
                    }
                    echo '</div></div>';
                }
                ?>
                <button class="nav-button right" onclick="slideRight()">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        </div>
        <button class="nav-button right" onclick="slideRight()">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Event Recommendation Section Start -->
<div class="recommended-events">
    <h3>Event Recommendation</h3>
    <div class="calendar-container">
        <button class="nav-button left" onclick="slideLeft()">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="slider-events">
            <?php
            if ($rss_data) {
                $count = 0;
                foreach ($rss_data->channel->item as $item) {
                    if ($count >= 10) break;

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
                    echo '<p>' . htmlspecialchars(html_entity_decode($description)) . '</p>';
                    echo '</div></div>';

                    $count++;
                }
            } else {
                echo "<p>Tidak ada event yang tersedia saat ini.</p>";
            }
            ?>
        </div>
        <button class="nav-button right" onclick="slideRight()">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>
<!-- Event Recommendation Section End -->




    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="js/script.js"></script>
    <script src="js/app.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>

</html>
