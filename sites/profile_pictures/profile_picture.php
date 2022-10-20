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

    $sql = "SELECT username AS user_username FROM users WHERE id = '" . $user_id . "';";

    $sql .= "SELECT
                profile_pictures.id AS profile_id,
                profile_pictures.filename AS profile_filename,
                profile_pictures.mime AS profile_file_type,
                profile_pictures.uploaded_by AS profile_uploaded_by,
                profile_pictures.active AS profile_active,
                profile_pictures.uploaded_At AS profile_uploaded_at,
                profile_pictures.deleted AS profile_deleted
            FROM profile_pictures
            LEFT JOIN users ON profile_pictures.uploaded_by = users.id
            WHERE profile_pictures.deleted = 0 AND profile_pictures.uploaded_by = '" . $user_id . "';";

    $rows = array();
    if ($conn -> multi_query($sql)) {
        do {
            if ($result = $conn -> store_result()) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }

                $result -> free_result();
            }
        } while ($conn -> next_result());
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

        <?php
            $profile_html = <<<HTML
            <div class="container mt-3">
                <div class="row">
                    <div class="col">
            HTML;

            $user_username = $rows[0]["user_username"];

            $profile_html = <<<HTML
                <center>
                    <h3>$user_username</h3>
                </center>
            HTML;

            for ($i = 1; $i < count($rows); $i++) {
                $profile_id = $rows[$i]["profile_id"];
                $profile_active = $rows[$i]["profile_active"];
                $profile_deleted = $rows[$i]["profile_deleted"];

                if ($profile_active == 1 && !$profile_deleted) {
                    $profile_html .= <<<HTML
                        <center>
                            <img src="download_profile_picture?id=$profile_id" style="object-fit:contain; width: 200px; height: 200px;" />
                        </center>
                    HTML;
                }
            }

            $profile_html .= <<<HTML
                    </div>
                </div>
            </div>
            HTML;

            echo $profile_html;
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

        <?php 
            if ($user_id == $_SESSION["user"]["id"]) {

                $album_id = mysqli_real_escape_string($conn, trim($_GET["id"]));

                $upload_file_html = <<<HTML
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
                HTML;

                echo $upload_file_html;

                for ($i = 1; $i < count($rows); $i++) {
                    $profile_id = $rows[$i]["profile_id"];
                    $profile_filename = $rows[$i]["profile_filename"];
                    $profile_file_type = $rows[$i]["profile_file_type"];
                    $profile_uploaded_by = $rows[$i]["profile_uploaded_by"];
                    $profile_uploaded_at = $rows[$i]["profile_uploaded_at"];
                    $profile_active = $rows[$i]["profile_active"];
                
                    $html = <<<HTML
                            <tr>
                                <td>$profile_id</td>
                            HTML;

                    $profile_filename_length = strlen($profile_filename);
                    $max_filename_length = 24;

                    if($profile_filename_length > $max_filename_length) {
                        $profile_short_filename = substr($profile_filename, 0, $max_filename_length) . "...";

                        $html .= <<<HTML
                                    <td title="$profile_filename">$profile_short_filename</td>
                                    HTML;
                    }
                    else if ($profile_filename_length > 0 && $profile_filename_length <= $max_filename_length) {
                        $html .= <<<HTML
                                    <td title="$profile_filename">$profile_filename</td>
                                    HTML;
                    }
                    else {
                        $html .= <<<HTML
                                    <td title="<unnamed>">&ltunnamed&gt</td>
                                    HTML;
                    }

                    $html .= <<<HTML
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

                    $html .= <<<HTML
                        <a href="delete_profile_picture?id=$profile_id" class="btn btn-outline-primary">
                            Delete
                        </a>
                    HTML;

                    $html .= <<<HTML
                                </div>
                            </td>
                        </tr>
                    HTML;

                    echo $html;
                }

                $upload_file_html = <<<HTML
                            </tbody>
                        </table>
                    </div>
                HTML;

                echo $upload_file_html;
            }
        ?>

    </body>
</html>