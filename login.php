<?php
session_start(); // Pastikan session dimulai di awal
include('config/conn.php');

// Cek login
if (isset($_POST['cek_login'])) {
  $username = $_POST['username'];
  $password_hash = $_POST['password'];

  if (empty($username) || empty($password_hash)) {
      $error[] = 'Please Enter username and password';
  } else {
      $result = pg_query_params($con, "SELECT * FROM users WHERE username = $1", array($username));
      if (pg_num_rows($result) != 0) {
          $data = pg_fetch_array($result);
          if (password_verify($password_hash, $data['password_hash'])) {
              $_SESSION['user_id'] = $data['user_id'];
              $_SESSION['username'] = $data['username'];
              header("Location: index.php");
              exit();
          } else {
              $error[] = 'Wrong password';
          }
      } else {
          $error[] = 'Username cannot found';
      }
  }
}

// Cek signup
if (isset($_POST['signup'])) {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $default_profile_picture = 'uploads/default_profile_picture.jpg';

  if (empty($username) || empty($email) || empty($password)) {
      $error[] = 'Please fill all columns';
  } else {
      $result = pg_query_params($con, "SELECT * FROM users WHERE username = $1 OR email = $2", array($username, $email));
      if (pg_num_rows($result) > 0) {
          $error[] = 'Username or Email already exist';
      } else {
          $hashed_password = password_hash($password, PASSWORD_DEFAULT);
          $query = pg_query_params($con, "INSERT INTO users (username, email, password_hash, profile_picture) VALUES ($1, $2, $3, $4)", array($username, $email, $hashed_password, $default_profile_picture));
          if ($query) {
              header("Location: login.php");
              exit();
          } else {
              $error[] = 'Something wrong. Try Again.';
          }
      }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/style.css" />
  <title>WikiTrip</title>
</head>

<body>
  <div class="auth-page-container">
    <?php
        if(isset($error)){
            foreach($error as $error){
                echo '<div class="alert alert-danger" role="alert" style="text-align: right;">'.$error.'</div>';
            };
        };
    ?>
    <div class="forms-container">
      <div class="signin-signup">
        <form action="" method="POST" class="sign-in-form form-auth">
          <div class="logo-container">
            <img src="image/logo_wikitrip.png" alt="WikiTrip Logo" class="auth-logo" />
            <h1 class="auth-brand">
              <span class="wiki">WIKI</span><span class="trip">TRIP</span>
            </h1>
          </div>
          <h2 class="title auth-title">Sign in</h2>
          <div class="input-field auth-input-field">
            <i class="bi bi-person auth-icon"></i>
            <input type="text" class="auth-input" placeholder="Username" name="username" />
          </div>
          <div class="input-field auth-input-field">
            <i class="bi bi-lock auth-icon"></i>
            <input type="password" class="auth-input" id="sign-in-password" placeholder="Password" name="password" />
            <button type="button" class="toggle-password"
              onclick="togglePassword('sign-in-password', 'sign-in-toggle-icon')">
              <i class="bi bi-eye" id="sign-in-toggle-icon"></i>
            </button>
          </div>
          <input type="submit" value="Login" name="cek_login" class="btn solid auth-btn" />
        </form>

        <form action="" method="POST" class="sign-up-form form-auth">
          <div class="logo-container">
            <img src="image/logo_wikitrip.png" alt="WikiTrip Logo" class="auth-logo" />
            <h1 class="auth-brand">
              <span class="wiki">Wiki</span><span class="trip">Trip</span>
            </h1>
          </div>
          <h2 class="title auth-title">Sign up</h2>
          <div class="input-field auth-input-field">
            <i class="bi bi-person auth-icon"></i>
            <input type="text" class="auth-input" placeholder="Username" name="username"/>
          </div>
          <div class="input-field auth-input-field">
            <i class="bi bi-envelope auth-icon"></i>
            <input type="email" class="auth-input" placeholder="Email" name="email" />
          </div>
          <div class="input-field auth-input-field">
            <i class="bi bi-lock auth-icon"></i>
            <input type="password" class="auth-input" id="sign-up-password" placeholder="Password" name="password" />
            <button type="button" class="toggle-password"
              onclick="togglePassword('sign-up-password', 'sign-up-toggle-icon')">
              <i class="bi bi-eye" id="sign-up-toggle-icon"></i>
            </button>
          </div>
          <input type="submit" class="btn auth-btn" name="signup" value="Register" />
        </form>
      </div>
    </div>
    <div class="panels-container auth-panels">
      <div class="panel left-panel auth-panel">
        <div class="content auth-content">
          <h3 class="auth-panel-title">First time here? </h3>
          <p class="auth-panel-text">
            Join WikiTrip and begin your journey to endless destinations!
          </p>
          <button class="btn transparent auth-panel-btn" id="sign-up-btn">
            Sign up
          </button>
        </div>
        <img src="image/login.svg" class="image" alt="" />
      </div>
      <div class="panel right-panel auth-panel">
        <div class="content auth-content">
          <h3 class="auth-panel-title">Back for more? </h3>
          <p class="auth-panel-text">
            Sign in and let's keep exploring North Sumatera together!
          </p>
          <button class="btn transparent auth-panel-btn" id="sign-in-btn">
            Sign in
          </button>
        </div>
        <img src="image/register.svg" class="image" alt="" />
      </div>
    </div>
  </div>

  <script src="js/login.js"></script>
</body>

</html>