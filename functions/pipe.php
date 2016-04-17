<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:38
 */
namespace xiaofeng\F\Fn;

function pipe1(/*callable*/ $f, /*callable*/ $g) {
    _assertCallable($f, "First arguments");
    _assertCallable($g, "Second arguments");
    // fixme
}

function pipe() {
    $fns = func_get_args();
    _assertAllCallables($fns);

    // fixme
}
