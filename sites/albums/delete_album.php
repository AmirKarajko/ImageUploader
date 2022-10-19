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

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "UPDATE albums SET deleted = 1 WHERE id = ? AND created_by = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $album_id, $_SESSION["user"]["id"]);
        }
        mysqli_stmt_execute($stmt);

        $sql = "UPDATE images SET deleted = 1 WHERE albums_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $album_id);
        }
        mysqli_stmt_execute($stmt);

        header("location: albums");
        exit;
    }
?>