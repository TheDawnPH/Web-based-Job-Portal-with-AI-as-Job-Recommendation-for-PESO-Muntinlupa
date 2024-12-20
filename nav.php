<?php

$login = "<a class='nav-link' href='/login.php'>Login/Register</a>";
$profile = "<a class='nav-link' href='/profile.php'>Profile</a>";
$logout = "<a class='nav-link' onclick='return logout();' href='/logout.php'>Logout</a>";
$applications = "<a class='nav-link' href='/job_applications.php'> Job Applications</a>";
?>

<nav class="navbar navbar-expand-lg no-print sticky-top" data-bs-theme="light" style="background-color: white;">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php" style="color:#003595 !important;">
      <img src="/img/peso_muntinlupa.png" alt="PESO Muntinlupa Logo" width="50" class="d-inline-block align-text-top">
    </a>
    <span class="d-none d-lg-block" style="color:#003595 !important;">PESO Muntinlupa Job Portal</span>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav" style="font-size: 18px;">
        <li class="nav-item">
          <a class="nav-link" href="/index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/jobs.php">Jobs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/typingtest.php">Typing Test</a>
        </li>
        <li class="nav-item">
          <?php
          if (isset($_SESSION['loggedin'])) {
            echo $profile;
          }
          ?>
        </li>
        <li class="nav-item">
          <?php
          if (isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'applicant') {
            echo $applications;
          }
          ?>
        </li>
        <?php
        // Check if the user is logged in and is of type 'company'
        if (isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'company' || isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'admin') {
        ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Company Tools
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/company/add_job_applications.php">Add Job Listing</a></li>
              <li><a class="dropdown-item" href="/company/job_listings.php">View Job Listing</a></li>
              <li><a class="dropdown-item" href="/company/job_applicants.php">View Job Applicants</a></li>
              <li><a class="dropdown-item" href="/company/job_applications_reports.php">Job Application Reports</a></li>
            </ul>
          </li>
        <?php
        }
        ?>
        <?php
        // Check if the user is logged in and is of type 'admin'
        if (isset($_SESSION['loggedin']) && $_SESSION['user_type'] === 'admin') {
        ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Admin Tools
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/admin/dashboard.php">Dashboard</a></li>
              <li><a class="dropdown-item" href="/admin/job_repository.php">Job Listing Repository</a></li>
              <li><a class="dropdown-item" href="/admin/job_applications_reports.php">Job Application Reports</a></li>
              <li><a class="dropdown-item" href="/admin/request_company.php">Verify Company Verification</a></li>
              <li><a class="dropdown-item" href="/admin/users.php">Users</a></li>
            </ul>
          </li>
        <?php
        }
        ?>
        <li class="nav-item">
          <script>
            function logout() {
              return confirm("Are you sure to logout?");
            }
          </script>
          <?php
          if (isset($_SESSION['loggedin'])) {
            echo $logout;
          } else {
            echo $login;
          }
          ?>
        </li>
      </ul>
    </div>
  </div>
</nav>