<?php

namespace Yunhan\Utils;

class Env
{
    /**
     * @var array 缓存数据
     */
    private static $cache = [];

    public static function isDev()
    {
        return self::checkEnv(__FUNCTION__, ['dev', 'local']);
    }

    public static function isTest()
    {
        return self::checkEnv(__FUNCTION__, ['test']);
    }

    public static function isDevOrTest()
    {
        return self::checkEnv(__FUNCTION__, ['dev', 'local', 'test']);
    }

    public static function isProd()
    {
        return self::checkEnv(__FUNCTION__, ['production', 'staging']);
    }

    public static function isStaging()
    {
        return self::checkEnv(__FUNCTION__, ['staging']);
    }

    /**
     * 加载环境变量
     * @param string $baseDir 根目录地址
     */
    public static function load($baseDir)
    {
        $env = require $baseDir . '/../env.php';
        foreach ($env as $name => $value) {
            $current = getenv($name);
            if ($current === false) {
                putenv("$name=$value");

                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    /**
     * 内部封装方法
     * @param string       $name
     * @param array|string $env
     * @return bool
     */
    private static function checkEnv($name, $env): bool
    {
        if (!isset(self::$cache[$name])) {
            self::$cache[$name] = app()->environment($env);
        }
        return self::$cache[$name];
    }

    /**
     * 清空缓存
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
