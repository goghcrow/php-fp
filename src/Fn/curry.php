<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:34
 */
namespace xiaofeng\fp\fn;

/**
 * placeholder version 2
 * a longer string, more expensive
 */
const __ = __LINE__ . '$$' . __FILE__; // . '$$' .__NAMESPACE__;

/**
 * placeholder func version 1
 * @deprecated
 * @return string
 */
function _() {
    static $cache = null;
    if($cache === null) {
        $cache = md5(__FILE__) . mt_rand();
    }
    return $cache;
}

/**
 * curry once
 * @param callable|string $f
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 */
function curry1($f) {
    _assertCallable($f, "First arguments");
    return function($a) use($f) {
        return function($b) use($f, $a) {
            return $f($a, $b);
        };
    };
}

/**
 * curry helper
 * @internal
 * @access private
 * @param callable|string $f
 * @param array $args
 * @param int $requiredCount required parameters count
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 */
function _curry($f, array $args, $requiredCount) {
    _assertCallable($f, "First arguments");
    if(count($args) >= $requiredCount) {
        return call_user_func_array($f, $args);
    } else {
        return function(/*...$args*/) use($f, $args, $requiredCount) {
            return _curry($f, array_merge($args, func_get_args()), $requiredCount);
        };
    }
}

/**
 * curry a callable
 * @param callable|string $f
 * @param ...array $args
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 */
function curry(/*$f, ...$args*/) {
    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");
    return _curry($f, $args, _parameterCount($f));
}

/**
 * curry once supporting placeholder __
 * @param callable|string $f
 * @param $ ...array $args
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 */
function curryp1(/*$f, ...$args*/) {
    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");

    /**
     * @param ...array $leftArgs
     * @return mixed
     * @throws \InvalidArgumentException
     */
    return function(/*...$leftArgs*/) use($f, $args) {
        $leftArgs = func_get_args();
        foreach($args as &$arg) {
            if($arg === __) {
                $arg = array_shift($leftArgs);
            }
        }
        unset($arg);
        return call_user_func_array($f, array_merge($args, $leftArgs));
    };
}

/**
 * @internal
 * @access private
 * @param array $allArgs
 * @param int $requiredCount
 * @return bool
 */
function _satisfyArgs(array $allArgs, $requiredCount) {
    if(count($allArgs) < $requiredCount) {
        return false;
    }
    foreach($allArgs as $index => $arg) {
        if($arg === __) {
            return false;
        }
    }
    return true;
}

/**
 * helper of curryp
 * @internal
 * @access private
 * @param callable|string $f
 * @param array $allArgs
 * @param int $requiredCount
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 */
function _curryp($f, array $allArgs, $requiredCount) {
    _assertCallable($f, "First arguments");

    /**
     * @param ...array $args
     * @return \Closure|mixed
     */
    return function(/*...$args*/) use($f, $allArgs, $requiredCount) {
        $args = func_get_args();
        if(($left = count($args)) === 0) {
            return _curryp($f, $allArgs, $requiredCount);
        }

        foreach($allArgs as $index => &$arg) {
            if($arg === __) {
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
 * curry supporting placeholder __
 * @param callable|string $f
 * @param $ ...array $args
 * @return \Closure|mixed
 * @throws \InvalidArgumentException
 *
 * if you need curry optional argument, please put placeholder
 * in position of it manually
 * 如果需要curry可选参数，则需要手动在可选参数加占位符
 */
function curryp(/*$f, ...$args*/) {
    $args = func_get_args();
    $f = array_shift($args);
    _assertCallable($f, "First arguments");

    $requiredCount = _parameterCount($f, true);
    $left = $requiredCount - count($args);
    if($left > 0) {
        $args = array_merge($args, array_fill(0, $left, _));
    }
    return _curryp($f, $args, $requiredCount);
}

/**
 * get args's count of callable
 * @internal
 * @access private
 * @param callable|string $f
 * @param bool $required is Required parameter count
 * @return int
 * @throws \InvalidArgumentException
 *
 * @desc
 *     get args number of closure by reflecting __invoke method
 *     $closure = function($a, $b, $c = 1) {};
 *     is_callable($closure, false, $name);
 *     // result:
 *     $name === "Closure::__invoke";
 *     (new \ReflectionObject($closure))
 *         ->getMethod("__invoke")
 *         ->getNumberOfParameters(); // 3
 *         ->getNumberOfRequiredParameters(); // 2
 */
function _parameterCount($f, $required = true) {
    _assertCallable($f, "First argument");
    if (is_array($f)) {
        $refm = (new \ReflectionClass($f[0]))->getMethod($f[1]);
        return $required ?
            $refm->getNumberOfRequiredParameters() : $refm->getNumberOfParameters();
    }
    if(is_object($f)) {
        // is_callable && is_object
        // 1. $f instanceof \Closure
        // 2. $f implements __invoke
        $refm = (new \ReflectionObject((object)$f))->getMethod("__invoke");
        return $required ?
            $refm->getNumberOfRequiredParameters() : $refm->getNumberOfParameters();
    }
    if(is_string($f)) {
        if(function_exists($f)) {
            $reffn = new \ReflectionFunction($f);
            return $required ?
                $reffn->getNumberOfRequiredParameters() : $reffn->getNumberOfParameters();
        } else {
            list($class, $method) = explode("::", $f);
            $refm = (new \ReflectionClass($class))->getMethod($method);
            return $required ?
                $refm->getNumberOfRequiredParameters() : $refm->getNumberOfParameters();
        }
    }
    throw new \InvalidArgumentException("Should not happen");
}