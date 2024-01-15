<?php
// if user not logged in, show this a tag
$login = "<a class='nav-link' href='login.php'>Login/Register</a>";
$profile = "<a class='nav-link' href='profile.php'>Profile</a>";
$logout = "<a class='nav-link' href='logout.php'>Logout</a>";
$applications = "<a class='nav-link' href='job_applications.php'> Job Applications</a>";

?>
<nav class="navbar navbar-expand-lg" data-bs-theme="dark" style="background-color: #000080">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
        <img src="img/peso_muntinlupa.png" alt="PESO Muntinlupa Logo" width="30" class="d-inline-block align-text-center">&nbsp;&nbsp;Muntinlupa Job Portal
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav" style="text-align:right; font-size: 18px;">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <!--- <li class="nav-item">
          <a class="nav-link" href="about.php">About</a>
        </li> --->
        <li class="nav-item">
          <?php
            if(isset($_SESSION['logged_in'])){
              echo $logout;
            } else {
              echo $login;
            }
          ?>
        </li>
        <li class="nav-item">
          <?php
            if(isset($_SESSION['logged_in'])){
              echo $profile;
            }
          ?>
        </li>
        <li class="nav-item">
          <?php
            if(isset($_SESSION['logged_in'])){
              echo $applications;
            }
          ?>
        </li>
      </ul>
    </div>
  </div>
</nav>