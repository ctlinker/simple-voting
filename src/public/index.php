<?php

require __DIR__ . "/../../vendor/autoload.php";

use Mock\MyClass;

$m = new MyClass();
echo $m->greet();
