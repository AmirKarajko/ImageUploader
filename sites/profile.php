<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }

    require_once(__DIR__ . "/../php/database.php");

    $sql = "SELECT
                users.id AS user_id,
                users.username AS user_username
            FROM users";

    if(isset($_GET["id"]) && is_numeric($_GET["id"])) {
        $user_id = mysqli_real_escape_string($conn, $_GET["id"]);

        $sql .= " WHERE users.id = $user_id";
    }
    else if(isset($_GET["username"])) {
        $user_username = mysqli_real_escape_string($conn, trim($_GET["username"]));

        $sql .= " WHERE users.username = '$user_username'";
    }
    else {
        exit;
    }

    if ($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    // Redirect to index when no user is found
    if(count($rows) == 0) {
        header("location: /");
        exit;
    }

    $title = (isset($rows[0]["user_username"]) && strlen($rows[0]["user_username"]) > 0) ? $rows[0]["user_username"] : "<unnamed>";

    $user_id = $rows[0]["user_id"];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />

        <!-- Bootstrap -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

        <link rel="icon" type="image/png" href="../favicon.png">
        <title><?php echo $title; ?> | Image Uploader</title>
    </head>
    <body>
        <?php
            require_once(__DIR__ . "/../php/navbar.php");

            echo getNavbar(array(
                (object)[
                    "title" => "Albums",
                    "href" => "albums",
                    "active" => false
                ],
                (object)[
                    "title" => "Profile",
                    "href" => "profile?id=$user_id",

                    "active" => true
                ],
                (object)[
                    "title" => "Log Out",
                    "href" => "logout",
                    "active" => false
                ]
            ), false);
        ?>

        <div class="container mt-3">
            <div class="row">
                <div class="col">
                    <?php
                        // Show notification on upload error
                        if (isset($_SESSION["upload_error"])) {
                            $str = "<div class=\"alert alert-danger\">";
                            $str .= $_SESSION["upload_error"];
                            $str .= "</div>";

                            $_SESSION["upload_error"] = NULL;

                            echo $str;
                        }
                    ?>
                </div>
            </div>
        </div>

        <?php if($_SESSION["user"]["id"] == $user_id) { ?>

        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <form action="upload_profile.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="" />
                        <div class="mb-3">
                            <label for="upload" class="label">File</label>
                            <input class="file-input" type="file" name="upload" />
                        </div>
                        <div>
                            <button class="btn btn-primary" type="submit">Upload File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php } ?>

        <div align="center" class="container mt-3">
            <?php
            $user_profile_picture = NULL;

            $sql = "SELECT profile_pictures.data, users.username AS user_username FROM profile_pictures LEFT JOIN users ON profile_pictures.uploaded_by = users.id WHERE profile_pictures.uploaded_by = '$user_id' AND profile_pictures.active = 1 AND profile_pictures.deleted = 0 LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if($result && mysqli_num_rows($result) > 0) {
                $user_profile_picture = base64_encode(mysqli_fetch_array($result, MYSQLI_BOTH)["data"]);
                $profilePicturesMap["$user_id"] = $user_profile_picture;
            }
            if ($user_profile_picture != null) {
                $result = mysqli_query($conn, $sql);
                $user_username = mysqli_fetch_assoc($result)["user_username"];
                $result = mysqli_query($conn, $sql);

                $html = <<<HTML
                            <p>$user_username</p>
                            <img src="data:image/jpeg;base64,$user_profile_picture" title="$user_username" width="256" height="256" />
                            HTML;
                echo $html;
            }
            ?>
            
        </div>

    <body>
</html>