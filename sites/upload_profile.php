<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }


    if (count($_FILES) > 0) {
        // Upload selected file
        if (is_uploaded_file($_FILES["upload"]["tmp_name"])) {
            require_once(__DIR__ . "/../php/database.php");

            $filename = $_FILES["upload"]["name"];
            $tmpName = $_FILES["upload"]["tmp_name"];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            $fileType = $_FILES["upload"]["type"];
            $data = addslashes(file_get_contents($tmpName));

            // Set previous profile pictures as inactive
            $sql = "UPDATE profile_pictures SET active = 0 WHERE uploaded_by = '{$_SESSION["user"]["id"]}'";
            mysqli_query($conn, $sql) or die("<b>Error:</b> Problem on Profile Picture Upload<br />" . mysqli_error($conn));

            // Upload new profile picture
            $sql = "INSERT INTO profile_pictures(uploaded_by, filename, extension, mime, data) VALUES ('{$_SESSION["user"]["id"]}', '{$filename}', '{$extension}', '{$fileType}', '{$data}')";
            mysqli_query($conn, $sql) or die("<b>Error:</b> Problem on Profile Picture Upload<br />" . mysqli_error($conn));
        }
    }

    $username = $_SESSION["user"]["username"];
    header("location: profile?username=$username");
    exit;
?>