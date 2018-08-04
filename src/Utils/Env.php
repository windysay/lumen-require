<?php

namespace Yunhan\Utils;

class Env
{
    /**
     * @var array 缓存数据
     */
    private static $cache = [];

    /**
     * 是否生产环境
     * @return bool
     */
    public static function isProd()
    {
        if (!isset(self::$cache['isProd'])) {
            self::$cache['isProd'] = app()->environment('production');
        }
        return self::$cache['isProd'];
    }

    /**
     * 清空缓存
     * @return void
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
}
