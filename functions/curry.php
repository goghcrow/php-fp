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
    _assertCallable($f, "First arguments");
    if(count($args) >= $requiredParameterCount) {
        return call_user_func_array($f, $args);
    } else {
        return function(/*...$args*/) use($f, $args, $requiredParameterCount) {
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

/**
 * 临时实现~ curry一次的支持占位符的curry函数
 * @return \Closure
 */
function _curryp1(/*$f, ...$args*/) {
    $placeHolder = _(); // fixme

    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");

    return function(/*...$leftArgs*/) use($f, $args, $placeHolder) {
        $leftArgs = func_get_args();
        foreach($args as &$arg) {
            if($arg === $placeHolder) {
                $arg = array_shift($leftArgs);
            }
        }
        unset($arg);
        return call_user_func_array($f, array_merge($args, $leftArgs));
    };
}

/**
 * private
 * @param array $allArgs
 * @param $requiredCount
 * @return bool
 */
function _satisfyArgs(array $allArgs, $requiredCount) {
    $placeHolder = _();
    if(count($allArgs) < $requiredCount) {
        return false;
    }
    foreach($allArgs as $index => $arg) {
        if($arg === $placeHolder) {
            return false;
        }
    }
    return true;
}

/*
 * private
 */
function _curryp($f, array $allArgs, $requiredCount) {
    $placeHolder = _();
    _assertCallable($f, "First arguments");

    return function(/*...$args*/) use($f, $allArgs, $requiredCount, $placeHolder) {
        $args = func_get_args();
        if(($left = count($args)) === 0) {
            return _curryp($f, $allArgs, $requiredCount);
        }

        foreach($allArgs as $index => &$arg) {
            if($arg === $placeHolder) {
                $arg = array_shift($args);
                $left--;
                if($left <= 0) {
                    break;
                }
            }
        }
        unset($arg);

        if($left > 0) {
            $allArgs = array_merge($allArgs, $args);
        }

        if(_satisfyArgs($allArgs, $requiredCount)) {
            return call_user_func_array($f, $allArgs);
        } else {
            return _curryp($f, $allArgs, $requiredCount);
        }
    };
}

/**
 * 支持占位符的curry
 * @Datetime 2016-04-19 0:46
 * @return \Closure
 * 如果需要curry可选参数，则需要手动在可选参数加占位符
 */
function curryp(/*$f, ...$args*/) {
    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");

    $requiredCount = _parameterCount($f, true);
    $left = $requiredCount - count($args);
    if($left > 0) {
        $args = array_merge($args, array_fill(0, $left, _()));
    }
    return _curryp($f, $args, $requiredCount);
}