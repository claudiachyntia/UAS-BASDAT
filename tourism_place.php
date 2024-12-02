<?php
session_start();
include('config/conn.php'); // Pastikan koneksi sudah disesuaikan untuk PostgreSQL

// Mendapatkan ID tempat wisata dari URL
$tourism_id = isset($_GET['tourism_id']) ? (int)$_GET['tourism_id'] : 0;

// Query untuk mengambil data tempat wisata
$query = "SELECT tourism_name, image_url, map_url, description FROM tourismplaces WHERE tourism_id = $tourism_id";
$result = pg_query($con, $query);

if (pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $tourism_name = $row['tourism_name'];
    $image_url = $row['image_url'];
    $map_url = $row['map_url'];
    $description = $row['description'];
} else {
    echo "Data tidak ditemukan.";
    exit;
}

// Cek apakah user sudah menyimpan tempat ini dalam database
$isBookmarked = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id']; // Pastikan sesi sudah dimulai dan user_id disimpan
    $tourismId = $tourism_id; // Pastikan ini adalah ID tempat wisata yang sedang dilihat
    
    // Query untuk memeriksa apakah data sudah ada di wishlist
    $wishlist = "SELECT * FROM wishlist WHERE user_id = $userId AND tourism_id = $tourismId";
    $hasilwishlist = pg_query($con, $wishlist);
    if (pg_num_rows($hasilwishlist) > 0) {
        $isBookmarked = true;
    }

    // Query untuk mendapatkan rating pengguna
    $sql = "SELECT rating_value FROM ratings WHERE user_id = $userId AND tourism_id = $tourismId";
    $result = pg_query($con, $sql);
    if ($result) {
        $row = pg_fetch_assoc($result);
        $existing_rating = $row['rating_value'] ?? null;
    }
} else {
    $existing_rating = null;
}

// Query untuk menghitung rata-rata rating
$query = "SELECT rating_value FROM ratings WHERE tourism_id = $tourism_id";
$stmt = pg_query($con, $query);
$totalRating = 0;
$count = 0;
while ($row = pg_fetch_assoc($stmt)) {
    $totalRating += $row['rating_value'];
    $count++;
}

// Hitung rata-rata rating
$averageRating = $count > 0 ? round($totalRating / $count, 1) : 0;

// Kirim ke JavaScript
echo "<script>var averageRating = $averageRating;</script>";

// Query untuk mendapatkan lokasi dan cuaca
$query = "SELECT tourism_id, tourism_name, map_url FROM tourismplaces WHERE tourism_id = $tourism_id";
$result = pg_query($con, $query);

if ($row = pg_fetch_assoc($result)) {
    $iframe = $row['map_url'];
    preg_match('/!2d(-?\d+(\.\d+)?)!3d(-?\d+(\.\d+)?)/', $iframe, $matches);
    $longitude = $matches[1];
    $latitude = $matches[3];
    $apiKey = '57ebcc195051e20431d693d383e76a1e';
    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat=$latitude&lon=$longitude&units=metric&appid=$apiKey";
    $weatherData = file_get_contents($weatherUrl);
    $weather = json_decode($weatherData, true);
} else {
    $error = "Data tidak ditemukan untuk ID = $tourism_id.";
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

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="main-nav" style="background-color: rgba(0, 0, 0, 0.303);">
        <div class="container">
            <a class="navbar-brand logo fw-bold fs-4 d-flex align-items-center" href="index.php">
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
    
    <div class="main-content"> 
        <h1 class="content-title"><?php echo $tourism_name; ?></h1>
        <button id="bookmarkButton" class="btn btn-outline-primary" data-tourism-id="<?php echo $tourismId; ?>">
            <i class="fa-solid fa-bookmark"></i> <?php echo $isBookmarked ? 'Unsave' : 'Save'; ?>
        </button>      
        <hr class="content-divider">
        
        <div class="image-map-container" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <img src="<?php echo $image_url; ?>" alt="<?php echo $tourism_name; ?>" style="width: 100%; height: 320px; margin-bottom: 20px;">
            </div>
            <div style="flex: 1;">
                <?php echo str_replace('<iframe', '<iframe style="width: 100%; height: 320px;"', $map_url); ?>
            </div>
        </div>

    <!-- Separate container for the card -->
    <div class="card-container" style="max-width: 1150px; margin: auto;">
        <div class="card" style="margin-top: 20px;">
            <div class="card-body" style="display: flex; flex-direction: column; align-items: flex-start;">
                <h2 class="card-title">Rating</h2>
                <p class="card-rating" id="dynamic-rating">
                    <?php
                        $roundedRating = floor($averageRating);
                        $stars = str_repeat('★', $roundedRating) . str_repeat('☆', 5 - $roundedRating);
                        echo "$stars ($roundedRating/5)";
                    ?></p> 
                <h2 class="card-title">Description</h2>
                <p class="card-description" style="text-align: left; margin-top: 6px; margin-left: 15px;">
                    <?php echo $description; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- wheater -->
    <div class="container my-4" style="display: flex; justify-content: center; align-items: center;">        <?php if (isset($error)) : ?>
            <div class="alert alert-danger text-center"><?= $error; ?></div>
        <?php else : ?>
            <div class="widget">
                <div class="details">
                <div class="temperature"><?= $weather['main']['temp']; ?>°</div>
                <div class="summary">
                    <p class="summaryText"><?= ucfirst($weather['weather'][0]['description']); ?></p>
                </div>
                <div class="precipitation">Humidity: <?= $weather['main']['humidity']; ?>%</div>
                <div class="wind">Wind: <?= $weather['wind']['speed']; ?> </div>
                </div>
                <div class="pictoBackdrop"></div>
                <div class="pictoFrame"></div>
                <div class="pictoCloudBig"></div>
                <div class="pictoCloudFill"></div>
                <div class="pictoCloudSmall"></div>
                <div class="iconCloudBig"></div>
                <div class="iconCloudFill"></div>
                <div class="iconCloudSmall"></div>
            </div>
        <?php endif; ?>
    </div>

        <div class="wrapper-rating" style="margin-top: 20px;">
            <?php if ($existing_rating): ?>
                <!-- Jika user sudah memberikan rating -->
                <p id="message">
                    <?php
                        $ratingText = "";
                        switch ($existing_rating) {
                            case 1:
                                $ratingText = "Terrible";
                                break;
                            case 2:
                                $ratingText = "Bad";
                                break;
                            case 3:
                                $ratingText = "Good";
                                break;
                            case 4:
                                $ratingText = "Satisfied";
                                break;
                            case 5:
                                $ratingText = "Excellent";
                                break;
                        }
                        echo $ratingText;
                    ?>
                </p>
                <div class="container-rating" style="display: flex; gap: 5px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="star-container <?php echo $i <= $existing_rating ? 'inactive' : 'active'; ?>" data-value="<?php echo $i; ?>">
                            <i class="<?php echo $i <= $existing_rating ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                            <span class="number"><?php echo $i; ?></span>
                        </div>
                    <?php endfor; ?>
                    </div>
            <?php else: ?>
                <!-- Jika user belum memberikan rating -->
                <p id="message">Rate Your Experience</p>
                <div class="container-rating" style="display: flex; gap: 5px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="star-container inactive" data-value="<?php echo $i; ?>">
                            <i class="fa-regular fa-star"></i>
                            <span class="number"><?php echo $i; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
                <button id="submit" disabled style="margin-top: 10px;" data-user-id="<?php echo $user_id; ?>" data-tourism-id="<?php echo $tourism_id; ?>">Submit</button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- footer -->
    <?php include 'footer.php'; ?>
    
    <script>
        var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="js/rating.js"></script>
    <script src="js/bookmark.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>

</html>