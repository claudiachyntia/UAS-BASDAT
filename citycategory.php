<?php
session_start();
include('config/conn.php');
$isLoggedIn = isset($_SESSION['user_id']);

$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$image_url = isset($_GET['image_url']) ? $_GET['image_url'] : '';

// Query untuk mengambil informasi kota dari tabel cities
$city_query = "
    SELECT city_name, description
    FROM cities
    WHERE city_id = $1
";
$result = pg_query_params($con, $city_query, array($city_id));
if ($result && pg_num_rows($result) > 0) {
    $city = pg_fetch_assoc($result);
    $city_name = htmlspecialchars($city['city_name']);
    $city_description = htmlspecialchars($city['description']);
} else {
    $city_name = '';
    $city_description = '';
}

// Query untuk mengambil category_name dari tabel tourismcategories
$category_query = "
    SELECT category_name
    FROM tourismcategories
    WHERE category_id = $1
";
$result = pg_query_params($con, $category_query, array($category_id));
if ($result && pg_num_rows($result) > 0) {
    $category = pg_fetch_assoc($result);
    $category_name = htmlspecialchars($category['category_name']);
} else {
    $category_name = '';
}

// Query untuk mengambil tourismplaces yang sesuai dengan category_id dan city_id
$tour_query = "
    SELECT tp.tourism_id, tp.tourism_name, tp.image_url, COALESCE(AVG(r.rating_value), 0) AS average_rating
    FROM tourismplaces tp
    LEFT JOIN ratings r ON tp.tourism_id = r.tourism_id
    WHERE tp.city_id = $1 AND tp.category_id = $2
    GROUP BY tp.tourism_id, tp.tourism_name, tp.image_url
";
$result = pg_query_params($con, $tour_query, array($city_id, $category_id));
if ($result) {
    $tour_places = pg_fetch_all($result);
} else {
    $tour_places = [];
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
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
            <div class="collapse navbar-collapse justify-content-center" id=" navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active text-white" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php#about">About</a>
                    </li>
                    <!-- Destination Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Destination
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="index.php#nature-destination">Nature destinations</a></li>
                            <li><a class="dropdown-item" href="index.php#cultural-destination">Cultural destinations</a></li>
                            <li><a class="dropdown-item" href="index.php#culinary-destination">Culinary destinations</a></li>
                        </ul>
                    </li>
                    <!-- Event Dropdown -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php#Event">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="community.php">Community</a>
                    </li>
                </ul>
                <div>
                    <ul>
                        <!-- Profile Dropdown -->
                        <li class="nav-item profile-dropdown">
                            <div class="bi bi-person-circle text-white fs-4 me-2"></div>
                            <ul>
                                <li class="sub-item">
                                    <a href="profile.php" class="profile-link" style="text-decoration: none; display: flex; align-items: center;">
                                        <i class="bi bi-person-circle material-icons-outlined"></i>
                                        <p style="margin-left: 8px">Profile</p>
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

    <div class="main_background"> 
        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($city_name); ?>" class="mainback_img">
        <div class="home__container container" style="align-items: center;">
            <div class="home__data">
                <h1 class="home__data-title" style="font-size: 4rem; text-decoration:overline underline;">
                    <?php echo htmlspecialchars($city_name); ?>
                </h1>
            </div>
        </div>
    </div>

    <div class="main-content">
        <h2 class="content-title"><?php echo htmlspecialchars($city_name); ?></h2>
        <p class="content-description"><?php echo htmlspecialchars($city_description); ?></p>
        <hr class="content-divider">

        <h3 class="explore-title" id="explore">Explore The <?php echo htmlspecialchars($category_name); ?> Of <?php echo htmlspecialchars($city_name); ?></h3>
        <div class="culinary-card">
            <?php if (!empty($tour_places)): ?>
                <?php foreach ($tour_places as $place): ?>
                    <a href="tourism_place.php?tourism_id=<?php echo $place['tourism_id']; ?>" class="restaurant" style="text-decoration: none;">
                        <img src="<?php echo htmlspecialchars ($place['image_url']); ?>" class="restaurant-img">
                        <h3 class="card-title" style="margin-top: 15px; font-size: 1.15rem; padding-left: 15px;">
                            <?php echo htmlspecialchars($place['tourism_name']); ?>
                        </h3>
                        <p class="card-rating" style="margin-top: 0%; padding-left: 15px;">
                            <?php
                            $roundedRating = floor($place['average_rating']);
                            $stars = str_repeat('★', $roundedRating) . str_repeat('☆', 5 - $roundedRating);
                            echo "$stars ($roundedRating/5)";
                            ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="js/nature.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

</body>

</html>