<?php
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "company") {
    header("location: 404.php");
    exit;
}

// Establish a PDO connection (Update credentials based on your database)
try {
    $conn = new PDO("mysql:host=localhost;dbname=peso_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Get the application ID from the URL parameter
$applicationId = isset($_GET['app_id']) ? $_GET['app_id'] : null;

// Check if the parameter is set
if ($applicationId === null) {
    echo "Error: Missing 'app_id' parameter in the URL";
    exit();
}

// Update the status of the job application to 'rejected'
$sql = "UPDATE job_application SET application_status = 'rejected' WHERE app_id = :app_id";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':app_id', $applicationId);
    $stmt->execute();
    
    echo "Applicant rejected successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$conn = null;
?>
