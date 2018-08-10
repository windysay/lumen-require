<?php

namespace Yunhan\JAuth;

use Yunhan\JAuth\Exceptions\ExpiredException;
use Yunhan\JAuth\Exceptions\SignatureTokenException;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Storage\StorageEntity;
use Yunhan\JAuth\Util\AuthUtil;

class AuthBase
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
        $storage = new StorageEntity();
        $uid = $storage->getStorage()->get($token);
        if (empty($uid)) {
            throw new SignatureTokenException('无效 TOKEN');
        }
        $userModel = AuthUtil::getUserModel();
        $user = (new $userModel)->getUserByIdToJAuth($uid);
        return $user;
    }

    public static function guardNameIsValid($guard)
    {
        $arr = AuthUtil::getGuardConfig();
        return array_key_exists($guard, $arr);
    }
}
