<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:31
 */
namespace xiaofeng\F\Fn;

// http://php.net/manual/en/function.assert.php#function.assert.expectations
ini_set("zend.assertions", 1);
ini_set("assert.exception", 1);

/**
 * @internal
 * @access private
 * @param mixed $var
 * @param string $what
 * @throws \InvalidArgumentException
 */
function _assertCallable($var, $what) {
    assert(is_callable($var), new \InvalidArgumentException("$what should be callable"));
}

/**
 * @internal
 * @access private
 * @param array $vars
 * @throws \InvalidArgumentException
 */
function _assertAllCallables(array $vars) {
    foreach($vars as $i => $var) {
        _assertCallable($var, "The $i argument");
    }
}

/**
 * @internal
 * @access private
 * @param mixed $var
 * @param string $what
 * @throws \InvalidArgumentException
 */
function _assertIsArrayOrObject($var, $what) {
    assert(is_array($var) || is_object($var), new \InvalidArgumentException("$what should be array or object"));
}

/**
 * @internal
 * @access private
 * @param mixed $var
 * @param string $what
 * @throws \InvalidArgumentException
 */
function _assertNotEmpty($var, $what) {
    assert(!empty($var), new \InvalidArgumentException("$what should be not empty"));
}

/**
 * @internal
 * @access private
 * @param mixed $var
 * @param string $what
 * @throws \InvalidArgumentException
 */
function _assertIsString($var, $what) {
    assert(is_string($var), new \InvalidArgumentException("$what should be string"));
}

/**
 * @internal
 * @access private
 * @param mixed $var
 * @param string $what
 * @throws \InvalidArgumentException
 */
function _assertIsObject($var, $what) {
    assert(is_object($var), new \InvalidArgumentException("$what should be objecct"));
}

/**
 * @internal
 * @access private
 * @param $obj
 * @param $method
 * @throws \InvalidArgumentException
 */
function _assertMethodExist($obj, $method) {
    _assertIsString($method, "Second Argument");
    _assertIsObject($obj, "First Argument");
    assert(method_exists($obj, $method), new \InvalidArgumentException("$method do not exist"));
}