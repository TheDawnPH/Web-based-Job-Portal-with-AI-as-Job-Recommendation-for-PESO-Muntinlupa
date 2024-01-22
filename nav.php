<?php
$login = "<a class='nav-link' href='/login.php'>Login/Register</a>";
$profile = "<a class='nav-link' href='/profile.php'>Profile</a>";
$logout = "<a class='nav-link' href='/logout.php'>Logout</a>";
$applications = "<a class='nav-link' href='/job_applications.php'> Job Applications</a>";

?>
<nav class="navbar navbar-expand-lg" data-bs-theme="dark" style="background-color: #000080">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
        <img src="/img/peso_muntinlupa.png" alt="PESO Muntinlupa Logo" width="30" class="d-inline-block align-text-center">&nbsp;&nbsp;Muntinlupa Job Portal
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav" style="text-align:right; font-size: 18px;">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <?php
            if(isset($_SESSION['loggedin'])){
              echo $logout;
            } else {
              echo $login;
            }
          ?>
        </li>
        <li class="nav-item">
          <?php
            if(isset($_SESSION['loggedin'])){
              echo $profile;
            }
          ?>
        </li>
        <li class="nav-item">
          <?php
            if(isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'applicant'){
              echo $applications;
            }
          ?>
        </li>
        <?php
          // Check if the user is logged in and is of type 'company'
          if(isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'company'){
        ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Company Tools
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/company/add_job_applications.php">Add Job Listing</a></li>
              <li><a class="dropdown-item" href="/company/job_listings.php">View Job Listing</a></li>
              <li><a class="dropdown-item" href="/company/job_applicants.php">View Job Applicants</a></li>
              <?php
                if($_SESSION['company_verified'] == 0){
                  echo "<li><a class='dropdown-item' href='/company/request_company_verification.php'>Request Verification</a></li>";
                }
              ?>
            </ul>
          </li>
        <?php
          }
        ?>
      </ul>
    </div>
  </div>
</nav>
