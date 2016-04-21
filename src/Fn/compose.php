<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:38
 */
namespace xiaofeng\fp\fn;

/**
 * convert a callable accepting multiple parameters
 * to a closure accepting multiple parameters in a array
 * 转换接受多参数函数为接受一个数组参数的函数
 * 用来传递compose函数返回值与参数转换
 * @param callable|string $f
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function arrayfyArgs($f) {
    _assertCallable($f, "First arguments");
    return function(array $args) use($f) {
        return call_user_func_array($f, $args);
    };
}

/**
 * compose two callables
 * @param callable|string $f
 * @param callable|string $g
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function compose1($f, $g) {
    _assertCallable($f, "First arguments");
    _assertCallable($g, "Second arguments");
    return function(/*...$args*/) use($f, $g) {
        $args = call_user_func_array($g, func_get_args());
        return $f($args);
    };
}

/**
 * compose callables
 * @param $ ...array::callable|string $args
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function compose(/*...$args*/) {
    $fns = array_reverse(func_get_args());
    _assertNotEmpty($fns, "Arguments");
    _assertAllCallables($fns);

    /**
     * @return mixed
     * @param array $args
     */
    return function(/*...$args*/) use($fns){
        $fn = array_shift($fns);
        $args = call_user_func_array($fn, func_get_args());
        foreach($fns as $f) {
            // 返回值直接做参数传递
            // 除非做一个包装类, 否则call_user_func_array($f, $args)
            $args = $f($args);
        }
        return $args;
    };
}

/**
 * pipe two callables
 * @param callable|string $f
 * @param callable|string $g
 * @return \Closure
 * @throws \InvalidArgumentException
 */
function pipe1($f, $g) {
    _assertCallable($f, "First arguments");
    _assertCallable($g, "Second arguments");
    return function(/*...$args*/) use($f, $g) {
        $args = call_user_func_array($f, func_get_args());
        return $g($args);
    };
}

/**
 * pipe callables
 * @return \Closure
 * @param ...array::callable $args
 * @throws \InvalidArgumentException
 */
function pipe(/*...$args*/) {
    $fns = func_get_args();
    _assertNotEmpty($fns, "Arguments");
    _assertAllCallables($fns);

    /**
     * @param ...array $args
     * @return mixed
     */
    return function(/*...$args*/) use($fns){
        $fn = array_shift($fns);
        $args = call_user_func_array($fn, func_get_args());
        foreach($fns as $f) {
            $args = $f($args);
        }
        return $args;
    };
}
