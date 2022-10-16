<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
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
</html>