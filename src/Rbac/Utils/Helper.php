<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 16:15
 */

namespace Yunhan\Rbac\Utils;


class Helper
{
    /**
     * 清理无限极分类里面的空column
     * @param $data
     */
    public static function cleanNullChildren(&$data, $column = 'children')
    {
        foreach ($data as &$item) {
            if (is_array($item[$column]) && !empty($item[$column])) {
                static::cleanNullChildren($item[$column], $column);
            }
            if (empty($item[$column])) {
                unset($item[$column]);
            }
        }
        unset($item);
    }
}