<?php

namespace Yunhan\JAuth\Driver;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth as LAuth;
use Yunhan\JAuth\Exceptions\ExpiredException;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Storage\StorageEntity;
use Yunhan\JAuth\Util\AuthUtil;
use Yunhan\JAuth\Util\SsoHelper;

class SsoDriver implements DriverInterface
{
    public static function getUser()
    {
        if (!$token = AuthUtil::requestToken()) {
            throw new AuthorizationException('未登录');
        }
        $userModel = AuthUtil::getUserModel();
        $user = (new $userModel)->getUserByTokenToSso($token);
        return $user;
    }

    public function login($uid, $guard, $token, $expiration)
    {
        // sso不提供login
    }

    public function logout($token, $guard)
    {
        //TODO 待
    }

    public function user()
    {
        return LAuth::user();
    }

    public function id()
    {
        $user = LAuth::user();
        return $user->{$user->getSsoKeyName()};
    }
}
