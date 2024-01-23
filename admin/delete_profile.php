<?php
// Establish a PDO connection 
try {
    $conn = new PDO("mysql:host=localhost;dbname=peso_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Check if user_id is set in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Prepare a SQL statement to delete the user
    $sql = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    
    // Bind the parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "User with ID $user_id has been deleted.";
    } else {
        echo "Error deleting user.";
    }
} else {
    echo "Invalid user ID.";
}

// Close the database connection
$conn = null;
?>
