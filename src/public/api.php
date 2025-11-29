<?php
    require_once "./deps.php";
    session_start();
    use Utils\Path;
    use Utils\Error;

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

    if($request == "error-test") {
        Error::invoque("Message d'erreur de test");
        exit();
    }

    if($request == "error") {
        require_once Path::resolvePrivatePath("view/error.php");
        exit();
    }

    if($request == "result") {
        require_once Path::resolvePrivatePath("view/result.php");
        exit();
    }

    if($request == "vote") {
        require_once Path::resolvePrivatePath("view/vote.php");
        exit();
    }

    if($request == "submit_vote") {
        require_once Path::resolvePrivatePath("view/submit.php");
        exit();
    }

    Error::invoque("Requette non supporter");
?>