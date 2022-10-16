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
                albums.id AS album_id,
                albums.album_name AS album_name,
                users.id AS album_created_by,
                users.username AS album_author,
                albums.created_at AS album_created_at
            FROM albums
            LEFT JOIN users ON albums.created_by = users.id
            WHERE albums.deleted = 0;";

    if ($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Bootstrap -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

        <title>Albums | Image Uploader</title>
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
                    "title" => "Create New Album",
                    "href" => "new_album",

                    "active" => false
                ],
                (object)[
                    "title" => "Log Out",
                    "href" => "logout",
                    "active" => false
                ]
            ));
        ?>

        <div class="container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Author</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $user_id = $_SESSION["user"]["id"];

                        foreach ($rows as $row) {
                            $album_id = $row["album_id"];
                            $album_name = $row["album_name"];
                            $album_created_by = $row["album_created_by"];
                            $album_author = $row["album_author"];
                            $album_created_at = $row["album_created_at"];

                            $html = <<<HTML
                                        <tr>
                                            <td>$album_id</td>
                                            <td>$album_name</td>
                                            <td>$album_author</td>
                                            <td>$album_created_at</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Action buttons">
                                                    <a href="album?id=$album_id" class="btn btn-outline-primary">
                                                        Open
                                                    </a>
                                        HTML;

                            if($album_created_by == $user_id) {
                                $html .= <<<HTML
                                            <a href="delete_album?id=$album_id" class="btn btn-outline-primary">
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

    </body>
</html>