<?php

namespace Utils;

class Path{
    public static function resolvePrivatePath(string $path){
        return __DIR__ . "/" . ".." . "/" . $path;
    }

    public static function resolvePublicPath(string $path){
        return __DIR__ . "/" . ".." . "/" . ".."  . "/" . "public" . "/" . $path;
    }
}