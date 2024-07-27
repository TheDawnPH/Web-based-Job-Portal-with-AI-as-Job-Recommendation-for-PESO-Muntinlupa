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
        } else {
            $_ENV[$line] = null;
        }
    }

    return true;
}

// Load environment variables from .env file
// if loadEnv is already loaded, don't load it again
if (!isset($_ENV['DB_HOST'])) {
    loadEnv();
}

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

// Database connection
define('DB_SERVER', $_ENV['DB_HOST']);
define('DB_USERNAME', $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_PORT', $_ENV['DB_PORT']);

// Get connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
} else {
    // job industry
    $jinindustry = "CREATE TABLE IF NOT EXISTS jinindustry (
        jinindustry_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        jinindustry_name text NOT NULL
    )";
    if (mysqli_query($conn, $jinindustry)) {
        //echo "Table jinindustry created successfully.";
    } else {
        echo "ERROR: Could not able to execute $jinindustry. " . mysqli_error($conn);
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
        user_password text NOT NULL,
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
        jinindustry_id INT(255),
        sex VARCHAR(255),
        profile_image text,
        company_verified INT(1) DEFAULT 0,
        wpm INT(3),
        company_name VARCHAR(255),
        company_position VARCHAR(255),
        FOREIGN KEY (jinindustry_id) REFERENCES jinindustry(jinindustry_id))
        ";
    if (mysqli_query($conn, $user)) {
        //echo "Table users created successfully.";
    } else {
        echo "ERROR: Could not able to execute $user. " . mysqli_error($conn);
    }

    $job_listing = "CREATE TABLE IF NOT EXISTS job_listing (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        job_title VARCHAR(255) NOT NULL,
        job_description text NOT NULL,
        job_requirements text NOT NULL,
        job_salary VARCHAR(255) NOT NULL,
        job_type VARCHAR(255) NOT NULL,
        image_name text,
        jinindustry_id INT NOT NULL,
        shs_qualified INT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        visible INT(1) DEFAULT 1,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (jinindustry_id) REFERENCES jinindustry(jinindustry_id))
        ";
    if (mysqli_query($conn, $job_listing)) {
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
    if (mysqli_query($conn, $company_documents)) {
        //echo "Table company_documents created successfully.";
    } else {
        echo "ERROR: Could not able to execute $company_documents. " . mysqli_error($conn);
    }

    $job_applications = "CREATE TABLE IF NOT EXISTS job_applications (
        app_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        job_id INT NOT NULL,
        application_status INT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (job_id) REFERENCES job_listing(id)
    )";
    if (mysqli_query($conn, $job_applications)) {
        //echo "Table job_applications created successfully.";
    } else {
        echo "ERROR: Could not able to execute $job_applications. " . mysqli_error($conn);
    }


    // check if admin user is added, if not add admin user
    $sql = "SELECT * FROM users WHERE user_type = 'admin'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $admin = "INSERT INTO users (sex, user_type, fname, mname, lname, suffix, email, user_password, verification_status, verification_code, company_verified) VALUES 
    ('male','admin', 'PESO', 'Super', 'Admin', '', 'admin@muntinlupajobportal.site', '" . password_hash('PESOAdmin!', PASSWORD_DEFAULT) . "', 1, '" . md5('admin@muntinlupajobportal.site' . time()) . "', 1)";
        if (mysqli_query($conn, $admin)) {
            //echo "Admin user added successfully.";
        } else {
            echo "ERROR: Could not able to execute $admin. " . mysqli_error($conn);
        }
    }

    $sql2 = "SELECT * FROM jinindustry";
    $result2 = mysqli_query($conn, $sql2);
    if (mysqli_num_rows($result2) == 0) {
        $jinsql = "INSERT INTO jinindustry (jinindustry_name) VALUES 
    ('Accounting'),
    ('Admin'),
    ('Agriculture'),
    ('Architecture'),
    ('Arts'),
    ('Aviation'),
    ('Banking'),
    ('Beauty'),
    ('Business'),
    ('Construction'),
    ('Customer Service'),
    ('Design'),
    ('Education'),
    ('Engineering'),
    ('Finance'),
    ('Food Service'),
    ('Healthcare'),
    ('Hospitality'),
    ('Human Resources'),
    ('Information Technology'),
    ('Insurance'),
    ('Legal'),
    ('Logistics'),
    ('Maintenance'),
    ('Management'),
    ('Manufacturing'),
    ('Marketing'),
    ('Media'),
    ('Medical'),
    ('Mining'),
    ('Nursing'),
    ('Oil and Gas'),
    ('Operations'),
    ('Pharmaceutical'),
    ('Property'),
    ('Public Relations'),
    ('Purchase'),
    ('Quality Assurance'),
    ('Real Estate'),
    ('Research'),
    ('Restaurant'),
    ('Retail'),
    ('Sales'),
    ('Science'),
    ('Security'),
    ('Shipping'),
    ('Skilled Labor'),
    ('Social Work'),
    ('Sports'),
    ('Strategy'),
    ('Supply Chain'),
    ('Telecommunications'),
    ('Tourism'),
    ('Trades'),
    ('Transportation'),
    ('Travel'),
    ('Utilities'),
    ('Warehousing')
    ";
        if (mysqli_query($conn, $jinsql)) {
            //echo "Job Industry added successfully.";
        } else {
            echo "ERROR: Could not able to execute $jinsql. " . mysqli_error($conn);
        }
    }

    // mysql to get user company_verified status
    $sql = "SELECT company_verified FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $cverify = mysqli_fetch_assoc($result);
            if (!$cverify) {
                // No data returned, set default value
                $cverify = array("company_verified" => 0);
            }
        } else {
            // Error in fetching result
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        // Error in preparing statement
        echo "Error: " . mysqli_error($conn);
    }

    // implement analytics, insert table for analytics
    $analytics = "CREATE TABLE IF NOT EXISTS analytics (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        total_visitors INT NOT NULL,
        total_applicants INT NOT NULL,
        total_company INT NOT NULL,
        total_not_verified_company INT NOT NULL,
        total_verified_company INT NOT NULL,
        total_job_postings INT NOT NULL,
        total_application INT NOT NULL,
        total_pending_application INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $analytics)) {
        //echo "analytics added successfully.";
    } else {
        echo "ERROR: Could not able to execute $analytics. " . mysqli_error($conn);
    }
}
