<?php
session_start();

require_once "/config.php";

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    header("location: 404.php");
    exit;
}

// Establish a PDO connection
try {
    $conn = new PDO("mysql:host=localhost;dbname=peso_db", "root", "");
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}


// Fetch applicants from the database
$sql = "SELECT * FROM job_applications";
$stmt = $conn->query($sql);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant List</title>
</head>
<body>
    <h2>Applicant List</h2>
    
    <table border="1">
        <tr>
            <th>Applicant ID</th>
            <th>Applicant Name</th>
            <th>Application Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($applicants as $applicant): ?>
            <tr>
                <td><?php echo $applicant['app_id']; ?></td>
                <td><?php echo $applicant['applicant_name']; ?></td>
                <td><?php echo $applicant['application_status']; ?></td>
                <td>
                    <a href="accept_applicant.php?app_id=<?php echo $applicant['app_id']; ?>">Accept</a> |
                    <a href="reject_applicant.php?app_id=<?php echo $applicant['app_id']; ?>">Reject</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    
</body>
</html>

<?php
// Close the database connection
$conn = null;
?>
