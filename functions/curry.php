<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:34
 */
namespace xiaofeng\F\Fn;

/**
 * @param $f
 * @return \Closure
 */
function _curry1($f) {
    _assertCallable($f, "First arguments");
    return function($a) use($f) {
        return function($b) use($f, $a) {
            return $f($a, $b);
        };
    };
}

/**
 * private
 * curry helper
 * @param $f
 * @param array $args
 * @param $requiredParameterCount
 * @return \Closure|mixed
 */
function _curry($f, array $args, $requiredParameterCount) {
    if(count($args) >= $requiredParameterCount) {
        return call_user_func_array($f, $args);
    } else {
        return function() use($f, $args, $requiredParameterCount) {
            return _curry($f, array_merge($args, func_get_args()), $requiredParameterCount);
        };
    }
}

/**
 * @return \Closure|mixed
 */
function curry(/*$f, ...$args*/) {
    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");
    return _curry($f, $args, _parameterCount($f));
}