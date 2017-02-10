<?php declare(strict_types=1);

/*
 * This file is part of the Consatan\Support package.
 *
 * (c) Chopin Ngo <consatan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Consatan\Support;

/**
 * 检查给定的值是否指定类定义的常量
 *
 * @param  string|object  $class  类名或类的实例
 * @param  mixed    $value  要检查的值
 * @param  string   $prefix  ('') 类定义的常量名的前缀
 * @param  boolean  $strict  (true) 检查值时是否启用严格模式
 * @param  boolean  $name    (false) 设置为 true 且给定值是类的常量时返回常量名，
 *     不是类的常量或类不存在返回空字符串，
 *     当给定值对应多个常量时，返回匹配的第一个常量名，如果要避免出现这种情况，
 *     应该使用 `$prefix` 参数对常量名做出限制。
 * @return mixed    给定值不是类的常量或类不存在返回 false，否则返回 true
 *     或者返回字符串，见 `$name` 的参数说明
 */
function in_constants($class, $value, string $prefix = '', bool $strict = true, bool $name = false)
{
    $prefix = trim($prefix);

    try {
        $ref = new \ReflectionClass($class);
    } catch (\ReflectionException $e) {
        return $name ? '' : false;
    }

    $constants = $ref->getConstants();

    if (!$name && '' === $prefix) {
        return in_array($value, $constants, $strict);
    }

    foreach ($constants as $k => $v) {
        if (strpos($k, $prefix) === 0 && ($value === $v || (!$strict && $value == $v))) {
            return $name ? $k : true;
        }
    }

    return $name ? '' : false;
}

/**
 * 根据给定值返回类定义的常量名，当给定值匹配到多个常量名时，返回第一个匹配的
 * 常量名，为避免值种情况，应使用 `$prefix` 参数匹配的常量名进行限制。
 *
 * @param  string|object  $class  类名或类的实例
 * @param  mixed    $value  要检查的值
 * @param  string   $prefix  ('') 类定义的常量名的前缀
 * @param  boolean  $strict  (true) 检查值时是否启用严格模式
 * @return string   如果没找到对应值的变量名，返回空字符串
 */
function constant_name($class, $value, string $prefix = '', bool $strict = true): string
{
    return ($name = in_constants($class, $value, $prefix, $strict, true)) !== false ? $name : '';
}

/**
 * Contver 64 bits int to 32 bits.
 *
 * @see http://stackoverflow.com/a/16497435/831243
 * @param  int  $value
 * @return int
 */
function int32bits(int $value): int
{
    // Check if 32bits OS.
    if (PHP_INT_SIZE > 4) {
        $value = ($value & 0xffffffff);

        if ($value & 0x80000000) {
            $value = -((~$value & 0xffffffff) + 1);
        }
    }

    return $value;
}

/**
 * 转换数组为 SimpleXMLElement
 *
 * @param array $arr   数据数组
 * @param string $xml  ('xml') xml 根节点名字
 * @return \SimpleXMLElement
 */
function array2xml(array $arr, string $root = 'xml'): \SimpleXMLElement
{
    $root = '' === ($root = trim($root)) ? 'xml' : $root;
    $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$root></$root>");
    $fn = function (array $arr, \SimpleXMLElement &$xml, string $name = '') use (&$fn) {
        foreach ($arr as $key => $val) {
            if (is_object($val)) {
                $val = (array)$val;
            }

            if (is_array($val)) {
                if (is_numeric($key) && 0 <= $key) {
                        $child = $xml->addChild($name);
                        $fn($val, $child);
                } else {
                    if (!isset($val[0])) {
                        $child = $xml->addChild($key);
                        $fn($val, $child);
                    } else {
                        $fn($val, $xml, $key);
                    }
                }
            } elseif (is_scalar($val)) {
                if ('_' !== $key) {
                    if (!is_null($val)) {
                        $xml->addAttribute($key, (string)$val);
                    } else {
                        $xml->addChild($key);
                    }
                } else {
                    $xml->{0} = $val;
                }
            }
        }
    };

    $fn($arr, $xml);
    return $xml;
}
