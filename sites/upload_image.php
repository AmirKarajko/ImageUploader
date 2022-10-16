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

            $album_id = mysqli_real_escape_string($conn, $_POST["id"]);

            $filename = $_FILES["upload"]["name"];
            $tmpName = $_FILES["upload"]["tmp_name"];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            $fileType = $_FILES["upload"]["type"];
            $data = addslashes(file_get_contents($tmpName));

            $sql = "INSERT INTO images(filename, extension, mime, data, uploader, album_id) VALUES ('{$filename}', '{$extension}', '{$fileType}', '{$data}', '{$_SESSION["user"]["id"]}', '{$album_id}')";

            mysqli_query($conn, $sql) or die("<b>Error:</b> Problem on File Upload<br />" . mysqli_error($conn));
        }
    }

    header("location: album?id=" . $album_id);
    exit;
?>