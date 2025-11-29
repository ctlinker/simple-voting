<?php
    require_once "./deps.php";
    session_start();
    use Utils\Path;

   (string) $request = $_GET["request"];

    if(!isset($request) || !is_string($request)) {
        exit();
    }

    if($request == "test") {
        echo "Api Test Passed";
        exit();
    }

    if($request == "admin") {
        require_once Path::resolvePrivatePath("view/admin.php");
        exit();
    }
?>