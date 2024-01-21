<?php

//require_once "config.php";

session_start();


// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "peso_db";


// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


 // Retrieve data from the form
 $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
 $job_title = isset($_POST['job_title']) ? $_POST['job_title'] : '';
 $job_description = isset($_POST['job_description']) ? $_POST['job_description'] : '';
 $job_salary = isset($_POST['job_salary']) ? $_POST['job_salary'] : '';
 $image_name = isset($_POST['image_name']) ? $_POST['image_name'] : '';
 $jinindustry_id = isset($_POST['jinindustry_id']) ? $_POST['jinindustry_id'] : '';
 $jocc_id = isset($_POST['jocc_id']) ? $_POST['jocc_id'] : '';
 $shs_qualified = isset($_POST['shs_qualified']) ? $_POST['shs_qualified'] : '';
 $job_posted = isset($_POST['job_posted']) ? $_POST['job_posted'] : '';
 $created_at = isset($_POST['created_at']) ? $_POST['created_at'] : '';

 $sql = "INSERT INTO job_listing ( user_id, job_title, job_description, job_salary, image_name, jinindustry_id, jocc_id, shs_qualified, job_posted, created_at) 
       VALUES ( '$user_id', '$job_title', '$job_description', '$job_salary', '$image_name', '$jinindustry_id', '$jocc_id', '$shs_qualified', '$job_posted', '$created_at')";


if ($conn->query($sql) === TRUE) {
    echo "Job Listing inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
// Close the database connection
$conn->close();
?>
