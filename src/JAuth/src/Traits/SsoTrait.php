<?php

namespace Yunhan\JAuth\Traits;

use Yunhan\JAuth\Util\SsoHelper;

trait SsoTrait
{
    public function getUserByTokenToSso($token)
    {
        $user = SsoHelper::validate($token);
        if ($user == false) {
            return null;
        }
        return $user;
    }
}
