<?php
/**
 * User: xiaofeng
 * Date: 2016/4/16
 * Time: 1:05
 */
namespace xiaofeng\F\Fn;
use xiaofeng\F;

/**
 * @param callable|string $f
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function not($f) {
    _assertCallable($f, "First arguments");
    return function(/*...$args*/) use($f) {
        return !call_user_func_array($f, func_get_args());
    };
}

/**
 * get a \Closure returning $predicate
 *      binging two internal variable ahead of invoking
 *      1. $iter: the first of argument orElse null by invoking
 *      2. $args: array of all arguments by invoking
 * @param string $predicate
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function where($predicate) {
    _assertIsString($predicate, "First arguments");
    // $funcStr = "return function() use(\$args, \$iter) { return $predicate; };";
    return function() use(/*$funcStr*/$predicate) {
        $args = func_get_args();
        $iter = isset($args[0]) ? $args[0] : null;
        // $f = eval($funcStr);
        return eval("return $predicate;");
    };
}

/**
 * map a callable to a array
 * @param callable|string $f
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function mapf($f) {
    _assertCallable($f, "First arguments");
    return function(array $arr) use($f) {
        return array_map($f, $arr);
    };
}

// fixme
//function fmap($f, F\Functor $functor) {
//    _assertCallable($f, "First arguments");
//    return $functor($f);
//}

//function amap() {
//
//}