<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:33
 */
namespace xiaofeng\F\Fn;

/**
 * private
 * 获取callable参数
 * @param $f
 * @param bool $required
 * @return int
 * @author xiaofeng
 *
 * $closure = function($a, $b, $c = 1) {};
 * is_callable($closure, false, $name);
 * $name === "Closure::__invoke";
 * (new \ReflectionObject($closure))->getMethod("__invoke")
 *     ->getNumberOfParameters(); // 3
 *     ->getNumberOfRequiredParameters(); // 2
 */
function _parameterCount($f, $required = true) {
    _assertCallable($f, "First argument");
    if (is_array($f)) {
        $refm = (new \ReflectionClass($f[0]))->getMethod($f[1]);
        return $required ?
            $refm->getNumberOfRequiredParameters() : $refm->getNumberOfParameters();
    }
    if(is_object($f) && ($f instanceof \Closure)) {
        $refm = (new \ReflectionObject($f))->getMethod("__invoke");
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