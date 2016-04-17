<?php
/**
 * User: 乌鸦
 * Date: 2016/4/16
 * Time: 2:24
 */
namespace test
{
    class C
    {
        public function m($a, $b = null) {}
        public static function sm($a, $b = null) {}
    }

    function f($a, $b = null) {}
}

namespace xiaofeng
{
    error_reporting(E_ALL);
    require __DIR__ . "/functions/bootstrap.php";
    use xiaofeng\F as F;
    use xiaofeng\F\Fn as Fn;
    use test;


    // Fn\op
    ///////////////////////////////////////////////////////////////////////////
    $id = Fn\op("id");
    assert($id("hello") === "hello");
    assert(Fn\op("id", "hello") === "hello");

    $plus = Fn\op("+");
    $plus1 = $plus(1);
    $plus5 = Fn\op("+", 5);

    assert($plus instanceof \Closure);
    assert($plus1 instanceof \Closure);
    assert($plus5 instanceof \Closure);

    assert($plus1(5) === 6);
    assert(Fn\op("+", 1, 3) == 4);
    assert($plus5(1) === 6);

    $arr = [7,8,9];
    $index = Fn\op("[]");
    $head = Fn\op("[]", 0);
    assert($head($arr) === 7);
    assert($head([]) === null);
    assert($index(2, $arr) === 9);
    assert(Fn\op("[]", 2, $arr) === 9);
    $isset = Fn\op("?");
    $isset4 = Fn\op("?", 3);
    assert($isset(2, $arr) === true);
    assert($isset4($arr) === false);

    $obj = (object)["name"=>"xiaofeng", "age"=>26];
    $prop = Fn\op("->");
    $name = Fn\op("->", "name");
    assert($name($obj) === "xiaofeng");
    assert($name((object)[]) === null);
    assert($prop("age", $obj) === 26);
    assert(Fn\op("->", "age", $obj) === 26);
    $issetName = Fn\op("?", "name");
    assert($issetName($obj) === true);

    $table = [["name"=>"xiaofeng1", "age"=>26], ["name"=>"xiaofeng2", "age"=>26]];
    $secondName = Fn\op("...[]", [1, "name"]);
    assert($secondName($table) === "xiaofeng2");

    // Fn\not curry not
    ///////////////////////////////////////////////////////////////////////////
    $allOdd = function($a, $b, $c) {
        return ($a & 1) === 1 && ($b & 1) === 1 && ($c & 1) === 1;
    };
    assert($allOdd(1,3,5) === true);
    assert($allOdd(1,3,4) === false);

    $curryNot = Fn\op("!");
    $notIsInt = $curryNot("is_int");
    assert($notIsInt(4) === false);
    // assert(Fn\op("!", "is_string")(1) === true);

    $notAllOdd = Fn\op("!", $allOdd);
    assert($notAllOdd(1,3,5) === false);
    assert($notAllOdd(1,3,4) === true);

    $notAllleft2Odd = Fn\op("!", $allOdd, 1);
    $wtfName = $notAllleft2Odd(3);
    assert($notAllleft2Odd instanceof \Closure);
    assert($notAllleft2Odd(3,5) === false);
    assert($notAllleft2Odd(3,4) === true);
    assert($notAllleft2Odd(2,4) === true);
    assert($wtfName(5) === false);
    assert($wtfName(4) === true);

    // Fn\_parameterCount
    ///////////////////////////////////////////////////////////////////////////
    assert(Fn\_parameterCount(["test\\C", "m"]) === 1);
    assert(Fn\_parameterCount([new test\C, "m"]) === 1);
    assert(Fn\_parameterCount(function($a, $b = null) {}) === 1);
    assert(Fn\_parameterCount("\\array_map") === 2);
    assert(Fn\_parameterCount("test\\c::sm") === 1);

    assert(Fn\_parameterCount(["test\\C", "m"], false) === 2);
    assert(Fn\_parameterCount([new test\C, "m"], false) === 2);
    assert(Fn\_parameterCount(function($a, $b = null) {}, false) === 2);
    assert(Fn\_parameterCount("\\array_map", false) === 2);
    assert(Fn\_parameterCount("test\\c::sm", false) === 2);

    ///////////////////////////////////////////////////////////////////////////
    function _testSum($sumFn) {
        $add1 = $sumFn(1);
        assert($add1(2) === 3);
        // assert($sum(1)(2) === 3);
        assert($add1(2, 10) === 13);
    }

    function sumFn($a, $b, $c = 0) {
        return $a + $b + $c;
    }
    class Sum {
        public function call($a, $b, $c = 0) {
            return sumFn($a, $b, $c);
        }
        public static function scall($a, $b, $c = 0) {
            return sumFn($a, $b, $c);
        }
    }
    _testSum(Fn\curry("xiaofeng\\sumFn"));
    _testSum(Fn\curry([new Sum(), "call"]));
    _testSum(Fn\curry(["xiaofeng\\Sum", "scall"]));
    _testSum(Fn\curry("xiaofeng\\Sum::scall"));

    $add1 = Fn\curry("xiaofeng\\sumFn", 1);
    assert($add1(10) === 11);

    $add  = Fn\_curry1(function($a, $b) { return $a + $b; });
    $add100 = $add(100);
    assert($add100(1) === 101);


//    $filter = Fn\curry(Fn\flip("\\array_filter"), 0);
//    $map = Fn\curry("\\array_map");
//    $reduce = Fn\curry(Fn\flip("\\array_reduce"), 0);
//    $range = Fn\curry("\\range", 1);

//    Fn\compose($reduce(Fn\op("+")), $map("chr"), $filter(), $range(10));

//    array_filter()
//    array_map()

    ///////////////////////////////////////////////////////////////////////////

//    $strlenA1 = Fn\compose(Fn\op("+", 1), "\\strlen");
//    assert($strlenA1("Hello"), 6);
}
