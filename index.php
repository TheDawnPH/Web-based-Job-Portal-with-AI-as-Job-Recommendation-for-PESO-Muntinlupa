<?php
session_start();

include 'config.php';


?>

<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
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
    <style>
        .cover {
            /* use 1350x300px image */
            background-image: url('img/banner.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 300px;

        }

        @media (max-width: 1200px) {
            .cover {
                display: none;
                /* Hide the cover image on devices with resolution 1200px and below */
            }
        }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="col-md-12">
        <div class="cover"></div>
    </div>
    <div class="container">
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] === 'applicant') { ?>
                <div class="col-md-4">
                    <h1>Available Jobs</h1>
                    <hr>
                    <?php
                    // Assuming $conn is your database connection
                    $sql = "SELECT * FROM job_listing WHERE jinindustry_id =" . $_SESSION['jinindustry_id'];
                    $result = mysqli_query($conn, $sql);

                    // Check if there are any rows in the result set
                    if (mysqli_num_rows($result) > 0) {
                        // Loop through the rows and display job information with job industry
                        while ($row = mysqli_fetch_assoc($result)) {
                            $sql2 = "SELECT * FROM jinindustry WHERE jinindustry_id =" . $row['jinindustry_id'];
                            $result2 = mysqli_query($conn, $sql2);
                            $row2 = mysqli_fetch_assoc($result2);

                            echo '<div>';
                            echo '<h2>' . $row['job_title'] . '</h2>';
                            // show job industry from $row2
                            echo '<p>Job Industry: ' . $row2['jinindustry_name'] . '</p>';
                            echo '<p>Job Salary: ₱' . $row['job_salary'] . '</p>';
                            echo '<a href="job_details.php?job_id=' . $row['id'] . '" class="btn btn-primary">View Job</a>';
                            echo '</div><hr>';
                        }
                    } else {
                        echo '<p>No job listings found.</p>';
                    }

                    // Close the result set and database connection
                    mysqli_free_result($result);
                    mysqli_close($conn);
                    ?>
                    <br>
                </div>';
            <?php
            }
            ?>
            <div class="col-md-4">
                <h1>Latest Jobs</h1>
                <hr>
                <?php
                // Assuming $conn is your database connection
                $sql = "SELECT * FROM job_listing ORDER BY id DESC LIMIT 10";
                $result = mysqli_query($conn, $sql);

                // Check if there are any rows in the result set
                if (mysqli_num_rows($result) > 0) {
                    // Loop through the rows and display job information with job industry
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sql2 = "SELECT * FROM jinindustry WHERE jinindustry_id =" . $row['jinindustry_id'];
                        $result2 = mysqli_query($conn, $sql2);
                        $row2 = mysqli_fetch_assoc($result2);

                        echo '<div>';
                        echo '<h2>' . $row['job_title'] . '</h2>';
                        // show job industry from $row2
                        echo '<p>Job Industry: ' . $row2['jinindustry_name'] . '</p>';
                        echo '<p>Job Salary: ₱' . $row['job_salary'] . '</p>';
                        echo '<a href="job_details.php?job_id=' . $row['id'] . '" class="btn btn-primary">View Job</a>';
                        echo '</div><hr>';
                    }
                } else {
                    echo '<p>No job listings found.</p>';
                }

                // Close the result set and database connection
                mysqli_free_result($result);
                mysqli_close($conn);
                ?>
            </div><br>
            <?php if (!isset($_SESSION["user_type"])) { ?>
                <div class="col-md-3">
                    <h1>Login or Register</h1>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                        <a href="register.php" class="btn btn-primary btn-lg">Register</a>
                    </div><br>
                </div>
            <?php
            } ?>
            <div class="col-md-2">
                <h1>Facebook Feed</h1>
                <hr>
                <iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FMuntinlupaPESO%2F&tabs=timeline&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId" width="350" height="500" style="border:none;overflow:hidden;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
            </div>
        </div>
    </div>
</body>

</html>