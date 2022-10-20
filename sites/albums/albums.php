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

    $searchQuery = "";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET["search"])) {
            $searchValue = mysqli_real_escape_string($conn, trim($_GET["search"]));
            $searchQuery = " AND (
                albums.name LIKE '%$searchValue%'
            )";
        }
    }

    require_once("./php/pagination.php");

    $totalResults = 0;
    $sql = "SELECT COUNT(id) AS total_albums FROM albums WHERE deleted = 0$searchQuery";
    if($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        $totalResults = $rows[0]["total_albums"];
    }

    $currentPage = isset($_GET["page"]) ? $_GET["page"] : 1;
    $itemsPerPage = 10;
    $limitRangeStart = ($currentPage - 1) * $itemsPerPage;
    $limitRangeEnd = $itemsPerPage;

    $sql = "SELECT
                albums.id AS album_id,
                albums.name AS album_name,
                users.id AS album_created_by,
                users.username AS album_author,
                albums.created_at AS album_created_at
            FROM albums
            LEFT JOIN users ON albums.created_by = users.id
            WHERE albums.deleted = 0$searchQuery
            LIMIT $limitRangeStart, $limitRangeEnd";

    if ($result = mysqli_query($conn, $sql)) {
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    $user_id = $_SESSION["user"]["id"];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Bootstrap -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

        <link rel="icon" type="image/png" href="../favicon.png">
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
                    "title" => "Profile",
                    "href" => "profile_picture?id=$user_id",

                    "active" => false
                ],
                (object)[
                    "title" => "Log Out",
                    "href" => "logout",
                    "active" => false
                ]
            ), true);
        ?>

        <?php
            echo getPagination($currentPage, $itemsPerPage, $totalResults, "albums");
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
                                        HTML;

                            $album_name_length = strlen($album_name);
                            $max_name_length = 24;

                            if($album_name_length > $max_name_length) {
                                $album_short_name = substr($album_name, 0, $max_name_length) . "...";

                                $html .= <<<HTML
                                            <td title="$album_name">$album_short_name</td>
                                            HTML;
                            }
                            else if ($album_name_length > 0 && $album_name_length <= $max_name_length) {
                                $html .= <<<HTML
                                            <td title="$album_name">$album_name</td>
                                            HTML;
                            }
                            else {
                                $html .= <<<HTML
                                            <td title="<unnamed>">&ltunnamed&gt</td>
                                            HTML;
                            }

                            $html .= <<<HTML
                                            <td><a href="profile_picture?id=$album_created_by">$album_author</a></td>
                                            <td>$album_created_at</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Action buttons">
                                                    <a href="album?id=$album_id" class="btn btn-outline-primary">
                                                        Open
                                                    </a>
                                        HTML;

                            if($album_created_by == $user_id) {
                                $html .= <<<HTML
                                            <a onclick="if(confirm('Are you sure you want to delete this album?')) {window.location = 'delete_album?id=$album_id'}" class="btn btn-outline-primary">
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