<?php

namespace Yunhan\JAuth\Driver;

use Yunhan\JAuth\Exceptions\ExpiredException;
use Yunhan\JAuth\Exceptions\SignatureTokenException;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Storage\StorageEntity;
use Yunhan\JAuth\Util\AuthUtil;

class TokenDriver
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
        $guard = AuthUtil::getCurrentGuard();
        $uid = $storage->getStorage()->get($token, $guard);
        if (empty($uid)) {
            throw new SignatureTokenException('无效 TOKEN');
        }
        $userModel = AuthUtil::getUserModel();
        $user = (new $userModel)->getUserByIdToJAuth($uid);
        return $user;
    }
}
