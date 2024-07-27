<?php
$root = "c:/xampp/htdocs";

require $root . "/config.php";

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
    $totalVisitors = '0';
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

// store to analytics table
$sql = "INSERT INTO analytics (total_visitors, total_applicants, total_company, total_verified_company, total_not_verified_company, total_job_postings, total_application, total_pending_application) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssssss", $totalVisitors, $totalApplicants, $totalCompany, $totalVerifiedCompany, $totalNOTVerifiedCompany, $totalJobPostings, $totalApplication, $totalPendingApplication);
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing query: " . mysqli_error($conn));
}

// if created_at at job listing is more than 30 days, set visible to 0
$sql = "UPDATE job_listing SET visible = 0 WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
if (!mysqli_query($conn, $sql)) {
    die("Error executing query: " . mysqli_error($conn));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
exit;
?>
