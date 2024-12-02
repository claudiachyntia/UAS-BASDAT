<?php 
session_start();
include('config/conn.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Query untuk mendapatkan semua tempat yang dibookmark oleh user
$query = "
    SELECT tp.tourism_id, tp.tourism_name, tp.image_url, tp.description 
    FROM wishlist w
    JOIN tourismplaces tp ON w.tourism_id = tp.tourism_id
    WHERE w.user_id = $userId
";
$result = pg_query($con, $query);
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
    <?php include 'navbar.php'; ?>

    <!-- Bookmark Section -->
    <div class="bookmark-section container" style ="margin-bottom: 40px">
        <h2 class="bookmark-section-title">Bookmark</h2>
        <?php if (pg_num_rows($result) > 0) { ?>
        <div class="bookmark-section-divider"></div>
        <div class="row">
            <?php while ($row = pg_fetch_assoc($result)) { ?>
                <div class="col-md-4">
                    <div class="bookmark-section-card card">
                        <img class="bookmark-section-card-img card-img-top" src="<?php echo $row['image_url']; ?>" alt="Destination Image">
                        <div class="bookmark-section-card-body card-body">
                            <h5 class="bookmark-section-card-title card-title"><?php echo $row['tourism_name']; ?></h5>
                            <p class="bookmark-section-card-text card-text">explore so you can share your own experience.</p>
                            <a href="tourism_place.php?tourism_id=<?php echo $row['tourism_id']; ?>" class="bookmark-section-card-btn btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php } else { ?>
            <div style="text-align: center; font-weight: bold; margin-top: 50px; margin-bottom: 250px;">
                <p style="color:#6c757d; font-size: 18px;" >You haven't saved anything</p>
            </div>
        <?php } ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>
