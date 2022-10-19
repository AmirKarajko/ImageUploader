<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once(__DIR__ . "/../../php/database.php");

        $album_id = mysqli_real_escape_string($conn, trim($_POST["id"]));
        $album_name = mysqli_real_escape_string($conn, trim($_POST["name"]));
        $album_description = mysqli_real_escape_string($conn, trim($_POST["description"]));

        $sql = "UPDATE albums SET name = ?, description = ? WHERE id = ? AND created_by = ? AND deleted = 0";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $album_name, $album_description, $album_id, $_SESSION["user"]["id"]);
        }
        mysqli_stmt_execute($stmt);

        header("location: album?id=" . $album_id);
        exit;
    }
?>