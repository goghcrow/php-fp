<?php
/**
 * User: xiaofeng
 * Date: 2016/4/15
 * Time: 23:49
 */

// TODO: 拆分整理

namespace xiaofeng\F;
use xiaofeng\F\Fn as Fn;

interface Functor
{
    public static function of($value);
    // map, __invoke魔术方法，可以让对象更像函数~
    public function __invoke(/*callable*/ $f);
}

abstract class AbstractFunctor implements Functor
{
    protected $__value;
    protected function __construct($value) {
        $this->__value = $value;
    }
    public static function of($value) {
        return new static($value);
    }
}

class Just extends AbstractFunctor
{
    public function __invoke(/*callable*/ $f) {
        Fn\_assertCallable($f, "First argument");
        return static::of($f($this->__value));
    }
}

class Collection extends AbstractFunctor
{
    public function __invoke(/*callable*/ $f) {
        Fn\_assertCallable($f, "First argument");

    }
}