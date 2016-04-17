<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:38
 */
namespace xiaofeng\F\Fn;

function compose1($f, $g) {
    _assertCallable($f, "First arguments");
    _assertCallable($g, "Second arguments");
    // fixme
}

function compose() {
    $fns = func_get_args();
    _assertNotEmpty($fns, "Arguments");
    _assertAllCallables($fns);

    return function() use($fns){
        $fn = array_pop($fns);
        $ret = call_user_func_array($fn, func_get_args());
        foreach(array_reverse($fns) as $f) {
            // fixme 约定使用数组返回值，则可以使用
            // call_user_func_array($fn, $ret);
            $ret = $f($ret);
        }
        return $ret;
    };
}
