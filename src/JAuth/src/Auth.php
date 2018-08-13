<?php

namespace Yunhan\JAuth;

use Illuminate\Support\Facades\Auth as LAuth;
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
     * @return bool|string token
     */
    public static function login($uid, $guard)
    {
        $storage = new StorageEntity();
        if (! AuthUtil::guardNameIsValid($guard)) {
            throw new SystemException('无效guard');
        }
        $token = AuthUtil::generateUUID($uid);
        $expiration = (int)AuthUtil::getTokenExpiration();
        return $storage->getStorage()->set($uid, $guard, $token, $expiration);
    }

    /**
     * 退出登录
     * @return bool
     */
    public static function logout()
    {
        $token = AuthUtil::requestToken();
        $guard = AuthUtil::getCurrentGuard();
        $storage = new StorageEntity();
        return $storage->getStorage()->del($token, $guard);
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
        return LAuth::user();
    }

    public static function id()
    {
        return LAuth::id();
    }

    // JAuthinterface get set del
    // JAuth cache token error jauth []
// config   jauth.php return [ 'token' => null, 'cache' => 'db' =>model 'cache', 'reids'],
    // cache [ dbCache[ model get set del] redisCache[redis ] facadeCache [cache] ]
    // token [ get ]
    // Auth login v logout re  [cache token] Auth;;JAuth()->login()

}
