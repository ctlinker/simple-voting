<?php
use DB\Database;
use Utils\Error;
use Utils\Path;

// Middelware logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["admin_username"];
    $password = $_POST["admin_password"];

    $user = Database::fetch(
        "SELECT * FROM admin_users WHERE username = :username AND password = :password",
        [
            ":username" => $username,
            ":password" => $password,
        ],
    );

    if ($user) {
        $_SESSION["user_id"] = $user["id"];
        require_once Path::resolvePrivatePath("view/dashboard.php");
        exit();
    } else {
        Error::invoque("Mot de passe invalide ou Username Invalide");
        exit();
    }
}
