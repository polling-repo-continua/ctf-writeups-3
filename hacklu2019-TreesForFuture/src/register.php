<?php
/*register.php*/
require_once "../config.php";
require_once "utils.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
        header("Location: /internal/admin", true, 302);
        die();
    } else {
        // We had some issues with our server architecture, but this fixed it.
        $parmas = parse_str(urldecode(file_get_contents("php://input")));
        $pdo = new_database_connection();
        $stmt = $pdo->prepare("select * from members where username=:username");
        if (isset($params["username"]) && is_string($params["username"])) {
            if (isset($params["password"]) && is_string($params["password"])) {
                $stmt->bindParam(":username", $params["username"], PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    $params["password"] = hash("sha512", $params["password"]);
                    $stmt = $pdo->prepare("Insert into members (username, password) values (:username, '{$params['password']}')");
                    $stmt->bindParam(":username", $params["username"], PDO::PARAM_STR);
                    $stmt->execute();
                    if ($stmt->rowCount() === 1) {
                        if (isset($params["auto_login"])) {
                            require_once "login.php";
                            die("Welp, I have no idea what you did, but this code is supposed to be dead.");
                        } else {
                            header("Location: /internal/login");
                            die();
                        }
                    } else {
                        $error = "Something went wrong while creating your account. So no account for you.";
                    }
                } else {
                    $error = "Account already exists";
                }
            } else {
                $error = "Username/Password invalid.";
            }
        } else {
            $error = "Username/Password invalid.";
        }
        header("Location: /internal/login?error=" . urlencode($error));
        die();
    }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
        echo '<p>Already logged in.</p>';
        die();
    } else {
        if (isset($_GET["error"]) && is_string($_GET["error"])) {
            echo '<div class="error"><p>' . htmlentities($_GET["error"]) . '</p></div>';
        }
        echo '<div id="Login" class="">';
        echo '<br>';
        echo '<p>';
        echo '<h1><b> Admin Registration</b></br> </h1>';
        echo '</p>';
        echo '<img src="/internal/img/logo_white.png" alt="Avatar" width="150" height="150">';
        echo '<form action="/internal' . $_SERVER["SCRIPT_NAME"] . '" method="POST">';
        echo '<label for="params[username]"><b>Username</b></label>';
        echo '<br>';
        echo '<input type="text" placeholder="Username" name="params[username]" required>';
        echo '<br>';
        echo '<label for="params[password]"><b>Password</b></label>';
        echo '<br>';
        echo '<input type="password" placeholder="Password" name="params[password]" required>';
        echo '<br>';
        echo '<button type="submit" class="button"><b>Register</b></button>';
        echo '<br>';
        echo '<label><input type="checkbox" checked="checked" name="params[auto_login]">Auto Login</label>';
        echo '</form>';
        echo '</div>';
    }
}