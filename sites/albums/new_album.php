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

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $counter = 1;
        $name_exists = true;
        $name = "album";

        while ($name_exists) {
            if ($counter > 1) {
                $name = "album_" . $counter;
            }

            $sql = "SELECT name FROM albums WHERE name LIKE ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $name);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $n_rows = mysqli_stmt_num_rows($stmt);

            $name_exists = $n_rows > 0;

            $stmt->close();
            $conn->next_result();

            $counter++;
        }

        echo $name;

        $sql = "INSERT INTO albums (name, description, created_by) VALUES (?, '', ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $name, $_SESSION["user"]["id"]);
        }
        mysqli_stmt_execute($stmt);

        $last_id = $conn->insert_id;
        header("location: album?id=$last_id");
        exit;
    }
?>