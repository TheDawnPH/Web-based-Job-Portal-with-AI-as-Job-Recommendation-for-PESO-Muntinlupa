<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Generate a CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require "config.php";

$user_type = $fname = $mname = $lname = $suffix = $email = $password = $confirm_password = "";
$user_type_err = $fname_err = $mname_err = $lname_err = $suffix_err = $email_err = $password_err = $confirm_password_err = "";
$company_name = $company_position = "";

function isEmailExists($conn, $email)
{
    $sql = "SELECT user_id FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $result = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        return $result > 0;
    }
    return false;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "CSRF Token is invalid, This page is doomed.";
        die();
    }

    if (empty(trim($_POST["user_type"]))) {
        $user_type_err = "Please select registration type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter first name.";
    } else {
        $fname = trim($_POST["fname"]);
    }

    $mname = trim($_POST["mname"]);

    if (empty(trim($_POST["lname"]))) {
        $lname_err = "Please enter last name.";
    } else {
        $lname = trim($_POST["lname"]);
    }

    $suffix = trim($_POST["suffix"]);


    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if ($user_type === 'company') {
        if (empty(trim($_POST["companyname"]))) {
            $company_name = "N/A";
        } else {
            $company_name = trim($_POST["companyname"]);
        }

        if (empty(trim($_POST["companyposition"]))) {
            $company_position = "N/A";
        } else {
            $company_position = trim($_POST["companyposition"]);
        }
    }

    // check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Verify reCAPTCHA response
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $warning = "Please complete the reCAPTCHA challenge.";
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $_ENV['SECRET_KEY'] . '&response=' . $_POST['g-recaptcha-response']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        if (!$response->success) {
            $warning = "reCAPTCHA verification failed. Please try again.";
        }
    }

    if (empty($email_err) && isEmailExists($conn, $email)) {
        $email_err = "Email already exists.";
    }

    function generateVerificationCode()
    {
        return bin2hex(random_bytes(16)); // Generates a random 32-character string
    }

    if (empty($user_type_err) && empty($fname_err) && empty($mname_err) && empty($lname_err) && empty($suffix_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($warning)) {
        $sql = "INSERT INTO users (user_type, fname, mname, lname, suffix, email, user_password, verification_code, company_name, company_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $param_user_type, $param_fname, $param_mname, $param_lname, $param_suffix, $param_email, $param_password, $param_verification_code, $param_company_name, $param_company_position);

            $param_user_type = $user_type;
            $param_fname = $fname;
            $param_mname = $mname;
            $param_lname = $lname;
            $param_suffix = $suffix;
            $param_email = $email;
            $param_company_name = $company_name;
            $param_company_position = $company_position;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_verification_code = generateVerificationCode();

            if (mysqli_stmt_execute($stmt)) {
                // phpmailer
                require 'PHPMailer/src/Exception.php';
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';
                loadEnv();

                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Mailer = "smtp";
                $mail->SMTPDebug  = 0;
                $mail->SMTPAuth   = TRUE;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $_ENV['SMTP_PORT'];
                $mail->Host       = $_ENV['SMTP_HOST'];
                $mail->Username   = $_ENV['SMTP_USER']; // email address
                $mail->Password   = $_ENV['SMTP_PASS']; // password
                $mail->IsHTML(true);
                $mail->AddAddress($email, $fname . " " . $lname);
                $mail->SetFrom($_ENV['SMTP_EMAIL'], "PESO Muntinlupa");
                $mail->Subject = "PESO Muntinlupa Job Portal - Email Verification";
                $content = "<b>Hi " . $fname . " " . $lname . ",</b><br><br>";
                $content .= "Please click the link below to verify your email address.<br><br>";
                $content .= "<a href='https://" . $website . "/verify.php?code=$param_verification_code'>Verify Email</a><br><br>";
                $content .= "Thank you!<br>";
                $content .= "PESO Muntinlupa";
                $mail->MsgHTML($content);
                if (!$mail->Send()) {
                    $warning = "Error while sending Email.";
                    //var_dump($mail);
                } else {
                    $alert = "Please check your email for the verification link.";
                }
                // end of phpmailer

                $alert = "Please check your email for the verification link.";
            } else {
                $warning = "Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}

?>

<html>

<head>
    <title>Register - PESO Muntinlupa Job Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
    <script>
        // show privacy modal once
        $(document).ready(function() {
            if (localStorage.getItem('privacyPopup') !== 'shown') {
                $('.PrivacyPopup').modal('show');
                localStorage.setItem('privacyPopup', 'shown');
            }
        });
    </script>
</head>

<body>
    <div class="container">
        <a href="login.php" class="btn btn-secondary">Back to Login</a><br><br>
        <div class="row g-0">
            <div class="col-md">
                <img src="img/peso_muntinlupa.png" alt="PESO Logo" class="img-fluid mx-auto d-block">
            </div>
            <div class="col-md">
                <br>
                <h1>Register</h1>
                <br>
                <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg" class="img-fluid" alt="Responsive image">
                <br><br>
                <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"]), ENT_QUOTES); ?>" method="post">
                    <?php
                    if (!empty($alert)) {
                        echo '<div class="alert alert-success">' . $alert . '</div>';
                    } elseif (!empty($warning)) {
                        echo '<div class="alert alert-danger">' . $warning . '</div>';
                    } elseif (!empty($user_type_err)) {
                        echo '<div class="alert alert-danger">' . $user_type_err . '</div>';
                    } elseif (!empty($fname_err)) {
                        echo '<div class="alert alert-danger">' . $fname_err . '</div>';
                    } elseif (!empty($mname_err)) {
                        echo '<div class="alert alert-danger">' . $mname_err . '</div>';
                    } elseif (!empty($lname_err)) {
                        echo '<div class="alert alert-danger">' . $lname_err . '</div>';
                    } elseif (!empty($suffix_err)) {
                        echo '<div class="alert alert-danger">' . $suffix_err . '</div>';
                    } elseif (!empty($email_err)) {
                        echo '<div class="alert alert-danger">' . $email_err . '</div>';
                    } elseif (!empty($password_err)) {
                        echo '<div class="alert alert-danger">' . $password_err . '</div>';
                    } elseif (!empty($confirm_password_err)) {
                        echo '<div class="alert alert-danger">' . $confirm_password_err . '</div>';
                    }
                    ?>
                    <div class="mb-4">
                        <div class="form-text">Already have an account? <a href="login.php">Login here</a>.</div>
                    </div>
                    <div class="mb-4">
                        <label for="user_type" class="form-label">Registration Type</label>
                        <select class="form-select" name="user_type" id="user_type">
                            <option value="applicant">As Applicant</option>
                            <option value="company">As Company</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" name="fname" class="form-control" id="fname" aria-describedby="fnameHelp" required>
                    </div>
                    <div class="mb-4">
                        <label for="mname" class="form-label">Middle Name</label>
                        <input type="text" name="mname" class="form-control" id="mname" aria-describedby="mnameHelp">
                        <div class="form-text">Leave blank if not applicable.</div>
                    </div>
                    <div class="mb-4">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" name="lname" class="form-control" id="lname" aria-describedby="lnameHelp" required>
                    </div>
                    <div class="mb-4">
                        <label for="suffix" class="form-label">Suffix</label>
                        <input type="text" name="suffix" class="form-control" id="suffix" aria-describedby="suffixHelp">
                        <div class="form-text">Leave blank if not applicable.</div>
                    </div>
                    <div class="mb-4" id="companyname">
                        <label for="companyname" class="form-label">Company Name</label>
                        <input type="text" name="companyname" class="form-control" id="companyname" aria-describedby="companynameHelp">
                    </div>
                    <div class="mb-4" id="companypos">
                        <label for="companyposition" class="form-label">Position</label>
                        <input type="text" name="companyposition" class="form-control" id="companyposition" aria-describedby="companypositionHelp">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" id="email" aria-describedby="emailHelp" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" aria-describedby="confirm_passwordHelp" required>
                    </div>
                    <div class="mb-4">
                        <div class="form-text">By Registering yourself in this website, you agree on <a href="" data-bs-toggle="modal" data-bs-target=".PrivacyPopup">Data Privacy Clause and Privacy Notice</a>.</div>
                    </div>
                    <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['SITE_KEY']; ?>"></div>
                    <br>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="submit" class="btn btn-primary" value="Register">
                    <script>
                        document.getElementById('companyname').style.display = 'none';
                        document.getElementById('user_type').addEventListener('change', function() {
                            if (this.value === 'company') {
                                document.getElementById('companyname').style.display = 'block';
                            } else {
                                document.getElementById('companyname').style.display = 'none';
                            }
                        });

                        document.getElementById('companypos').style.display = 'none';
                        document.getElementById('user_type').addEventListener('change', function() {
                            if (this.value === 'company') {
                                document.getElementById('companypos').style.display = 'block';
                            } else {
                                document.getElementById('companypos').style.display = 'none';
                            }
                        });
                    </script>
                </form>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade modal-lg PrivacyPopup" id="PrivacyPopup" tabindex="-1" role="dialog" aria-labelledby="PrivacyPopuplabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="PrivacyPopuplabel">Data Privacy Clause and Privacy Notice</h5>
                    </div>
                    <div class="modal-body">
                        <h3>Data Privacy Clause</h3>
                        <p>This is to certify that all data/information that I have provided in this form are true to the best of my knowledge. I was informed of the nature, purpose and extent of the use of such information and my right to withdraw my consent in accordance with the <a target="_blank" href="https://privacy.gov.ph/implementing-rules-regulations-data-privacy-act-2012/">Implementing Rules and Regulations of the Data Privacy Act of 2012</a>.</p>
                        <br>
                        <h3>Privacy Notice</h3>
                        <p><b>PERSONAL DATA COLLECTED:</b><br>
                            We at the Public Employment Service Office shall collect and process the following personal data, such as but not limited to the following: (Name, address, contact information, other pertinent and relevant information for the application/requests) through (online form or physical form or whatever medium).<br><br>
                            <b>PURPOSE AND DATA USAGE:</b><br>
                            The personal data collected shall be used for the following legitimate purposes: Employment and shall only be processed to the extent necessary for compliance with the office’s legal obligation and mandates and to further the City Government’s legitimate interest.<br><br>
                            <b>STORAGE AND DISPOSAL:</b><br>
                            The data shall be stored in our (Archive/Filing Cabinets/Vault/Database/etc.) and shall be disposed of in accordance with the National Archives of the Philippines and other relevant issuances.<br><br>
                            <b>DISCLOSURE:</b><br>
                            We treat your personal information with utmost confidentiality and shall not be disclosed of nor shared with any unauthorized person, unless necessary for any other basis for lawful processing of information under the Data Privacy Act of 2012 and in adherence to the general data principles of transparency, legitimate purpose and proportionality.<br><br>
                            As our data subject, we assure you that we extend all reasonable efforts to ensure that the data collected and processed is complete, accurate and up to date. You have the right to access and to ask for a copy of your personal information, as well as to ask for its correction, erasure or blocking, and to file a complaint, if necessary.<br><br>
                            Exercise of any of the following data subject rights may be coursed through our Data Protection Officer through <a href="mailto:dpo@muntinlupacity.gov.ph">dpo@muntinlupacity.gov.ph</a>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Yes, I'm in</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>