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

// Cloudflare API key and Zone ID
$apiKey = $_ENV['CLOUDFLARE_API_KEY'];
$zoneId = $_ENV['CLOUDFLARE_ZONE_ID'];

// Cloudflare GraphQL API endpoint for analytics
$apiEndpoint = "https://api.cloudflare.com/client/v4/graphql";

$date_lt = date('Y-m-d');

// GraphQL query for analytics
$query = <<<GRAPHQL
query { viewer { zones(filter: { zoneTag: "$zoneId" }) { httpRequests1dGroups(filter: { date: "$date_lt" }, limit: 10000) { uniq { uniques }}}}}
GRAPHQL;

// Set up the cURL request for GraphQL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
));

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Decode the JSON response
$data = json_decode($response, true);

// Check if the necessary data is present
if (isset($data['data']['viewer']['zones'][0]['httpRequests1dGroups'][0]['uniq']['uniques'])) {
    // Get the total visitors count
    $totalVisitors = $data['data']['viewer']['zones'][0]['httpRequests1dGroups'][0]['uniq']['uniques'];
} else {
    // Set a default value or handle the case when the data is not present
    $totalVisitors = 'N/A';
}

// Close cURL session
curl_close($ch);

// intialize variables
$totalApplicants = 0;
$totalCompany = 0;
$totalVerifiedCompany = 0;
$totalNOTVerifiedCompany = 0;
$totalJobPostings = 0;
$totalApplication = 0;
$totalPendingApplication = 0;
$totalAcceptedApplication = 0;
$totalRejectedApplication = 0;

// get total applicants
$sql = "SELECT COUNT(*) AS total FROM users WHERE user_type = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_type);
$user_type = 'applicant';
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing query: " . mysqli_error($conn));
}
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalApplicants = $row['total'];
}

// get total company
$sql = "SELECT COUNT(*) AS total FROM users WHERE user_type = 'company'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalCompany = $row['total'];
}

// get total verified company
$sql = "SELECT COUNT(*) AS total FROM users WHERE user_type = 'company' AND company_verified = 1";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalVerifiedCompany = $row['total'];
}

// get total not verified company
$sql = "SELECT COUNT(*) AS total FROM users WHERE user_type = 'company' AND company_verified = 0";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalNOTVerifiedCompany = $row['total'];
}

// get total job postings
$sql = "SELECT COUNT(*) AS total FROM job_listing";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalJobPostings = $row['total'];
}

// get total application
$sql = "SELECT COUNT(*) AS total FROM job_applications";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalApplication = $row['total'];
}

// get total pending application
$sql = "SELECT COUNT(*) AS total FROM job_applications WHERE application_status = '0'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalPendingApplication = $row['total'];
}

// get total accepted application
$sql = "SELECT COUNT(*) AS total FROM job_applications WHERE application_status = '1'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalAcceptedApplication = $row['total'];
    if ($totalApplication > 0) {
        $totalAcceptedApplication = $totalAcceptedApplication / $totalApplication * 100;
    }
}

// get total rejected application
$sql = "SELECT COUNT(*) AS total FROM job_applications WHERE application_status = '2'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $totalRejectedApplication = $row['total'];
    if ($totalApplication > 0) {
        $totalRejectedApplication = $totalRejectedApplication / $totalApplication * 100;
    }
}


?>

<html>

<head>
    <title>Admin - Dashboard</title>
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
        <div class="row row-cols-1 row-cols-md-3 g-4">
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
                ['% of Accepted Application', $totalAcceptedApplication.'%', 'Total percentage of accepted applicants.'],
                ['% of Rejected Application', $totalRejectedApplication.'%', 'Total percentage of rejected applicants.']
            ];
            ?>

            <?php foreach ($cards as $card) : ?>
                <div class="col-md-4">
                    <div class="card mb-4 h-100 text-bg-dark">
                        <div class="card-body">
                            <h1 class="card-title mb-2 text-center"><?php echo $card[1]; ?></h1>
                            <hr>
                            <h6 class="card-subtitle"><?php echo $card[0]; ?></h6>
                            <p class="card-text"><?php echo $card[2]; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <br>
    </div>
</body>

</html>