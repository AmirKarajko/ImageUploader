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
        "{\b(images)\b}" => __DIR__ . "/sites/images.php",
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