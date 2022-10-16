<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }


    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        exit;
    }


    require_once(__DIR__ . "/../php/database.php");

    $image_id = mysqli_real_escape_string($conn, $_GET["id"]);

    if(isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
        $user_id = $_SESSION["user"]["id"];
        $sql = "SELECT filename, mime, data FROM images WHERE id = '$image_id' LIMIT 1";
    }

    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        header("Content-Disposition: attachment; filename=\"" . $row["filename"] . "\"");
        header("Content-Type: " . $row["mime"]);

        echo $row["data"];
    }
?>