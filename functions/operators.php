<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:35
 */
namespace xiaofeng\F\Fn;

// init
if(_Storage::$_init === false) {
    _Storage::init();
    _Storage::$_init = true;
}

/**
 * 生成常见operator的柯里化闭包，或直接执行op
 * op函数本身至少接受一个参数，op名称，剩余参数支持curry
 * o(╯□╰)o 肿么这么绕,还是直接看Fn_test的示例吧~~~
 *
 * @return mixed|\Closure
 * @author nikic, xiaofeng
 * 借鉴了nikic大神的思路
 * https://github.com/nikic/iter/blob/master/src/iter.fn.php#L68
 * 1. 将局部变量转移到类静态变量，存储一份
 * 2. 全部返回curry函数
 * 3. 添加若干操作符
 * 4. ...
 * Hindley–Milner 的不定参数肿么描述?!
 * op :: string -> [a -> ... -> z]
 */
function op(/*$op, ...$args*/) {
    $args = func_get_args();
    if(empty($args)) {
        throw new \InvalidArgumentException("Required at least one op parameter");
    }
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


/*private*/
class _Storage
{
    /*private*/
    public static $_init = false;
    /*private*/
    public static $_operators = [];
    /*private*/
    public static function init() {
        self::$_operators = [
            "instanceof" => _curry(function($a, $b) { return $a instanceof $b; }, [], 2), // op("instanceof") :: a -> b -> boolean
            "*"   => _curry(function($a, $b) { return $a *   $b; }, [], 2), // op("*") :: number -> number -> number
            "/"   => _curry(function($a, $b) { return $a /   $b; }, [], 2), // op("/") :: number -> number -> number
            "%"   => _curry(function($a, $b) { return $a %   $b; }, [], 2), // op("%") :: int -> int -> int
            "+"   => _curry(function($a, $b) { return $a +   $b; }, [], 2), // op("+") :: number -> number -> number
            "-"   => _curry(function($a, $b) { return $a -   $b; }, [], 2), // op("-") :: number -> number -> number
            "."   => _curry(function($a, $b) { return $a .   $b; }, [], 2), // op(".") :: string -> string -> string
            "<<"  => _curry(function($a, $b) { return $a <<  $b; }, [], 2), // op("<<") :: int -> int -> int
            ">>"  => _curry(function($a, $b) { return $a >>  $b; }, [], 2), // op(">>") :: int -> int -> int
            "<"   => _curry(function($a, $b) { return $a <   $b; }, [], 2), // op("<") :: number -> number -> number
            "<="  => _curry(function($a, $b) { return $a <=  $b; }, [], 2), // op("<=") :: number -> number -> number
            ">"   => _curry(function($a, $b) { return $a >   $b; }, [], 2), // op(">") :: number -> number -> number
            ">="  => _curry(function($a, $b) { return $a >=  $b; }, [], 2), // op(">=") :: number -> number -> number
            "=="  => _curry(function($a, $b) { return $a ==  $b; }, [], 2), // op("==") :: a -> a -> a
            "!="  => _curry(function($a, $b) { return $a !=  $b; }, [], 2), // op("!=") :: a -> a -> a
            "===" => _curry(function($a, $b) { return $a === $b; }, [], 2), // op("===") :: a -> a -> a
            "!==" => _curry(function($a, $b) { return $a !== $b; }, [], 2), // op("!==") :: a -> a -> a
            "&"   => _curry(function($a, $b) { return $a &   $b; }, [], 2), // op("&") :: int -> int -> int
            "^"   => _curry(function($a, $b) { return $a ^   $b; }, [], 2), // op("^") :: int -> int -> int
            "|"   => _curry(function($a, $b) { return $a |   $b; }, [], 2), // op("|") :: int -> int -> int
            "&&"  => _curry(function($a, $b) { return $a &&  $b; }, [], 2), // op("&&") :: boolean -> boolean -> boolean
            "||"  => _curry(function($a, $b) { return $a ||  $b; }, [], 2), // op("||") :: boolean -> boolean -> boolean
            "**"  => _curry(function($a, $b) { return \pow($a, $b); }, [], 2), // PHP7 op("**") :: number -> number -> number
            "<=>" => _curry(function($a, $b) { return $a == $b ? 0 : ($a < $b ? -1 : 1); }, [], 2), // PHP7 op("<=>") :: number -> number -> int

            // op("id") :: a -> a
            "id"  => _curry(function($var) { return $var; }, [], 1), // identity

            // not
            // op("!") :: ([a -> ... -> z] -> boolean) -> [a -> ... -> z] -> boolean
            "!"   => function(/*$f, ...$args*/) {
                $args = func_get_args();
                $f = array_shift($args);
                _assertCallable($f, "First arguments");
                return _curry(_not($f), $args, _parameterCount($f));
            },

            // 属性访问
            // op("->") :: string -> object -> a
            "->"  => _curry(function($prop, $obj)  {
                return isset($obj->$prop)  ? $obj->$prop : null;
            }, [], 2),

            // 数组下标key方法
            // op("[]") :: string|number -> array -> a
            "[]"  => _curry(function($index, $arr) {
                return isset($arr[$index]) ? $arr[$index] : null;
            }, [], 2),

            // nested ->
            // op("...->") :: array(string) -> object -> a
            "...->"  => _curry(function(array $properties, $object) {
                foreach ($properties as $property) {
                    $object = $object->$property;
                }
                return $object;
            }, [], 2),

            // nested []
            // op("...[]") :: array(string|number) -> array -> a
            "...[]"  => _curry(function(array $indexes, $array) {
                foreach ($indexes as $index) {
                    $array = $array[$index];
                }
                return $array;
            }, [], 2),

            // isset
            // op("?") :: string|number -> a -> boolean
            "?" => _curry(function($key, $var) {
                // _assertIsArrayOrObject($var, "Second argument");
                if(is_array($var))  return isset($var[$key]);
                if(is_object($var)) return isset($var->$key);
                return isset($var);
            }, [], 2),
        ];
    }
}

/**
 * private
 * not helper
 * @param $f
 * @return \Closure
 */
function _not($f) {
    _assertCallable($f, "First arguments");
    return function(/*...$args*/) use($f) {
        return !call_user_func_array($f, func_get_args());
    };
}