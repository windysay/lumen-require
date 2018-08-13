<?php

namespace Yunhan\JAuth\Util;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Self_;

class AuthUtil
{
    /**
     * 生成 token
     * @param $uid
     * @return string
     */
    public static function generateUUID($uid)
    {
        //根据当前时间（微秒计）生成唯一id.
        return strtoupper(md5($uid . uniqid((string)rand(), true)));
    }

    /**
     * 获取请求中的 token
     * @return array|null|string
     */
    public static function requestToken()
    {
        $request = AuthUtil::request();
        $tokenName = self::getTokenName();
        $accessToken = $request->header($tokenName);
        if (empty($accessToken)) {
            $accessToken = $request->input($tokenName);
            if (empty($accessToken)) {
                return null;
            }
        }
        return $accessToken;
    }

    /**
     * Get an instance of the current request or an input item from the request.
     * @return \Illuminate\Http\Request
     */
    public static function request()
    {
        return Container::getInstance()->make('request');
    }

    /**
     * 获取 token 键名
     * @return string
     */
    public static function getTokenName()
    {
        return self::config('token_name');
    }

    /**
     * 获取 JAuth 配置
     * @param string $name
     * @return array
     */
    public static function config($name, $default = null, $baseName = 'JAuth')
    {
        return config("{$baseName}.{$name}", $default);
    }

    public static function currentTime()
    {
        return time();
    }

    /**
     * 设置默认 Guard
     * @param $name
     */
    public static function setCurrentGuard($name)
    {
        Auth::setDefaultDriver($name);
    }

    /**
     * 获取当前 Guard 对应 driver
     * @return string
     */
    public static function getCurrentDriver()
    {
        $currentGuard = self::getCurrentGuard();
        return config("auth.guards.{$currentGuard}.driver");
    }

    /**
     * 获取默认 Guard
     * @return string
     */
    public static function getCurrentGuard()
    {
        return Auth::getDefaultDriver();
    }

    /**
     * 获取当前 Guard 对应 user model
     * @return string
     */
    public static function getUserModel()
    {
        $currentGuard = self::getCurrentGuard();
        $providers = config("auth.guards.{$currentGuard}.provider");
        return config("auth.providers.{$providers}.model");
    }

    /**
     * 获取 auth.php config 配置的 guards 配置
     * @return mixed
     */
    public static function getGuardConfig()
    {
        return config('auth.guards');
    }

    /**
     * 获取 JAuth.php config 配置的 driver_config 配置
     * @return array
     */
    public static function getDriverConfig()
    {
        return self::config('driver_config');
    }

    /**
     * 获取 ticket 表名
     * @return string
     */
    public static function getTicketTableName()
    {
        return self::config('table_names.ticket');
    }

    /**
     * 获取 token 过期时间
     * @return int
     */
    public static function getTokenExpiration()
    {
        return self::config('expiration');
    }

    /**
     * 获取 storage_driver 配置(默认存储)
     * @return array
     */
    public static function storageName()
    {
        return self::config('storage_driver', 'database');
    }

    /**
     * 验证 guard 是否为配置中的有效项
     * @param $guard
     * @return bool
     */
    public static function guardNameIsValid($guard)
    {
        $arr = self::getGuardConfig();
        return array_key_exists($guard, $arr);
    }
}