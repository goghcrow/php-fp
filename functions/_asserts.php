<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:31
 */
namespace xiaofeng\F\Fn;

/**
 * private
 * @param $var
 * @param $what
 */
function _assertCallable($var, $what) {
    if(!is_callable($var)) {
        throw new \InvalidArgumentException("$what should be callable");
    }
}

/**
 * private
 * @param array $vars
 */
function _assertAllCallables(array $vars) {
    foreach($vars as $i => $var) {
        _assertCallable($var, "The $i argument");
    }
}

/**
 * private
 * @param $var
 * @param $what
 */
function _assertIsArrayOrObject($var, $what) {
    if(!is_array($var) && !is_object($var)) {
        throw new \InvalidArgumentException("$what should be array or object");
    }
}

/**
 * private
 * @param $var
 * @param $what
 */
function _assertNotEmpty($var, $what) {
    if(empty($var)) {
        throw new \InvalidArgumentException("$what should be not empty");
    }
}