# php-fp

something about fp in php

~~~ php
namespace test
{
    class C
    {
        public function m($a, $b = null) {}
        public static function sm($a, $b = null) {}
    }

    function f($a, $b = null) {}

    interface M
    {
        public function say($msg);
    }

    class C1 implements M
    {
        public function say($msg) {
            return __CLASS__ . ":" . $msg;
        }
    }

    class C2 implements M
    {
        public function say($msg) {
            return __CLASS__ . ":" . $msg;
        }
    }
}

namespace xiaofeng
{
    error_reporting(E_ALL);
    require __DIR__ . "/fn/bootstrap.php";
    use xiaofeng\fp as F;
    use xiaofeng\fp\fn as fn;
    use test;


    use const xiaofeng\fp\fn\__;
    use function xiaofeng\fp\fn\{
        op, not, where, mapf,
        compose, pipe, compose1, pipe1, arrayfyArgs,
        curry1, curry, curryp1, curryp
    };

    ini_set("zend.assertions", 1);
    ini_set("assert.exception", 1);

    // class_alias for simplify usage of namespace

    // fn\op
    ///////////////////////////////////////////////////////////////////////////
    $id = op("id");
    assert($id("hello") === "hello");
    assert(op("id", "hello") === "hello");

    $plus = op("+");
    $plus1 = $plus(1);
    $plus5 = op("+", 5);

    assert($plus instanceof \Closure);
    assert($plus1 instanceof \Closure);
    assert($plus5 instanceof \Closure);

    assert($plus1(5) === 6);
    assert(op("+", 1, 3) == 4);
    assert($plus5(1) === 6);

    $arr = [7,8,9];
    $index = op("[]");
    $head = op("[]", 0);
    assert($head($arr) === 7);
    assert($head([]) === null);
    assert($index(2, $arr) === 9);
    assert(op("[]", 2, $arr) === 9);
    $isset = op("?");
    $isset4 = op("?", 3);
    assert($isset(2, $arr) === true);
    assert($isset4($arr) === false);

    $obj = (object)["name"=>"xiaofeng", "age"=>26];
    $prop = op("->");
    $name = op("->", "name");
    assert($name($obj) === "xiaofeng");
    assert($name((object)[]) === null);
    assert($prop("age", $obj) === 26);
    assert(op("->", "age", $obj) === 26);
    $issetName = op("?", "name");
    assert($issetName($obj) === true);

    $table = [["name"=>"xiaofeng1", "age"=>26], ["name"=>"xiaofeng2", "age"=>26]];
    $secondName = op("...[]", [1, "name"]);
    assert($secondName($table) === "xiaofeng2");

    $say = op("::", "say");
    $sayHello = $say("hello");
    assert($sayHello(new test\C1) === "test\\C1:hello");
    assert($sayHello(new test\C2) === "test\\C2:hello");
    // \ReflectionFunction::export($say);
    $c1SaySth = $say(__, new test\C1);
    $c2SaySth = $say(__, new test\C2);
    assert($c1SaySth("bye~") === "test\\C1:bye~");
    assert($c2SaySth("bye!") === "test\\C2:bye!");


    $ifelse = op("if");
    assert($ifelse(true, 1, 2) === 1);
    assert($ifelse(false, 1, 2) === 2);

    $if = op("if", __, ":-D", "%>_<%");
    assert($if(true) === ":-D");
    assert($if(false) === "%>_<%");

    // not curry not
    ///////////////////////////////////////////////////////////////////////////
    $allOdd = function($a, $b, $c) {
        return ($a & 1) === 1 && ($b & 1) === 1 && ($c & 1) === 1;
    };
    assert($allOdd(1,3,5) === true);
    assert($allOdd(1,3,4) === false);

    $curryNot = op("!");
    $notIsInt = $curryNot("is_int");
    assert($notIsInt(4) === false);
    // assert(op("!", "is_string")(1) === true);

    $notAllOdd = op("!", $allOdd);
    assert($notAllOdd(1,3,5) === false);
    assert($notAllOdd(1,3,4) === true);

    $notAllleft2Odd = op("!", $allOdd, 1);
    $wtfName = $notAllleft2Odd(3);
    assert($notAllleft2Odd instanceof \Closure);
    assert($notAllleft2Odd(3,5) === false);
    assert($notAllleft2Odd(3,4) === true);
    assert($notAllleft2Odd(2,4) === true);
    assert($wtfName(5) === false);
    assert($wtfName(4) === true);

    // _parameterCount
    ///////////////////////////////////////////////////////////////////////////
    assert(fn\_parameterCount(["test\\C", "m"]) === 1);
    assert(fn\_parameterCount([new test\C, "m"]) === 1);
    assert(fn\_parameterCount(function($a, $b = null) {}) === 1);
    assert(fn\_parameterCount("\\array_map") === 2);
    assert(fn\_parameterCount("test\\c::sm") === 1);

    assert(fn\_parameterCount(["test\\C", "m"], false) === 2);
    assert(fn\_parameterCount([new test\C, "m"], false) === 2);
    assert(fn\_parameterCount(function($a, $b = null) {}, false) === 2);
    assert(fn\_parameterCount("\\array_map", false) === 2);
    assert(fn\_parameterCount("test\\c::sm", false) === 2);

    // curry
    ///////////////////////////////////////////////////////////////////////////
    function _testSum($sumfn) {
        $add1 = $sumfn(1);
        assert($add1(2) === 3);
        // assert($sum(1)(2) === 3);
        assert($add1(2, 10) === 13);
    }

    function sumfn($a, $b, $c = 0) {
        return $a + $b + $c;
    }
    class Sum {
        public function call($a, $b, $c = 0) {
            return sumfn($a, $b, $c);
        }
        public static function scall($a, $b, $c = 0) {
            return sumfn($a, $b, $c);
        }
    }
    _testSum(curry("xiaofeng\\sumfn"));
    _testSum(curry([new Sum(), "call"]));
    _testSum(curry(["xiaofeng\\Sum", "scall"]));
    _testSum(curry("xiaofeng\\Sum::scall"));

    $add1 = curry("xiaofeng\\sumfn", 1);
    assert($add1(10) === 11);

    $add  = curry1(function($a, $b) { return $a + $b; });
    $add100 = $add(100);
    assert($add100(1) === 101);

    // compose
    ///////////////////////////////////////////////////////////////////////////
    $add = op("+");
    $oneParaAdd = arrayfyArgs($add);
    assert($add(1, 2) === $oneParaAdd([1, 2]));

    $strlenAdd1 = compose1(op("+", 1), "strlen");
    assert($strlenAdd1("hello") === strlen("hello") + 1);

    $strlenAdd1 = compose(op("+", 1), "strlen");
    assert($strlenAdd1("hello") === strlen("hello") + 1);

    $persons = [["name"=>"xiaofeng1"],["name"=>"xiaofeng2"],["name"=>"foo"],["name"=>"bar"]];
    $sum = curryp("\\array_reduce", __, op("+"), 0);
    $removeOdd = curryp("\\array_filter", __, where('$iter > 3'));
    $getName = curry("\\array_map", op("[]", "name"));
    $f = compose($sum, $removeOdd, mapf("\\strlen"), $getName);
    assert($f($persons) === 18);
    $f = pipe($getName, mapf("\\strlen"), $removeOdd, $sum);
    assert($f($persons) === 18);

    $strncasecmpArray = curryp("\\strncasecmp", "array", __, strlen("array"));
    $startWithArray = compose1(op("===", 0), $strncasecmpArray);
    $getArrayFunc = curryp("\\array_filter", __, $startWithArray);
    $f = compose1("\\print_r", $getArrayFunc);
    $f = pipe1($getArrayFunc, "\\print_r");
    // print all internal array functions
    // $f(get_defined_functions()["internal"]);

    // where
    ///////////////////////////////////////////////////////////////////////////
    $isEven = where('$iter % 2 === 0');
    assert($isEven(2) === true);
    assert($isEven(3) === false);

    // curryp
    ///////////////////////////////////////////////////////////////////////////
    $isEven = function($a) { return ($a & 1) === 0; };

    $getEven = curryp1("array_filter", __, $isEven);
    assert($getEven(range(1, 10)) === [1=>2,3=>4,5=>6,7=>8,9=>10]);

    $charAt0 = curryp1("\\substr", __, 0, 1);
    assert($charAt0("HELLO") === "H");

    $p = __;
    assert(fn\_satisfyArgs([$p], 2) === false);
    assert(fn\_satisfyArgs([$p, 1], 2) === false);
    assert(fn\_satisfyArgs([1, $p], 2) === false);
    assert(fn\_satisfyArgs([$p, 1, 1], 2) === false);
    assert(fn\_satisfyArgs([1, $p, 1], 2) === false);

    assert(fn\_satisfyArgs([1, 1, $p], 2) === false);
    assert(fn\_satisfyArgs([1, 1, $p, 1], 2) === false);
    assert(fn\_satisfyArgs([1, 1, $p, $p], 2) === false);

    assert(fn\_satisfyArgs([1,1], 2) === true);
    assert(fn\_satisfyArgs([1,1,1], 2) === true);

    $getEven = curryp("\\array_filter", __, $isEven);
    assert($getEven(range(1, 10)) === [1=>2,3=>4,5=>6,7=>8,9=>10]);

    $charAt0 = curryp("\\substr", __, 0, 1);
    assert($charAt0("Hello") === "H");

    $filter = curryp("\\array_filter", __, __);
    $getEven = $filter(__, $isEven);
    assert($getEven(range(1, 10)) === [1=>2,3=>4,5=>6,7=>8,9=>10]);

    $removeFirstChar = curryp("\\substr", __, 1);
    assert($removeFirstChar("Hello") === "ello");

    // curry optional parameter
    $removeFirstChar = curryp("\\substr", __, 1, __);
    $substrFrom1Len2 = $removeFirstChar(__, 2);
    assert($substrFrom1Len2("Hello") === "el");

    ///////////////////////////////////////////////////////////////////////////

}
~~~