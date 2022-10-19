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
        $errors = array();
        $maxsize = 10000000;
        $acceptable = array(
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

        if (($_FILES['upload']['size'] >= $maxsize) || ($_FILES["upload"]["size"] == 0)) {
            $errors[] = "File too large. File must be less than 10 megabytes.";
        }

        if ((!in_array($_FILES['upload']['type'], $acceptable)) && (!empty($_FILES["upload"]["type"]))) {
            $errors[] = "Invalid file type. Only BMP, GIF, JPG, JPEG, PNG types are accepted.";
        }

        if (count($errors) === 0) {
            // Upload selected file
            if (is_uploaded_file($_FILES["upload"]["tmp_name"])) {
                require_once(__DIR__ . "/../../php/database.php");

                $filename = $_FILES["upload"]["name"];
                $tmpName = $_FILES["upload"]["tmp_name"];
                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                $fileType = $_FILES["upload"]["type"];
                $data = addslashes(file_get_contents($tmpName));

                $fileFormat = "";
                if (substr($filename, -3) == "bmp" || substr($filename, -3) == "gif" || substr($filename, -3) == "jpg" || substr($filename, -4) == "jpeg" || substr($filename, -3) == "png") {
                    $sql = "INSERT INTO profile_pictures(filename, extension, mime, data, uploaded_by) VALUES ('{$filename}', '{$extension}', '{$fileType}', '{$data}', '{$_SESSION["user"]["id"]}')";

                    mysqli_query($conn, $sql) or die("<b>Error:</b> Problem on File Upload<br />" . mysqli_error($conn));
                }
            }
        }
        else {
            foreach($errors as $error) {
                echo $error . "<br />";
            }
            
            die();
        }
    }

    header("location: profile_picture?id=" . $_SESSION["user"]["id"]);
    exit;
?>