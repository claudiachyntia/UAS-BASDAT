<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="main-nav" style="background-color: #0a598f;">
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