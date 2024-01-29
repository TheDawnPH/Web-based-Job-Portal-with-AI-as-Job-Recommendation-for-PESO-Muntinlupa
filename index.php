<?php
session_start();

include 'config.php';

// Function to calculate cosine similarity
function cosineSimilarity($vector1, $vector2)
{
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    foreach ($vector1 as $key => $value) {
        $dotProduct += $value * $vector2[$key];
        $magnitude1 += $value * $value;
        $magnitude2 += $vector2[$key] * $vector2[$key];
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    } else {
        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}

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
            <?php
            if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] === 'applicant') {
                // Get the user's preferred skills
                $user_skill_sql = "SELECT * FROM users WHERE user_id =" . $_SESSION['user_id'];
                $user_skill_result = mysqli_query($conn, $user_skill_sql);
                $row = mysqli_fetch_assoc($user_skill_result);
                $user_skills = $row['jinindustry_id'];
                if (empty($user_skills)) {
                    $user_skills = '0';
                }

                // retrieve all job_listings and set it as array values for job_title, job_description and $jinindustry_id
                $job_sql = "SELECT id, job_title, job_description, jinindustry_id FROM job_listing";
                $job_result = mysqli_query($conn, $job_sql);
                $jobs = [];
                while ($row = mysqli_fetch_assoc($job_result)) {
                    $jobs[] = $row;
                }

                // Calculate cosine similarity between user's skills and job descriptions
                $user_skills_vector = explode(' ', $user_skills);

                $recommended_jobs = [];

                foreach ($jobs as $job) {
                    $job_skills_vector = explode(' ', $job['jinindustry_id']);
                    $similarity = cosineSimilarity($user_skills_vector, $job_skills_vector);

                    // You can adjust the threshold based on your requirements
                    if ($similarity > 0.5) {
                        $recommended_jobs[] = array(
                            'job_id' => $job['id'],
                            'job_title' => $job['job_title']
                        );
                    }
                }

                // Display the recommended jobs
            ?>
                <div class="col-md-4">
                    <h1>Recommended Jobs</h1>
                    <hr>
                    <?php if ($user_skills === '0') { ?>
                        <div class="alert alert-warning fade show" role="alert">
                            <strong>Warning!</strong><br>You have not set your preferred skills yet. Please update your profile to get recommended jobs.
                        </div>
                    <?php } ?>
                    <?php
                    foreach ($recommended_jobs as $job) :
                        $job_list_sql = mysqli_query($conn, "SELECT * FROM job_listing WHERE id =" . $job['job_id']);
                        $job_list = mysqli_fetch_assoc($job_list_sql);

                        $jinindustry_name = mysqli_query($conn, "SELECT * FROM jinindustry WHERE jinindustry_id =" . $job_list['jinindustry_id']);
                        $jinindustry = mysqli_fetch_assoc($jinindustry_name);
                    ?>
                        <div>
                            <h2><?php echo $job['job_title']; ?></h2>
                            <p>Job Industry: <?php echo $jinindustry['jinindustry_name']; ?></p>
                            <p>Job Salary: ₱<?php echo $job_list['job_salary']; ?></p>
                            <a href="job_details.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Job</a>
                            <hr>
                        </div>
                    <?php endforeach; ?>
                </div>
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