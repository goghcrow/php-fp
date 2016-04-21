<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:35
 */
namespace xiaofeng\F\Fn;

/**
 * 生成常见operator的柯里化闭包，或直接执行op
 * op函数本身至少接受一个参数，op名称，剩余参数支持curry,支持占位符
 * o(╯□╰)o 肿么这么绕,还是直接看Fn_test的示例吧~~~
 *
 * @param string $op name of operators
 * @param $ ...array $args
 * @return \Closure|mixed
 * @author nikic, xiaofeng
 *
 * 借鉴了nikic大神的思路
 * https://github.com/nikic/iter/blob/master/src/iter.fn.php#L68
 * 1. 将局部变量转移到类静态变量，存储一份
 * 2. 全部返回curry函数
 * 3. 添加若干操作符
 * 4. ...
 */
function op(/*$op, ...$args*/) {
    $args = func_get_args();
    _assertNotEmpty($args, "First Arguments");
    $op = array_shift($args);
    if (!isset(_Storage::$_operators[$op])) {
        throw new \InvalidArgumentException("Unknown operator \"$op\"");
    }
    $fn = _Storage::$_operators[$op];
    if(empty($args)) {
        return $fn;
    }
    return call_user_func_array($fn, $args);
}

/**
 * initialize the storage of operators
 */
if(_Storage::$_init === false) {
    _Storage::init();
    _Storage::$_init = true;
}

/**
 * Class _Storage
 * @access private
 * @package xiaofeng\F\Fn
 */
class _Storage
{
    /**
     * @access private
     * @var bool
     */
    public static $_init = false;
    /**
     * @access private
     * @var array
     */
    public static $_operators = [];

    /**
     * @access private
     */
    public static function init() {
        self::$_operators = [
            "instanceof" => _curryp(function($a, $b) { return $a instanceof $b; }, [], 2), // op("instanceof") :: a -> b -> boolean
            "*"   => _curryp(function($a, $b) { return $a *   $b; }, [], 2), // op("*") :: number -> number -> number
            "/"   => _curryp(function($a, $b) { return $a /   $b; }, [], 2), // op("/") :: number -> number -> number
            "%"   => _curryp(function($a, $b) { return $a %   $b; }, [], 2), // op("%") :: int -> int -> int
            "+"   => _curryp(function($a, $b) { return $a +   $b; }, [], 2), // op("+") :: number -> number -> number
            "-"   => _curryp(function($a, $b) { return $a -   $b; }, [], 2), // op("-") :: number -> number -> number
            "."   => _curryp(function($a, $b) { return $a .   $b; }, [], 2), // op(".") :: string -> string -> string
            "<<"  => _curryp(function($a, $b) { return $a <<  $b; }, [], 2), // op("<<") :: int -> int -> int
            ">>"  => _curryp(function($a, $b) { return $a >>  $b; }, [], 2), // op(">>") :: int -> int -> int
            "<"   => _curryp(function($a, $b) { return $a <   $b; }, [], 2), // op("<") :: number -> number -> number
            "<="  => _curryp(function($a, $b) { return $a <=  $b; }, [], 2), // op("<=") :: number -> number -> number
            ">"   => _curryp(function($a, $b) { return $a >   $b; }, [], 2), // op(">") :: number -> number -> number
            ">="  => _curryp(function($a, $b) { return $a >=  $b; }, [], 2), // op(">=") :: number -> number -> number
            "=="  => _curryp(function($a, $b) { return $a ==  $b; }, [], 2), // op("==") :: a -> a -> a
            "!="  => _curryp(function($a, $b) { return $a !=  $b; }, [], 2), // op("!=") :: a -> a -> a
            "===" => _curryp(function($a, $b) { return $a === $b; }, [], 2), // op("===") :: a -> a -> a
            "!==" => _curryp(function($a, $b) { return $a !== $b; }, [], 2), // op("!==") :: a -> a -> a
            "&"   => _curryp(function($a, $b) { return $a &   $b; }, [], 2), // op("&") :: int -> int -> int
            "^"   => _curryp(function($a, $b) { return $a ^   $b; }, [], 2), // op("^") :: int -> int -> int
            "|"   => _curryp(function($a, $b) { return $a |   $b; }, [], 2), // op("|") :: int -> int -> int
            "&&"  => _curryp(function($a, $b) { return $a &&  $b; }, [], 2), // op("&&") :: boolean -> boolean -> boolean
            "||"  => _curryp(function($a, $b) { return $a ||  $b; }, [], 2), // op("||") :: boolean -> boolean -> boolean
            "**"  => _curryp(function($a, $b) { return \pow($a, $b); }, [], 2), // PHP7 op("**") :: number -> number -> number
            "<=>" => _curryp(function($a, $b) { return $a == $b ? 0 : ($a < $b ? -1 : 1); }, [], 2), // PHP7 op("<=>") :: number -> number -> int

            // op("id") :: a -> a
            "id"  => _curryp(function($var) { return $var; }, [], 1), // identity

            // op("if") :: boolean -> a -> b
            "if"  => _curryp(function($predicate, $t, $f) {
                return $predicate ? $t : $f;
            }, [], 3),

            // not
            // op("!") :: ([a -> ... -> z] -> boolean) -> [a -> ... -> z] -> boolean
            "!"   => function(/*$f, ...$args*/) {
                $args = func_get_args();
                $f = array_shift($args);
                _assertCallable($f, "First arguments");
                return _curryp(not($f), $args, _parameterCount($f));
            },

            // visit property
            // op("->") :: string -> object -> a
            "->"  => _curryp(function($prop, $obj)  {
                return isset($obj->$prop)  ? $obj->$prop : null;
            }, [], 2),

            // invoke method
            // op("::") :: (array -> a) -> array -> object -> a
            "::"  => _curryp(function($methodName, $args, $obj) {
                _assertMethodExist($obj, $methodName);
                if(!is_array($args)) {
                    $args = [$args];
                }
                return call_user_func_array([$obj, $methodName], $args);
            }, [], 3),

            // visit array
            // op("[]") :: string|number -> array -> a
            "[]"  => _curryp(function($index, $arr) {
                return isset($arr[$index]) ? $arr[$index] : null;
            }, [], 2),

            // visit property nestedly
            // op("...->") :: array(string) -> object -> a
            "...->"  => _curryp(function(array $properties, $object) {
                foreach ($properties as $property) {
                    $object = $object->$property;
                }
                return $object;
            }, [], 2),

            //  visit array nestedly
            // op("...[]") :: array(string|number) -> array -> a
            "...[]"  => _curryp(function(array $indexes, $array) {
                foreach ($indexes as $index) {
                    $array = $array[$index];
                }
                return $array;
            }, [], 2),

            // isset
            // op("?") :: string|number -> a -> boolean
            "?" => _curryp(function($key, $var) {
                // _assertIsArrayOrObject($var, "Second argument");
                if(is_array($var))  return isset($var[$key]);
                if(is_object($var)) return isset($var->$key);
                return isset($var);
            }, [], 2),
        ];
    }
}