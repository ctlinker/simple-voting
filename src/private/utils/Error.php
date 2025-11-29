<?php
namespace Utils;

class Error {
    
    public static function invoque(string $message) {
        $_SESSION[""] = $message;
        require_once Path::resolvePrivatePath("view/error.php");
        exit;
    }

}