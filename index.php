<?php
    $request_url = $_SERVER["REQUEST_URI"];

    switch($request_url) {
        case "":
        case "/":
            require __DIR__ . "/sites/index.php";
            return;
        default:
            break;
    }

    $pos = strpos($request_url, "?");
    if ($pos !== false) {
        $request_url = substr($request_url, 0, $pos);
    }

    $rules = array(
        "{\b(albums)\b}" => __DIR__ . "/sites/albums.php",
        "{\b(album)\b}" => __DIR__ . "/sites/album.php",
        "{\b(new_album)\b}" => __DIR__ . "/sites/new_album.php",
        "{\b(save_album)\b}" => __DIR__ . "/sites/save_album.php",
        "{\b(delete_album)\b}" => __DIR__ . "/sites/delete_album.php",
        "{\b(upload_image)\b}" => __DIR__ . "/sites/upload_image.php",
        "{\b(view_image)\b}" => __DIR__ . "/sites/view_image.php",
        "{\b(download_image)\b}" => __DIR__ . "/sites/download_image.php",
        "{\b(delete_image)\b}" => __DIR__ . "/sites/delete_image.php",
        "{\b(logout)\b}" => __DIR__ . "/sites/logout.php"
    );

    $found = false;
    foreach ($rules as $pattern => $target) {
        if (preg_match($pattern, $request_url, $params)) {
            require $target;
            $found = true;
            return;
        }
    }
    if (!$found) {
        http_response_code(404);
//      require __DIR__ . "/sites/error404.php";
    }
?>