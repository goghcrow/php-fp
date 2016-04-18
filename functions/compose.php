<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:38
 */
namespace xiaofeng\F\Fn;


/**
 * 转换接受多参数函数为接受一个数组参数的函数
 * @param $f
 * @return \Closure
 */
function onePara($f) {
    _assertCallable($f, "First arguments");
    return function(array $args) use($f) {
        return call_user_func_array($f, $args);
    };
}

function compose1($f, $g) {
    _assertCallable($f, "First arguments");
    _assertCallable($g, "Second arguments");
    return function(...$args) use($f, $g) {
        return $f(call_user_func_array($g, func_get_args()));
    };
}

function compose() {
    $fns = func_get_args();
    _assertNotEmpty($fns, "Arguments");
    _assertAllCallables($fns);

    return function(/*...$args*/) use($fns){
        $fn = array_pop($fns);
        $args = call_user_func_array($fn, func_get_args());
        foreach(array_reverse($fns) as $f) {
            $args = $f($args);
        }
        return $args;
    };
}
