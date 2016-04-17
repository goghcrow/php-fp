<?php
/**
 * User: xiaofeng
 * Date: 2016/4/16
 * Time: 1:05
 */
namespace xiaofeng\F\Fn;
use xiaofeng\F;

function fmap($f, F\Functor $functor) {
    _assertCallable($f, "First arguments");
    return $functor($f);
}

function amap() {

}

function _if($f) {
    _assertCallable($f, "First arguments");
    return function($t, $f) use($f) {
        $t = is_callable($t) ? $t() : $t;
        $f = is_callable($f) ? $f() : $f;
        return $f() ? $t : $f;
    };
}

function flip($f) {
    _assertCallable($f, "First argument");
    return function() use($f) {
        return call_user_func_array($f, array_reverse(func_get_args()));
    };
}

// fixme
function method($method, ...$args) {
    return function($obj) use($method, $args) {
        return $obj->$method(...$args);
    };
}

//function is($what, $arg = null) {
//    array_filter(get_defined_functions()["internal"], function($f) {
//        return 0 === strncasecmp("array", $f, strlen("array"));});
//
//    $whats = [
//        "even" => function() {},
//        "int"
//    ];
//
//    if (!isset($functions[$op])) {
//        throw new \InvalidArgumentException("Unknown operator \"$op\"");
//    }
//
//    $fn = $functions[$op];
//    if (func_num_args() === 1) {
//        return $fn;
//    } else {
//        return function($a) use ($fn, $arg) {
//            return $fn($a, $arg);
//        };
//    }
//}