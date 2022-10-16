<?php
    // Initialize session
    session_start();

    // Check if user is not logged in
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        // Redirect to index
        header("location: /");
        exit;
    }

    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        exit;
    }

    $image_id = $_GET["id"];

    echo <<<HTML
                <html>
                    <head>
                        <link rel="icon" type="image/png" href="../favicon.png">
                        <title>View Image | Image Uploader</title>
                    </head>
                    <body>
                        <img src="download_image?id=$image_id" />
                    </body>
                </html>
            HTML;
?>