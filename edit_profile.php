<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: login.php");
    exit;
}

// get user type and user id
$user_type = $_SESSION["user_type"];
$user_id = $_SESSION["user_id"];

// get user data from user id
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Define variables and initialize with values from row
$gender = $row["sex"];
$birth_day = $row["birth_day"];
$birth_month = $row["birth_month"];
$birth_year = $row["birth_year"];
$contact_number = $row["contact_number"];
$house_number = $row["house_number"];
$street = $row["street"];
$subdivision = $row["subdivision"];
$barangay = $row["barangay"];
$city = $row["city"];
$province = $row["province"];
$zip_code = $row["zip_code"];
$school_name = $row["school_name"];
$school_year_begin = $row["school_year_begin"];
$school_year_end = $row["school_year_end"];
$technicalschool_name = $row["technicalschool_name"];
$nsrp_form = $biodata_form = $profile_picture = "";
$loi = $cp = $sec_accredit = $cda_license = $dole_license = $loc = $mbpermit = $job_vacant = $job_solicitation = $phjobnet_reg = $cert_nopendingcase = $cert_regSSS = $cert_regPhHealth = $cert_regPGIBG = $sketch_map = $bir_2303 = "";
$jinindustry = $row["jinindustry_id"];


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // check if nsrp form and biodata is pdf or docx
    if (!empty($_FILES["profile_picture"]["name"])) {
        $image_name = $_FILES["profile_picture"]["name"];
        $image_name_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        if ($image_name_ext != "png" && $image_name_ext != "jpg" && $image_name_ext != "jpeg") {
            $error = "Image must be a png, jpg, or jpeg file.";
        }
    } elseif (!empty($_FILES["nsrp_form"]["name"])) {
        $nsrp_form = $_FILES["nsrp_form"]["name"];
        $nsrp_form_ext = pathinfo($nsrp_form, PATHINFO_EXTENSION);
        if ($nsrp_form_ext != "pdf" && $nsrp_form_ext != "docx") {
            $error = "NSRP form must be a PDF or DOCX file.";
        }
    } elseif (!empty($_FILES["biodata_form"]["name"])) {
        $biodata_form = $_FILES["biodata_form"]["name"];
        $biodata_form_ext = pathinfo($biodata_form, PATHINFO_EXTENSION);
        if ($biodata_form_ext != "pdf" && $biodata_form_ext != "docx") {
            $error = "Biodata form must be a PDF or DOCX file.";
        }
    } else {
        // Prepare SQL statement
        $sql = "UPDATE users SET sex=?, birth_day=?, birth_month=?, birth_year=?, contact_number=?, house_number=?, street=?, subdivision=?, barangay=?, city=?, province=?, zip_code=?, school_name=?, school_year_begin=IFNULL(?, school_year_begin) , school_year_end= IFNULL(?, school_year_end), technicalschool_name=?, nsrp_form = IFNULL(?, nsrp_form), biodata_form = IFNULL(?, biodata_form), profile_image = IFNULL(?, profile_image), jinindustry_id = IFNULL(?, jinindustry_id) WHERE user_id=?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssssssssssssssssss", $param_sex, $param_birth_day, $param_birth_month, $param_birth_year, $param_contact_number, $param_house_number, $param_street, $param_subdivision, $param_barangay, $param_city, $param_province, $param_zip_code, $param_school_name, $param_school_year_begin, $param_school_year_end, $param_technicalschool_name, $param_nsrp_form, $param_biodata_form, $param_profile_picture, $param_jinindustry_id, $param_user_id);

            // Set parameters
            $param_sex = $_POST["gender"];
            $param_birth_day = $_POST["birth_day"];
            $param_birth_month = $_POST["birth_month"];
            $param_birth_year = $_POST["birth_year"];
            $param_contact_number = $_POST["contact_number"];
            $param_house_number = $_POST["house_number"];
            $param_street = $_POST["street"];
            $param_subdivision = $_POST["subdivision"];
            $param_barangay = $_POST["barangay"];
            $param_city = $_POST["city"];
            $param_province = $_POST["province"];
            $param_zip_code = $_POST["zip_code"];
            $param_school_name = $_POST["school_name"];
            $param_school_year_begin = !empty($_POST["school_year_begin"]) ? $_POST["school_year_begin"] : null;
            $param_school_year_end = !empty($_POST["school_year_end"]) ? $_POST["school_year_end"] : null;
            $param_technicalschool_name = $_POST["technicalschool_name"];
            $param_nsrp_form = !empty($_FILES["nsrp_form"]["name"]) ? $_FILES["nsrp_form"]["name"] : NULL;
            $param_biodata_form = !empty($_FILES["biodata_form"]["name"]) ? $_FILES["biodata_form"]["name"] : NULL;
            $param_profile_picture = !empty($_FILES["profile_picture"]["name"]) ? $_FILES["profile_picture"]["name"] : NULL;
            $param_user_id = $user_id;
            $param_jinindustry_id = $_POST["jinindustry"];


            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Create a directory for the user if it doesn't exist
                $user_upload_dir = "uploads/" . $user_id;
                if (!is_dir($user_upload_dir)) {
                    mkdir($user_upload_dir, 0755, true);
                }


                if (!empty($_FILES["nsrp_form"]["name"])) {
                    move_uploaded_file($_FILES["nsrp_form"]["tmp_name"], $user_upload_dir . "/" . $_FILES["nsrp_form"]["name"]);
                }
                if (!empty($_FILES["biodata_form"]["name"])) {
                    move_uploaded_file($_FILES["biodata_form"]["tmp_name"], $user_upload_dir . "/" . $_FILES["biodata_form"]["name"]);
                }
                if (!empty($_FILES["profile_picture"]["name"])) {
                    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $user_upload_dir . "/" . $_FILES["profile_picture"]["name"]);
                }


                $success = "Profile updated successfully!";
            } else {
                $error =  "Error updating profile.";
            }
        }
    }

    if ($user_type === 'company') {
        // check if loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, setch_map, 2303_bir is pdf or docx
        if (!empty($_FILES["loi"]["name"])) {
            $loi = $_FILES["loi"]["name"];
            $loi_ext = pathinfo($loi, PATHINFO_EXTENSION);
            if ($loi_ext != "pdf" && $loi_ext != "docx") {
                $error = "Letter of Intent must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cp"]["name"])) {
            $cp = $_FILES["cp"]["name"];
            $cp_ext = pathinfo($cp, PATHINFO_EXTENSION);
            if ($cp_ext != "pdf" && $cp_ext != "docx") {
                $error = "Company Profile must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["sec_accredit"]["name"])) {
            $sec_accredit = $_FILES["sec_accredit"]["name"];
            $sec_accredit_ext = pathinfo($sec_accredit, PATHINFO_EXTENSION);
            if ($sec_accredit_ext != "pdf" && $sec_accredit_ext != "docx") {
                $error = "SEC Accreditation must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cda_license"]["name"])) {
            $cda_license = $_FILES["cda_license"]["name"];
            $cda_license_ext = pathinfo($cda_license, PATHINFO_EXTENSION);
            if ($cda_license_ext != "pdf" && $cda_license_ext != "docx") {
                $error = "CDA License must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["dole_license"]["name"])) {
            $dole_license = $_FILES["dole_license"]["name"];
            $dole_license_ext = pathinfo($dole_license, PATHINFO_EXTENSION);
            if ($dole_license_ext != "pdf" && $dole_license_ext != "docx") {
                $error = "DOLE License must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["loc"]["name"])) {
            $loc = $_FILES["loc"]["name"];
            $loc_ext = pathinfo($loc, PATHINFO_EXTENSION);
            if ($loc_ext != "pdf" && $loc_ext != "docx") {
                $error = "LOC must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["mbpermit"]["name"])) {
            $mbpermit = $_FILES["mbpermit"]["name"];
            $mbpermit_ext = pathinfo($mbpermit, PATHINFO_EXTENSION);
            if ($mbpermit_ext != "pdf" && $mbpermit_ext != "docx") {
                $error = "MB Permit must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["job_vacant"]["name"])) {
            $job_vacant = $_FILES["job_vacant"]["name"];
            $job_vacant_ext = pathinfo($job_vacant, PATHINFO_EXTENSION);
            if ($job_vacant_ext != "pdf" && $job_vacant_ext != "docx") {
                $error = "Job Vacant must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["job_solicitation"]["name"])) {
            $job_solicitation = $_FILES["job_solicitation"]["name"];
            $job_solicitation_ext = pathinfo($job_solicitation, PATHINFO_EXTENSION);
            if ($job_solicitation_ext != "pdf" && $job_solicitation_ext != "docx") {
                $error = "Job Solicitation must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["phjobnet_reg"]["name"])) {
            $phjobnet_reg = $_FILES["phjobnet_reg"]["name"];
            $phjobnet_reg_ext = pathinfo($phjobnet_reg, PATHINFO_EXTENSION);
            if ($phjobnet_reg_ext != "pdf" && $phjobnet_reg_ext != "docx") {
                $error = "PH Jobnet Registration must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cert_nopendingcase"]["name"])) {
            $cert_nopendingcase = $_FILES["cert_nopendingcase"]["name"];
            $cert_nopendingcase_ext = pathinfo($cert_nopendingcase, PATHINFO_EXTENSION);
            if ($cert_nopendingcase_ext != "pdf" && $cert_nopendingcase_ext != "docx") {
                $error = "Certificate of No Pending Case must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cert_regSSS"]["name"])) {
            $cert_regSSS = $_FILES["cert_regSSS"]["name"];
            $cert_regSSS_ext = pathinfo($cert_regSSS, PATHINFO_EXTENSION);
            if ($cert_regSSS_ext != "pdf" && $cert_regSSS_ext != "docx") {
                $error = "Certificate of Registration with SSS must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cert_regPhHealth"]["name"])) {
            $cert_regPhHealth = $_FILES["cert_regPhHealth"]["name"];
            $cert_regPhHealth_ext = pathinfo($cert_regPhHealth, PATHINFO_EXTENSION);
            if ($cert_regPhHealth_ext != "pdf" && $cert_regPhHealth_ext != "docx") {
                $error = "Certificate of Registration with PhilHealth must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["cert_regPGIBG"]["name"])) {
            $cert_regPGIBG = $_FILES["cert_regPGIBG"]["name"];
            $cert_regPGIBG_ext = pathinfo($cert_regPGIBG, PATHINFO_EXTENSION);
            if ($cert_regPGIBG_ext != "pdf" && $cert_regPGIBG_ext != "docx") {
                $error = "Certificate of Registration with PAG-IBIG must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["sketch_map"]["name"])) {
            $sketch_map = $_FILES["sketch_map"]["name"];
            $sketch_map_ext = pathinfo($sketch_map, PATHINFO_EXTENSION);
            if ($sketch_map_ext != "pdf" && $sketch_map_ext != "docx") {
                $error = "Sketch Map must be a PDF or DOCX file.";
            }
        } elseif (!empty($_FILES["2303_bir"]["name"])) {
            $bir_2303 = $_FILES["2303_bir"]["name"];
            $bir_2303_ext = pathinfo($bir_2303, PATHINFO_EXTENSION);
            if ($bir_2303_ext != "pdf" && $bir_2303_ext != "docx") {
                $error = "BIR 2303 must be a PDF or DOCX file.";
            }
        } else {
            // check if company documents exist in company_documents table
            $company_documents_sql = "SELECT * FROM company_documents WHERE user_id = '$user_id'";
            $company_documents_result = mysqli_query($conn, $company_documents_sql);
            $company_documents_row = mysqli_fetch_assoc($company_documents_result);

            // if company documents exist, update company_documents table
            if ($company_documents_row > 0) {
                // Prepare SQL statement
                $company_documents_sql = "UPDATE company_documents SET loi = IFNULL(?, loi), cp = IFNULL(?, cp), sec_accredit = IFNULL(?, sec_accredit), cda_license = IFNULL(?, cda_license), dole_license = IFNULL(?, dole_license), loc = IFNULL(?, loc), mbpermit = IFNULL(?, mbpermit), job_vacant = IFNULL(?, job_vacant), job_solicitation = IFNULL(?, job_solicitation), phjobnet_reg = IFNULL(?, phjobnet_reg), cert_nopendingcase = IFNULL(?, cert_nopendingcase), cert_regSSS = IFNULL(?, cert_regSSS), cert_regPhHealth = IFNULL(?, cert_regPhHealth), cert_regPGIBG = IFNULL(?, cert_regPGIBG), sketch_map = IFNULL(?, sketch_map), 2303_bir = IFNULL(?, 2303_bir) WHERE user_id = ?";

                if ($stmt = mysqli_prepare($conn, $company_documents_sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssssssssssssssss", $param_loi, $param_cp, $param_sec_accredit, $param_cda_license, $param_dole_license, $param_loc, $param_mbpermit, $param_job_vacant, $param_job_solicitation, $param_phjobnet_reg, $param_cert_nopendingcase, $param_cert_regSSS, $param_cert_regPhHealth, $param_cert_regPGIBG, $param_sketch_map, $param_2303_bir, $param_user_id);

                    // Set parameters
                    $param_loi = !empty($_FILES["loi"]["name"]) ? $_FILES["loi"]["name"] : null;
                    $param_cp = !empty($_FILES["cp"]["name"]) ? $_FILES["cp"]["name"] : null;
                    $param_sec_accredit = !empty($_FILES["sec_accredit"]["name"]) ? $_FILES["sec_accredit"]["name"] : null;
                    $param_cda_license = !empty($_FILES["cda_license"]["name"]) ? $_FILES["cda_license"]["name"] : null;
                    $param_dole_license = !empty($_FILES["dole_license"]["name"]) ? $_FILES["dole_license"]["name"] : null;
                    $param_loc = !empty($_FILES["loc"]["name"]) ? $_FILES["loc"]["name"] : null;
                    $param_mbpermit = !empty($_FILES["mbpermit"]["name"]) ? $_FILES["mbpermit"]["name"] : null;
                    $param_job_vacant = !empty($_FILES["job_vacant"]["name"]) ? $_FILES["job_vacant"]["name"] : null;
                    $param_job_solicitation = !empty($_FILES["job_solicitation"]["name"]) ? $_FILES["job_solicitation"]["name"] : null;
                    $param_phjobnet_reg = !empty($_FILES["phjobnet_reg"]["name"]) ? $_FILES["phjobnet_reg"]["name"] : null;
                    $param_cert_nopendingcase = !empty($_FILES["cert_nopendingcase"]["name"]) ? $_FILES["cert_nopendingcase"]["name"] : null;
                    $param_cert_regSSS = !empty($_FILES["cert_regSSS"]["name"]) ? $_FILES["cert_regSSS"]["name"] : null;
                    $param_cert_regPhHealth = !empty($_FILES["cert_regPhHealth"]["name"]) ? $_FILES["cert_regPhHealth"]["name"] : null;
                    $param_cert_regPGIBG = !empty($_FILES["cert_regPGIBG"]["name"]) ? $_FILES["cert_regPGIBG"]["name"] : null;
                    $param_sketch_map = !empty($_FILES["sketch_map"]["name"]) ? $_FILES["sketch_map"]["name"] : null;
                    $param_2303_bir = !empty($_FILES["2303_bir"]["name"]) ? $_FILES["2303_bir"]["name"] : null;
                    $param_user_id = $user_id;

                    // Execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Move uploaded files to the user-specific folder
                        $user_upload_dir = "uploads/" . $user_id;
                        if (!is_dir($user_upload_dir)) {
                            mkdir($user_upload_dir, 0755, true);
                        }

                        if (!empty($_FILES["loi"]["name"])) {
                            move_uploaded_file($_FILES["loi"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loi"]["name"]);
                        }
                        if (!empty($_FILES["cp"]["name"])) {
                            move_uploaded_file($_FILES["cp"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cp"]["name"]);
                        }
                        if (!empty($_FILES["sec_accredit"]["name"])) {
                            move_uploaded_file($_FILES["sec_accredit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sec_accredit"]["name"]);
                        }
                        if (!empty($_FILES["cda_license"]["name"])) {
                            move_uploaded_file($_FILES["cda_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cda_license"]["name"]);
                        }
                        if (!empty($_FILES["dole_license"]["name"])) {
                            move_uploaded_file($_FILES["dole_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["dole_license"]["name"]);
                        }
                        if (!empty($_FILES["loc"]["name"])) {
                            move_uploaded_file($_FILES["loc"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loc"]["name"]);
                        }
                        if (!empty($_FILES["mbpermit"]["name"])) {
                            move_uploaded_file($_FILES["mbpermit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["mbpermit"]["name"]);
                        }
                        if (!empty($_FILES["job_vacant"]["name"])) {
                            move_uploaded_file($_FILES["job_vacant"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_vacant"]["name"]);
                        }
                        if (!empty($_FILES["job_solicitation"]["name"])) {
                            move_uploaded_file($_FILES["job_solicitation"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_solicitation"]["name"]);
                        }
                        if (!empty($_FILES["phjobnet_reg"]["name"])) {
                            move_uploaded_file($_FILES["phjobnet_reg"]["tmp_name"], $user_upload_dir . "/" . $_FILES["phjobnet_reg"]["name"]);
                        }
                        if (!empty($_FILES["cert_nopendingcase"]["name"])) {
                            move_uploaded_file($_FILES["cert_nopendingcase"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_nopendingcase"]["name"]);
                        }
                        if (!empty($_FILES["cert_regSSS"]["name"])) {
                            move_uploaded_file($_FILES["cert_regSSS"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regSSS"]["name"]);
                        }
                        if (!empty($_FILES["cert_regPhHealth"]["name"])) {
                            move_uploaded_file($_FILES["cert_regPhHealth"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPhHealth"]["name"]);
                        }
                        if (!empty($_FILES["cert_regPGIBG"]["name"])) {
                            move_uploaded_file($_FILES["cert_regPGIBG"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPGIBG"]["name"]);
                        }
                        if (!empty($_FILES["sketch_map"]["name"])) {
                            move_uploaded_file($_FILES["sketch_map"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sketch_map"]["name"]);
                        }
                        if (!empty($_FILES["2303_bir"]["name"])) {
                            move_uploaded_file($_FILES["2303_bir"]["tmp_name"], $user_upload_dir . "/" . $_FILES["2303_bir"]["name"]);
                        }

                        $success = "Profile updated successfully!";
                    } else {
                        $error = "Error updating profile.";
                    }

                    // Close statement
                    mysqli_stmt_close($stmt);

                    // Close connection
                    mysqli_close($conn);
                }
            } else {
                // insert data into company_documents table
                $company_documents_sql2 = "INSERT INTO company_documents (loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, sketch_map, 2303_bir, user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                if ($stmt = mysqli_prepare($conn, $company_documents_sql2)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssssssssssssssss", $param_loi, $param_cp, $param_sec_accredit, $param_cda_license, $param_dole_license, $param_loc, $param_mbpermit, $param_job_vacant, $param_job_solicitation, $param_phjobnet_reg, $param_cert_nopendingcase, $param_cert_regSSS, $param_cert_regPhHealth, $param_cert_regPGIBG, $param_sketch_map, $param_2303_bir, $param_user_id);

                    // Set parameters
                    $param_loi = !empty($_FILES["loi"]["name"]) ? $_FILES["loi"]["name"] : null;
                    $param_cp = !empty($_FILES["cp"]["name"]) ? $_FILES["cp"]["name"] : null;
                    $param_sec_accredit = !empty($_FILES["sec_accredit"]["name"]) ? $_FILES["sec_accredit"]["name"] : null;
                    $param_cda_license = !empty($_FILES["cda_license"]["name"]) ? $_FILES["cda_license"]["name"] : null;
                    $param_dole_license = !empty($_FILES["dole_license"]["name"]) ? $_FILES["dole_license"]["name"] : null;
                    $param_loc = !empty($_FILES["loc"]["name"]) ? $_FILES["loc"]["name"] : null;
                    $param_mbpermit = !empty($_FILES["mbpermit"]["name"]) ? $_FILES["mbpermit"]["name"] : null;
                    $param_job_vacant = !empty($_FILES["job_vacant"]["name"]) ? $_FILES["job_vacant"]["name"] : null;
                    $param_job_solicitation = !empty($_FILES["job_solicitation"]["name"]) ? $_FILES["job_solicitation"]["name"] : null;
                    $param_phjobnet_reg = !empty($_FILES["phjobnet_reg"]["name"]) ? $_FILES["phjobnet_reg"]["name"] : null;
                    $param_cert_nopendingcase = !empty($_FILES["cert_nopendingcase"]["name"]) ? $_FILES["cert_nopendingcase"]["name"] : null;
                    $param_cert_regSSS = !empty($_FILES["cert_regSSS"]["name"]) ? $_FILES["cert_regSSS"]["name"] : null;
                    $param_cert_regPhHealth = !empty($_FILES["cert_regPhHealth"]["name"]) ? $_FILES["cert_regPhHealth"]["name"] : null;
                    $param_cert_regPGIBG = !empty($_FILES["cert_regPGIBG"]["name"]) ? $_FILES["cert_regPGIBG"]["name"] : null;
                    $param_sketch_map = !empty($_FILES["sketch_map"]["name"]) ? $_FILES["sketch_map"]["name"] : null;
                    $param_2303_bir = !empty($_FILES["2303_bir"]["name"]) ? $_FILES["2303_bir"]["name"] : null;
                    $param_user_id = $user_id;

                    // Execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Move uploaded files to the user-specific folder
                        $user_upload_dir = "uploads/" . $user_id;
                        if (!is_dir($user_upload_dir)) {
                            mkdir($user_upload_dir, 0755, true);
                        }

                        if (!empty($_FILES["loi"]["name"])) {
                            move_uploaded_file($_FILES["loi"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loi"]["name"]);
                        }
                        if (!empty($_FILES["cp"]["name"])) {
                            move_uploaded_file($_FILES["cp"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cp"]["name"]);
                        }
                        if (!empty($_FILES["sec_accredit"]["name"])) {
                            move_uploaded_file($_FILES["sec_accredit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sec_accredit"]["name"]);
                        }
                        if (!empty($_FILES["cda_license"]["name"])) {
                            move_uploaded_file($_FILES["cda_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cda_license"]["name"]);
                        }
                        if (!empty($_FILES["dole_license"]["name"])) {
                            move_uploaded_file($_FILES["dole_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["dole_license"]["name"]);
                        }
                        if (!empty($_FILES["loc"]["name"])) {
                            move_uploaded_file($_FILES["loc"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loc"]["name"]);
                        }
                        if (!empty($_FILES["mbpermit"]["name"])) {
                            move_uploaded_file($_FILES["mbpermit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["mbpermit"]["name"]);
                        }
                        if (!empty($_FILES["job_vacant"]["name"])) {
                            move_uploaded_file($_FILES["job_vacant"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_vacant"]["name"]);
                        }
                        if (!empty($_FILES["job_solicitation"]["name"])) {
                            move_uploaded_file($_FILES["job_solicitation"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_solicitation"]["name"]);
                        }
                        if (!empty($_FILES["phjobnet_reg"]["name"])) {
                            move_uploaded_file($_FILES["phjobnet_reg"]["tmp_name"], $user_upload_dir . "/" . $_FILES["phjobnet_reg"]["name"]);
                        }
                        if (!empty($_FILES["cert_nopendingcase"]["name"])) {
                            move_uploaded_file($_FILES["cert_nopendingcase"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_nopendingcase"]["name"]);
                        }
                        if (!empty($_FILES["cert_regSSS"]["name"])) {
                            move_uploaded_file($_FILES["cert_regSSS"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regSSS"]["name"]);
                        }
                        if (!empty($_FILES["cert_regPhHealth"]["name"])) {
                            move_uploaded_file($_FILES["cert_regPhHealth"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPhHealth"]["name"]);
                        }
                        if (!empty($_FILES["cert_regPGIBG"]["name"])) {
                            move_uploaded_file($_FILES["cert_regPGIBG"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPGIBG"]["name"]);
                        }
                        if (!empty($_FILES["sketch_map"]["name"])) {
                            move_uploaded_file($_FILES["sketch_map"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sketch_map"]["name"]);
                        }
                        if (!empty($_FILES["2303_bir"]["name"])) {
                            move_uploaded_file($_FILES["2303_bir"]["tmp_name"], $user_upload_dir . "/" . $_FILES["2303_bir"]["name"]);
                        }

                        $success = "Profile updated successfully!";
                    } else {
                        $error = "Error updating profile.";
                    }
                }
            }
        }
    }
}

?>
<html>

<head>
    <title>PESO Job Portal - Edit Profile</title>
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
</head>

<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <h1>Edit Profile</h1>
        <?php if (isset($success)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"]), ENT_QUOTES); ?>" method="post" enctype="multipart/form-data">
            <!-- display user type, First Name, Middle Name, Last Name, Suffix, Email -->
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" disabled>
                    <option value="Applicant" <?php echo ($user_type === 'applicant') ? 'selected' : ''; ?>>Applicant
                    </option>
                    <option value="Company" <?php echo ($user_type === 'company') ? 'selected' : ''; ?>>Company</option>
                </select>
            </div><br>


            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['fname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['mname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['lname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="suffix">Suffix</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['suffix']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" disabled>
            </div>

            <div class="form-text">To Request for Change of Name, Email Adress, please email us at
                inquiry@muntinlupajoportal.site</div><br>

            <!-- display editable fields -->
            <div class="form-group">
                <label for="sex">Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Please Select --</option>
                    <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div><br>
            <div class="form-group">
                <label for="birth_day">Birth Day</label>
                <input type="number" name="birth_day" class="form-control" value="<?php echo $birth_day; ?>" min="1" max="31" placeholder="DD" required>
            </div><br>
            <div class="form-group">
                <label for="birth_month">Birth Month</label>
                <div class="form-text">Please use numbers for month (e.g. 1 for January, 2 for February, etc.)</div>
                <input type="number" name="birth_month" class="form-control" value="<?php echo $birth_month; ?>" min="1" max="12" placeholder="MM" required>
            </div><br>
            <div class="form-group">
                <label for="birth_year">Birth Year</label>
                <input type="number" name="birth_year" class="form-control" value="<?php echo $birth_year; ?>" maxlength="4" min="0" max="9999" step="1" placeholder="YYYY" pattern="[0-9]{4}" required>
            </div><br>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="<?php echo $contact_number; ?>" placeholder="09xxxxxxxxx" required>
            </div><br>
            <div class="form-group">
                <label for="house_number">House Number</label>
                <input type="number" name="house_number" class="form-control" value="<?php echo $house_number; ?>" placeholder="60" required>
            </div><br>
            <div class="form-group">
                <label for="street">Street</label>
                <input type="text" name="street" class="form-control" value="<?php echo $street; ?>" placeholder="Juan Street" required>
            </div><br>
            <div class="form-group">
                <label for="subdivision">Subdivision/Suburb</label>
                <input type="text" name="subdivision" class="form-control" value="<?php echo $subdivision; ?>" placeholder="San Jose Village" required>
            </div><br>
            <div class="form-group">
                <label for="barangay">Barangay</label>
                <input type="text" name="barangay" class="form-control" value="<?php echo $barangay; ?>" placeholder="Alabang" required>
            </div><br>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" class="form-control" value="<?php echo $city; ?>" placeholder="Muntinlupa City" required>
            </div><br>
            <div class="form-group">
                <label for="province">Province</label>
                <input type="text" name="province" class="form-control" value="<?php echo $province; ?>" placeholder="Metro Manila" required>
            </div><br>
            <div class="form-group">
                <label for="zip_code">Zip Code</label>
                <input type="text" name="zip_code" class="form-control" value="<?php echo $zip_code; ?>" placeholder="1780" required>
            </div><br>
            <div class="form-group">
                <label for="school_name">Last Finsihed School Name</label>
                <input type="text" name="school_name" class="form-control" value="<?php echo $school_name; ?>">
            </div><br>
            <div class="form-group">
                <label for="school_year_begin">School Year Begin</label>
                <input type="text" name="school_year_begin" class="form-control" value="<?php echo $school_year_begin; ?>" maxlength="4" min="0" max="9999" step="1" placeholder="YYYY" pattern="[0-9]{4}">
            </div><br>
            <div class="form-group">
                <label for="school_year_end">School Year End</label>
                <input type="text" name="school_year_end" class="form-control" value="<?php echo $school_year_end; ?>" maxlength="4" min="0" max="9999" step="1" placeholder="YYYY" pattern="[0-9]{4}">
            </div><br>
            <div class="form-group">
                <label for="technicalschool_name">Technical School Name</label>
                <input type="text" name="technicalschool_name" class="form-control" value="<?php echo $technicalschool_name; ?>">
            </div><br>
            <div class="form-group">
                <label for="jinindustry">Select Job Industry</label>
                <div class="form-text">Select a Job Industry you want to apply on</div>
                <select class="form-select" aria-label="Default select example" id="jinindustry" name="jinindustry" required>
                    <?php
                    $sql3 = "SELECT * FROM jinindustry";
                    $result3 = mysqli_query($conn, $sql3);
                    while ($row3 = mysqli_fetch_assoc($result3)) {
                        $selected = ($jinindustry == $row3['jinindustry_id']) ? 'selected' : '';
                        echo "<option value='" . $row3['jinindustry_id'] . "' " . $selected . ">" . $row3['jinindustry_name'] . "</option>";
                    }
                    ?>
                </select>
            </div><br>
            <?php if ($user_type == 'applicant') : ?>
                <div class="form-group">
                    <label for="nsrp_form">NSRP Form (PDF or DOCX only)</label>
                    <div class="form-text">No NSRP Form? <a href="admin/forms/NSRP-Form.pdf" target="_blank">Click here
                            to obtain one</a>.</div>
                    <input type="file" name="nsrp_form" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="biodata_form">Biodata Form (PDF or DOCX only)</label>
                    <input type="file" name="biodata_form" class="form-control" accept="application/pdf,.docx">
                </div><br>
            <?php endif; ?>

            <div class="form-group">
                <label for="profile_picture">Profile Picture (PNG, JPG, or JPEG Format)</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/png, image/jpeg, image/jpg">
            </div><br>

            <!-- loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, setch_map, 2303_bir document upload as company -->
            <?php if ($user_type == 'company') : ?>
                <div class="form-group">
                    <label for="loi">Letter of Intent (PDF or DOCX only)</label>
                    <input type="file" name="loi" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="cp">Company Profile (PDF or DOCX only)</label>
                    <input type="file" name="cp" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="sec_accredit">SEC Accreditation (PDF or DOCX only)</label>
                    <input type="file" name="sec_accredit" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="cda_license">CDA License (PDF or DOCX only)</label>
                    <input type="file" name="cda_license" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="dole_license">DOLE License (PDF or DOCX only)</label>
                    <input type="file" name="dole_license" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="loc">Location Map (PDF or DOCX only)</label>
                    <input type="file" name="loc" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="mbpermit">Mayor's Business Permit (PDF or DOCX only)</label>
                    <input type="file" name="mbpermit" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="job_vacant">Job Vacant (PDF or DOCX only)</label>
                    <input type="file" name="job_vacant" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="job_solicitation">Job Solicitation (PDF or DOCX only)</label>
                    <input type="file" name="job_solicitation" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="phjobnet_reg">PhilJobNet Registration (PDF or DOCX only)</label>
                    <input type="file" name="phjobnet_reg" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="cert_nopendingcase">Certificate of No Pending Case (PDF or DOCX only)</label>
                    <input type="file" name="cert_nopendingcase" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="cert_regSSS">Certificate of Registration with SSS (PDF or DOCX only)</label>
                    <input type="file" name="cert_regSSS" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="">Certificate of Registration with PhilHealth (PDF or DOCX only)</label>
                    <input type="file" name="cert_regPhHealth" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="">Certificate of Registration with Pag-IBIG (PDF or DOCX only)</label>
                    <input type="file" name="cert_regPGIBG" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="">Sketch Map (PDF or DOCX only)</label>
                    <input type="file" name="sketch_map" class="form-control" accept="application/pdf,.docx">
                </div><br>
                <div class="form-group">
                    <label for="">BIR Form 2303 (PDF or DOCX only)</label>
                    <input type="file" name="2303_bir" class="form-control" accept="application/pdf,.docx">
                </div><br>
            <?php endif; ?>
            <!-- submit button -->
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Profile">
        </form>

    </div>
</body>

</html>