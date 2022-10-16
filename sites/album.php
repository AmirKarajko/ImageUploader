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

    $album_id = mysqli_real_escape_string($conn, trim($_GET["id"]));

    $sql = "SELECT
                albums.id AS album_id,
                albums.album_name AS album_name,
                albums.description AS album_description,
                albums.created_by AS album_created_by
            FROM albums
            LEFT JOIN users ON albums.created_by = users.id
            WHERE albums.id = $album_id AND albums.deleted = 0;";
    if ($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    $title = (isset($rows[0]["album_name"]) && strlen($rows[0]["album_name"]) > 0) ? $rows[0]["album_name"] : "<unnamed>";


    $sql2 = "SELECT
                images.id AS image_id,
                images.filename AS image_filename,
                images.mime AS image_file_type,
                users.id AS image_uploader_id,
                users.username AS image_uploader,
                images.uploaded_at AS image_uploaded_at
            FROM images
            LEFT JOIN users ON images.uploader = users.id
            LEFT JOIN albums ON albums.id = images.album_id
            WHERE images.deleted = 0 AND albums.id = $album_id;";

    if ($result = mysqli_query($conn, $sql2)) {
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

        <title><?php echo $title; ?> | Image Uploader</title>
    </head>
    <body>
        <?php
            require_once(__DIR__ . "/../php/navbar.php");

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
                        <th>Uploader</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $user_id = $_SESSION["user"]["id"];

                        foreach ($rows2 as $row) {
                            $image_id = $row["image_id"];
                            $image_filename = $row["image_filename"];
                            $image_file_type = $row["image_file_type"];
                            $image_uploader_id = $row["image_uploader_id"];
                            $image_uploader = $row["image_uploader"];
                            $image_uploaded_at = $row["image_uploaded_at"];

                            $image_album_id = $album_id;

                            $html = <<<HTML
                                        <tr>
                                            <td>$image_id</td>
                                            <td>$image_filename</td>
                                            <td>$image_file_type</td>
                                            <td>$image_uploader</td>
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

                            if($image_uploader_id == $user_id) {
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