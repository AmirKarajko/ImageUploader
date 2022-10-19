<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }

    require_once(__DIR__ . "/../../php/database.php");

    $user_id = mysqli_real_escape_string($conn, trim($_GET["id"]));

    $sql = "SELECT
                profile_pictures.id AS profile_id,
                profile_pictures.filename AS profile_filename,
                profile_pictures.mime AS profile_file_type,
                profile_pictures.uploaded_by AS profile_uploaded_by,
                profile_pictures.active AS profile_active,
                profile_pictures.uploaded_At AS profile_uploaded_at,
                profile_pictures.deleted AS profile_deleted
            FROM profile_pictures
            LEFT JOIN users ON profile_pictures.uploaded_by = users.id
            WHERE profile_pictures.deleted = 0 AND profile_pictures.uploaded_by = $user_id;";
    
    if ($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    $sql = "SELECT username AS user_username FROM users WHERE id = $user_id";
    if ($result = mysqli_query($conn, $sql)) {
        $rows2 = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows2[] = $row;
        }
    }
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
        <title>Profile | Image Uploader</title>
    </head>
    <body>
        <?php
            require_once("./php/navbar.php");
            echo getNavbar(array(
                (object)[
                    "title" => "Albums",
                    "href" => "albums",

                    "active" => false
                ],
                (object)[
                    "title" => "Profile",
                    "href" => "profile_picture?id=" . $_SESSION["user"]["id"],

                    "active" => false
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
                        $profile_html = <<<HTML
                        HTML;

                        foreach ($rows2 as $row) {
                            $user_username = $row["user_username"];

                            $profile_html .= <<<HTML
                                                <center>
                                                    <h3>$user_username</h3>
                                                </center>
                                            HTML;
                        }

                        foreach ($rows as $row) {
                            $profile_id = $row["profile_id"];
                            $profile_active = $row["profile_active"];
                            $profile_deleted = $row["profile_deleted"];

                            if ($profile_active == 1 && !$profile_deleted) {
                                $profile_html .= <<<HTML
                                            <center>
                                                <img src="download_profile_picture?id=$profile_id" style="width:168px; height:168px" />
                                            </center>
                                            HTML;
                            }
                        }                        
                        
                        echo $profile_html;
                    ?>
                </div>
            </div>
        </div>
        
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

        <?php 
            if ($user_id == $_SESSION["user"]["id"]) {
                echo '
                    <div class="container mt-3">
                        <div class="card">
                            <div class="card-body">
                                <form action="upload_profile_picture" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $album_id; ?>" />
                                    <div class="mb-3">
                                        <label for="upload" class="label">File</label>
                                        <input class="file-input" type="file" name="upload" maxlength="128" />
                                    </div>
                                    <div>
                                        <button class="btn btn-primary" type="submit">Upload File</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Filename</th>
                                    <th>File Type</th>
                                    <th>Uploaded At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                ';

                foreach ($rows as $row) {
                    $profile_id = $row["profile_id"];
                    $profile_filename = $row["profile_filename"];
                    $profile_file_type = $row["profile_file_type"];
                    $profile_uploaded_by = $row["profile_uploaded_by"];
                    $profile_uploaded_at = $row["profile_uploaded_at"];
                    $profile_active = $row["profile_active"];
                
                    $html = <<<HTML
                            <tr>
                                <td>$profile_id</td>
                                <td>$profile_filename</td>
                                <td>$profile_file_type</td>
                                <td>$profile_uploaded_at</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Action buttons">
                                        <a href="set_profile_picture?id=$profile_id" class="btn btn-outline-primary">
                                            Set
                                        </a>
                                        <a href="download_profile_picture?id=$profile_id" class="btn btn-outline-primary">
                                            Download
                                        </a>
                            HTML;

                    $profile_file_types = array(
                        "image/avif",
                        "image/bmp",
                        "image/gif",
                        "image/vnd.microsoft.icon",
                        "image/jpeg",
                        "image/png",
                        "image/svg+xml",
                        "image/tiff",
                        "image/webp"
                    );
                    if(in_array($profile_file_type, $profile_file_types)) {
                        $html .= <<<HTML
                                        <a href="view_profile_picture?id=$profile_id" class="btn btn-outline-primary">
                                            View
                                        </a>
                                    HTML;
                    }

                    if($profile_uploaded_by == $user_id) {
                        $html .= <<<HTML
                                    <a href="delete_profile_picture?id=$profile_id" class="btn btn-outline-primary">
                                        Delete
                                    </a>
                                HTML;
                    }

                    $html .= <<<HTML
                                        </div>
                                    </td>
                                </tr>
                            HTML;

                    echo $html;
                }

                echo '</tbody>
                    </table>
                </div>';
            }
        ?>

    </body>
</html>