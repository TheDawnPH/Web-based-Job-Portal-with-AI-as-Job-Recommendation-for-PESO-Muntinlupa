<?php

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// if user is not logged in redirect to login page
if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    header("location: /login.php");
    exit;
}

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}


?>

<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="/css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <br>
        <!-- <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $cards = [
                ['Total User Count', $totalVisitors, 'Total number of users visiting the site.'],
                ['Total Applicant Users', $totalApplicants, 'Total number of applicants in the system.'],
                ['Total Company Users', $totalCompany, 'Total number of company users in the system.'],
                ['Total Verified Company Users', $totalVerifiedCompany, 'Total number of verified company users.'],
                ['Total Not Verified Company Users', $totalNOTVerifiedCompany, 'Total number of not verified company users.'],
                ['Total Job Posting', $totalJobPostings, 'Total number of job postings in the system.'],
                ['Total Application', $totalApplication, 'Total number of applications in the system.'],
                ['Total Pending Applications', $totalPendingApplication, 'Total number of pending applications in the system.'],
                ['% of Accepted Application', round($totalAcceptedApplication) . '%', 'Total percentage of accepted applicants.'],
                ['% of Rejected Application', round($totalRejectedApplication) . '%', 'Total percentage of rejected applicants.']
            ];
            ?>

            <?php foreach ($cards as $card) : ?>
                <div class="col-md-4">
                    <div class="card mb-4 h-100 text-bg-dark">
                        <div class="card-body">
                            <h3 class="card-title mb-2 text-center"><?php echo $card[1]; ?></h3>
                            <hr>
                            <h6 class="card-subtitle"><?php echo $card[0]; ?></h6>
                            <p class="card-text"><?php echo $card[2]; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div> -->
        <!-- create a graph per group from analytics table -->
        <div class="row">
            <div class="col-md-12">
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Visitors</h3>
                                <canvas id="chart_totalvisitors"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Applicants</h3>
                                <canvas id="chart_totalapplicants"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Company</h3>
                                <canvas id="chart_totalcompany"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Verified Company</h3>
                                <canvas id="chart_totalverifiedcompany"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Not Verified Company</h3>
                                <canvas id="chart_totalnotverifiedcompany"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Job Postings</h3>
                                <canvas id="chart_totaljobpostings"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Application</h3>
                                <canvas id="chart_totalapplication"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4 h-100 text-bg-dark">
                            <div class="card-body">
                                <h3 class="card-title mb-2 text-center">Total Pending Application</h3>
                                <canvas id="chart_totalpendingapplication"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $sql = "SELECT * FROM analytics";
                $result = mysqli_query($conn, $sql);
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $total_visitors = array_slice(array_column($data, 'total_visitors'), -10);
                $total_applicants = array_slice(array_column($data, 'total_applicants'), -10);
                $total_company = array_slice(array_column($data, 'total_company'), -10);
                $total_verified_company = array_slice(array_column($data, 'total_verified_company'), -10);
                $total_not_verified_company = array_slice(array_column($data, 'total_not_verified_company'), -10);
                $total_job_postings = array_slice(array_column($data, 'total_job_postings'), -10);
                $total_application = array_slice(array_column($data, 'total_application'), -10);
                $total_pending_application = array_slice(array_column($data, 'total_pending_application'), -10);
                $created_at = array_slice(array_map(function ($time) {
                    return date("m/d g:i a", strtotime($time));
                }, array_column($data, 'created_at')), -10);
                ?>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    var ctx = document.getElementById('chart_totalvisitors').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Visitors',
                                data: <?php echo json_encode($total_visitors); ?>,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalapplicants').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Applicants',
                                data: <?php echo json_encode($total_applicants); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalcompany').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Company',
                                data: <?php echo json_encode($total_company); ?>,
                                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalverifiedcompany').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Verified Company',
                                data: <?php echo json_encode($total_verified_company); ?>,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalnotverifiedcompany').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Not Verified Company',
                                data: <?php echo json_encode($total_not_verified_company); ?>,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totaljobpostings').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Job Postings',
                                data: <?php echo json_encode($total_job_postings); ?>,
                                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalapplication').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Application',
                                data: <?php echo json_encode($total_application); ?>,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    var ctx = document.getElementById('chart_totalpendingapplication').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($created_at); ?>,
                            datasets: [{
                                label: 'Total Pending Application',
                                data: <?php echo json_encode($total_pending_application); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                </script>

            </div>
            <br>
        </div>
</body>

</html>