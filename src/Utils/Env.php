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
        return self::checkEnv(__FUNCTION__, ['production', 'prod', 'pre', 'staging']);
    }

    public static function isStaging()
    {
        return self::checkEnv(__FUNCTION__, ['staging']);
    }

    public static function isPre()
    {
        return self::checkEnv(__FUNCTION__, ['pre']);
    }

    /**
     * 加载环境变量
     * @param string $basePath 根目录
     * @throws \Exception
     */
    public static function load($basePath)
    {
        $file = $basePath . 'env.php';
        if (!file_exists($file)) {
            throw new \Exception('env file not exist');
        }
        $env = require $file;
        foreach ($env as $name => $value) {
            if (!is_string($value)) {
                throw new \Exception('env value must be string: ' . $name);
            }
            putenv("$name=$value");
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
