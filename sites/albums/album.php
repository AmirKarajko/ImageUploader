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

    $album_id = mysqli_real_escape_string($conn, trim($_GET["id"]));

    $sql = "SELECT
                albums.id AS album_id,
                albums.name AS album_name,
                albums.description AS album_description,
                albums.created_by AS album_created_by
            FROM albums
            LEFT JOIN users ON albums.created_by = users.id
            WHERE albums.id = $album_id AND albums.deleted = 0;";

    $sql .= "SELECT
                images.id AS image_id,
                images.filename AS image_filename,
                images.mime AS image_file_type,
                users.id AS image_uploaded_by,
                users.username AS image_uploader,
                images.uploaded_at AS image_uploaded_at
            FROM images
            LEFT JOIN users ON images.uploaded_by = users.id
            LEFT JOIN albums ON albums.id = images.albums_id
            WHERE images.deleted = 0 AND albums.id = $album_id;";

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

    $title = (isset($rows[0]["album_name"]) && strlen($rows[0]["album_name"]) > 0) ? $rows[0]["album_name"] : "<unnamed>";

    $user_id = $_SESSION["user"]["id"];
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
            require_once(__DIR__ . "/../../php/navbar.php");

            if($rows[0]["album_created_by"] == $_SESSION["user"]["id"]) {
                echo getNavbar(array(
                    (object)[
                        "title" => "Albums",
                        "href" => "albums",
                        "active" => false
                    ],
                    (object)[
                        "title" => "Save",
                        "href" => "save_album",
                        "active" => false,
                        "form" => "editorForm"
                    ],
                    (object)[
                        "title" => "Delete",
                        "href" => "delete_album?id=$album_id",
                        "active" => false
                    ],
                    (object)[
                        "title" => "Profile",
                        "href" => "profile_picture?id=$user_id",
    
                        "active" => false
                    ],
                    (object)[
                        "title" => "Log Out",
                        "href" => "logout",
                        "active" => false
                    ]
                ), false);
            }
            else {
                echo getNavbar(array(
                    (object)[
                        "title" => "Albums",
                        "href" => "albums",
                        "active" => false
                    ],
                    (object)[
                        "title" => "Profile",
                        "href" => "profile_picture?id=$user_id",
    
                        "active" => false
                    ],
                    (object)[
                        "title" => "Log Out",
                        "href" => "logout",
                        "active" => false
                    ]
                ), false);
            }
        ?>

        <div class="container">
            <form action="save_album" method="post" id="editorForm">
                <input type="hidden" name="id" value="<?php echo $album_id; ?>" />
                <div class="mb-3">
                    <label for="name" class="label">Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo $rows[0]["album_name"]; ?>" maxlength="128" />
                </div>
                <div class="mb-3 mt-3">
                    <label for="description" class="label">Description</label>
                    <input type="text" class="form-control" name="description" value="<?php echo $rows[0]["album_description"]; ?>" maxlength="256" />
                </div>
            </form>
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

        <div class="container mt-3">
            <div class="card">
                <div class="card-body">
                    <form action="upload_image" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $album_id; ?>" />
                        <div class="mb-3">
                            <label for="upload" class="label">File</label>
                            <input class="file-input" type="file" name="upload[]" multiple />
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
                        <th>Uploaded by</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $user_id = $_SESSION["user"]["id"];

                        for ($i = 1; $i < count($rows); $i++) {
                            $image_id = $rows[$i]["image_id"];
                            $image_filename = $rows[$i]["image_filename"];
                            $image_file_type = $rows[$i]["image_file_type"];
                            $image_uploaded_by = $rows[$i]["image_uploaded_by"];
                            $image_uploader = $rows[$i]["image_uploader"];
                            $image_uploaded_at = $rows[$i]["image_uploaded_at"];
                            $image_uploaded_by = $rows[$i]["image_uploaded_by"];

                            $image_album_id = $album_id;

                            $html = <<<HTML
                                        <tr>
                                            <td>$image_id</td>
                                        HTML;

                            $image_filename_length = strlen($image_filename);
                            $max_filename_length = 24;

                            if($image_filename_length > $max_filename_length) {
                                $image_short_filename = substr($image_filename, 0, $max_filename_length) . "...";

                                $html .= <<<HTML
                                            <td title="$image_filename">$image_short_filename</td>
                                            HTML;
                            }
                            else if ($image_filename_length > 0 && $image_filename_length <= $max_filename_length) {
                                $html .= <<<HTML
                                            <td title="$image_filename">$image_filename</td>
                                            HTML;
                            }
                            else {
                                $html .= <<<HTML
                                            <td title="<unnamed>">&ltunnamed&gt</td>
                                            HTML;
                            }

                            $html .= <<<HTML
                                            <td>$image_file_type</td>
                                            <td><a href="profile_picture?id=$image_uploaded_by">$image_uploader</a></td>
                                            <td>$image_uploaded_at</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Action buttons">
                                                    <a href="download_image?id=$image_id" class="btn btn-outline-primary">
                                                        Download
                                                    </a>
                                        HTML;

                            $image_file_types = array(
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
                            if(in_array($image_file_type, $image_file_types)) {
                                $html .= <<<HTML
                                                <a href="view_image?id=$image_id" class="btn btn-outline-primary">
                                                    View
                                                </a>
                                            HTML;
                            }

                            if($image_uploaded_by == $user_id) {
                                $html .= <<<HTML
                                            <a href="delete_image?id=$image_id" class="btn btn-outline-primary">
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
                    ?>
                </tbody>
            </table>
        </div>

    <body>
</html>