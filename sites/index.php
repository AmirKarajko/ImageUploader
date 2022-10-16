<?php
    // Initialize session
    session_start();

    // Check if user is already logged in
    if (isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
        // Redirect to explore
        header("location: albums");
        exit;
    }

    // Read POST request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get database connection
        require_once(__DIR__ . "/../php/database.php");

        $username = mysqli_real_escape_string($conn, trim($_POST["username"]));
        $password = mysqli_real_escape_string($conn, trim($_POST["password"]));

        // Search for user in the database
        $sql = "SELECT id, username, password, profile_picture FROM users WHERE username = '$username' LIMIT 1";
        if ($result = mysqli_query($conn, $sql)) {
            $rows = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }

            if (count($rows) == 0) {
                $_SESSION["login_error"] = "User does not exist.";
                header("location: /");
                exit;
            }
            else {
                if (strcmp($password, $rows[0]["password"]) != 0) {
                    $_SESSION["login_error"] = "Wrong password.";
                    header("location: /");
                    exit;
                }
            }

            // Save user data
            $_SESSION["user"] = array(
                "id" => $rows[0]["id"],
                "username" => $rows[0]["username"],
                "profile_picture" => $rows[0]["profile_picture"]
            );

            header("location: albums");
            exit();
        }
        else {
            // Return error code 404 when no user is found
            http_response_code(404);
        }
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

        <title>Login | Image Uploader</title>
    </head>
    <body>
        <div class="container">
            <h1>Welcome to Image Uploader</h1>
        </div>

        <div class="container mt-3">
            <div class="row">
                <div class="col">
                    <?php
                        // Show notification on login error
                        if (isset($_SESSION["login_error"])) {
                            $str = "<div class=\"alert alert-danger\">";
                            $str .= $_SESSION["login_error"];
                            $str .= "</div>";

                            $_SESSION["login_error"] = NULL;

                            echo $str;
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="container mt-3">
            <h2>Login</h2>
            <!-- Login Form -->
            <form action="" method="post">
                <!-- Username -->
                <div class="mb-3 mt-3">
                    <label for="username" class="label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required />
                </div>

                <!-- Password -->
                <div class="mb-3 mt-3">
                    <label for="password" class="label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
                </div>

                <!-- Log In Button -->
                <button class="btn btn-primary" type="submit">Log In</button>
            </form>
        </div>
    </body>
</html>