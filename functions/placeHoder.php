<?php
/**
 * User: xiaofeng
 * Date: 2016/4/17
 * Time: 18:26
 */
namespace xiaofeng\F\Fn;

/**
 * placeholder
 * 有点长的字符串，比较代价高
 * @return null|string
 */
function _() {
    static $cache = null;
    if($cache === null) {
        $cache = md5(__FILE__) . mt_rand();
    }
    return $cache;
}
