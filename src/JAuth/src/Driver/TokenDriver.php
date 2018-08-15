<?php

namespace Yunhan\JAuth\Driver;

use Illuminate\Support\Facades\Auth as LAuth;
use Yunhan\JAuth\Exceptions\ExpiredException;
use Yunhan\JAuth\Exceptions\SignatureTokenException;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Storage\StorageEntity;
use Yunhan\JAuth\Util\AuthUtil;

class TokenDriver implements DriverInterface
{
    public static function getUser()
    {
        if (!$token = AuthUtil::requestToken()) {
            throw new SignatureTokenException('未登录');
        }
        return self::getUserByToken($token);
    }

    public static function getUserByToken($token)
    {
        $guard = AuthUtil::getCurrentGuard();
        $uid = StorageEntity::getInstance()->get($token, $guard);
        if (empty($uid)) {
            throw new SignatureTokenException('无效 TOKEN');
        }
        $userModel = AuthUtil::getUserModel();
        $user = (new $userModel)->getUserByIdToJAuth($uid);
        return $user;
    }

    public function login($uid, $guard, $token, $expiration)
    {
        return StorageEntity::getInstance()->set($uid, $guard, $token, $expiration);
    }

    public function logout($token, $guard)
    {
        return StorageEntity::getInstance()->del($token, $guard);
    }

    public function user()
    {
        return LAuth::user();
    }

    public function id()
    {
        return LAuth::id();
    }
}
