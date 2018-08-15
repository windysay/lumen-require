<?php

namespace Yunhan\JAuth;

use Illuminate\Support\Facades\Auth as LAuth;
use Yunhan\JAuth\Driver\DriverEntity;
use Yunhan\JAuth\Exceptions\SignatureTokenException;
use Yunhan\JAuth\Exceptions\SystemException;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Storage\StorageEntity;
use Yunhan\JAuth\Util\AuthUtil;

class Auth
{
    /**
     * 登录获取 token
     * @param $uid
     * @param $guard
     * @return bool|string token
     */
    public static function login($uid, $guard)
    {
        $token = AuthUtil::generateUUID($uid);
        $expiration = (int)AuthUtil::getTokenExpiration();
        return DriverEntity::getInstance($guard)->login($uid, $guard, $token, $expiration);
    }

    /**
     * 退出登录
     * @return bool
     */
    public static function logout()
    {
        $token = AuthUtil::requestToken();
        $guard = AuthUtil::getCurrentGuard();
        return DriverEntity::getInstance($guard)->logout($token, $guard);
    }

    /**
     * 获取额外身份信息，可在对应 user model 内用重写方式进行自定义返回
     */
    public static function identity()
    {
        $userModel = AuthUtil::getUserModel();
        return (new $userModel)->accessIdentity();
    }

    public static function user()
    {
        return DriverEntity::getInstance()->user();
    }

    public static function id()
    {
        return DriverEntity::getInstance()->id();
    }

    // JAuthinterface get set del
    // JAuth cache token error jauth []
// config   jauth.php return [ 'token' => null, 'cache' => 'db' =>model 'cache', 'reids'],
    // cache [ dbCache[ model get set del] redisCache[redis ] facadeCache [cache] ]
    // token [ get ]
    // Auth login v logout re  [cache token] Auth;;JAuth()->login()

}
