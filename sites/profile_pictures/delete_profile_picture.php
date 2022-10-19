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

    $profile_id = mysqli_real_escape_string($conn, trim($_GET["id"]));

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "UPDATE profile_pictures SET deleted = 1 WHERE id = ? AND uploaded_by = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $profile_id, $_SESSION["user"]["id"]);
        }
        mysqli_stmt_execute($stmt);

        echo "<script>history.go(-1);</script>";
        exit;
    }
?>