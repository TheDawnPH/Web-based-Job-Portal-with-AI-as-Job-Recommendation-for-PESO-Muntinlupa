<?php
// env function
function loadEnv($path = __DIR__)
{
    $envFile = $path . '/.env';

    if (!file_exists($envFile)) {
        return false;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }

    return true;
}

// Load environment variables from .env file
loadEnv();

// if anyone visit this page redirect to 404 page
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("location: 404.php");
    exit;
}

// mkdir and change permission upload folder in windows
$cmd = "mkdir uploads && icacls uploads /grant Everyone:F /t";
exec($cmd);

// mkdir and change permission upload folder in linux
$cmd = "mkdir uploads && chmod 777 uploads";
exec($cmd);

date_default_timezone_set('Asia/Manila');

// website url
$website = $_ENV['WEBSITE_URL'];

// smtp email
$smtp_host = $_ENV['SMTP_HOST'];
$smtp_port = $_ENV['SMTP_PORT'];
$smtp_username = $_ENV['SMTP_USER'];
$smtp_password = $_ENV['SMTP_PASS'];
$email_admin = $_ENV['SMTP_EMAIL'];


// Database connection
define('DB_SERVER', $_ENV['DB_HOST']);
define('DB_USERNAME', $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_PORT', $_ENV['DB_PORT']);

// Get connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
} else {
    $cscategory = "CREATE TABLE IF NOT EXISTS cscategory (
        cscategory_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        cscategory_name VARCHAR(255) NOT NULL
    )";
    if(mysqli_query($conn, $cscategory)){
        //echo "Table cscategory created successfully.";
    } else {
        echo "ERROR: Could not able to execute $cscategory. " . mysqli_error($conn);
    }
    
    $twentyfirstCat = "CREATE TABLE IF NOT EXISTS twentyfirstCat (
        twentyfirstCat_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        twentyfirstCat_name VARCHAR(255) NOT NULL
    )";
    if(mysqli_query($conn, $twentyfirstCat)){
        //echo "Table twentyfirstCat created successfully.";
    } else {
        echo "ERROR: Could not able to execute $twentyfirstCat. " . mysqli_error($conn);
    }

    $tswo = "CREATE TABLE IF NOT EXISTS tswo (
        tswo_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        tswocategory_name VARCHAR(255) NOT NULL
    )";
    if(mysqli_query($conn, $tswo)){
        //echo "Table tswo created successfully.";
    } else {
        echo "ERROR: Could not able to execute $tswo. " . mysqli_error($conn);
    }

    $jinindustry = "CREATE TABLE IF NOT EXISTS jinindustry (
        jinindustry_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        jinindustry_name VARCHAR(255) NOT NULL,
        cscategory_id INT NOT NULL,
        FOREIGN KEY (cscategory_id) REFERENCES cscategory(cscategory_id)
    )";
    if(mysqli_query($conn, $jinindustry)){
        //echo "Table jinindustry created successfully.";
    } else {
        echo "ERROR: Could not able to execute $jinindustry. " . mysqli_error($conn);
    }

    $jocc = "CREATE TABLE IF NOT EXISTS jocc (
        jocc_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        jobocc_name VARCHAR(255) NOT NULL,
        tswo_id INT NOT NULL,
        twentyfirstCat_id INT NOT NULL,
        FOREIGN KEY (tswo_id) REFERENCES tswo(tswo_id),
        FOREIGN KEY (twentyfirstCat_id) REFERENCES twentyfirstCat(twentyfirstCat_id)
    )";
    if(mysqli_query($conn, $jocc)){
        //echo "Table jocc created successfully.";
    } else {
        echo "ERROR: Could not able to execute $jocc. " . mysqli_error($conn);
    }

    // create user table if not exists
    $user = "CREATE TABLE IF NOT EXISTS users (
        user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_type VARCHAR(255) NOT NULL,
        fname VARCHAR(255) NOT NULL,
        mname VARCHAR(255) NOT NULL,
        lname VARCHAR(255) NOT NULL,
        suffix VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        user_password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        verification_status INT(1) DEFAULT 0,
        verification_code VARCHAR(255) NOT NULL,
        nsrp_form text, 
        biodata_form text,
        birth_day INT(2),
        birth_month INT(2),
        birth_year INT(4),
        contact_number VARCHAR(255),
        house_number VARCHAR(255),
        street VARCHAR(255),
        subdivision VARCHAR(255),
        barangay VARCHAR(255),
        city VARCHAR(255),
        province VARCHAR(255),
        zip_code INT(4),
        school_name VARCHAR(255),
        school_year_begin INT(4),
        school_year_end INT(4),
        technicalschool_name VARCHAR(255),
        cscategory_id INT(255),
        sex VARCHAR(255),
        twentyfirstCat_id INT(255),
        tswo_id INT(255),
        profile_image text,
        company_verified INT(1) DEFAULT 0,
        FOREIGN KEY (cscategory_id) REFERENCES cscategory(cscategory_id),
        FOREIGN KEY (twentyfirstCat_id) REFERENCES twentyfirstCat(twentyfirstCat_id),
        FOREIGN KEY (tswo_id) REFERENCES tswo(tswo_id)
    )";
    if(mysqli_query($conn, $user)){
        //echo "Table users created successfully.";
    } else {
        echo "ERROR: Could not able to execute $user. " . mysqli_error($conn);
    }

    $job_listing = "CREATE TABLE IF NOT EXISTS job_listing (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        job_title VARCHAR(255) NOT NULL,
        job_description text NOT NULL,
        job_salary VARCHAR(255) NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        jinindustry_id INT NOT NULL,
        jocc_id INT NOT NULL,
        shs_qualified INT(1) DEFAULT 0,
        job_posted INT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (jinindustry_id) REFERENCES jinindustry(jinindustry_id),
        FOREIGN KEY (jocc_id) REFERENCES jocc(jocc_id)
    )";
    if(mysqli_query($conn, $job_listing)){
        //echo "Table job_listing created successfully.";
    } else {
        echo "ERROR: Could not able to execute $job_listing. " . mysqli_error($conn);
    }

    $company_documents = "CREATE TABLE IF NOT EXISTS company_documents (
        cdocu_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        loi text,
        cp text,
        sec_accredit text,
        cda_license text,
        dole_license text,
        loc text,
        mbpermit text,
        job_vacant text,
        job_solicitation text,
        phjobnet_reg text,
        cert_nopendingcase text,
        cert_regSSS text,
        cert_regPhHealth text,
        cert_regPGIBG text,
        sketch_map text,
        2303_bir text,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    if(mysqli_query($conn, $company_documents)){
        //echo "Table company_documents created successfully.";
    } else {
        echo "ERROR: Could not able to execute $company_documents. " . mysqli_error($conn);
    }

    $job_applications = " CREATE TABLE IF NOT EXISTS job_applications (
        app_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        job_id INT NOT NULL,
        application_status INT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (job_id) REFERENCES job_listing(id)
    )";



}
?>