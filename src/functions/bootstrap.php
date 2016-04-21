<?php
/**
 * User: xiaofeng
 * Date: 2016/4/16
 * Time: 1:05
 *
 * require bootstrap
 */
if (version_compare(PHP_VERSION, "7.0.0") >= 0) {
    require __DIR__ . DIRECTORY_SEPARATOR . "_asserts7.php";
} else {
    require __DIR__ . DIRECTORY_SEPARATOR . "_asserts5.php";
}
require __DIR__ . DIRECTORY_SEPARATOR . "functors.php";
require __DIR__ . DIRECTORY_SEPARATOR . "fn.php";
require __DIR__ . DIRECTORY_SEPARATOR . "curry.php";
require __DIR__ . DIRECTORY_SEPARATOR . "compose.php";
require __DIR__ . DIRECTORY_SEPARATOR . "operators.php";
