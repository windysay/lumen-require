<?php

namespace Yunhan\JAuth\Traits;

use Yunhan\JAuth\Util\SsoHelper;

trait SsoTrait
{
    /**
     * 定义oss user返回
     * @param string $token 表单或头部的 token 字段，可在JAuth自定义token键名
     * @return \stdClass|null 返回 带有用户信息的\stdClass 或 null
     */
    public function getUserByTokenToSso($token)
    {
        $user = SsoHelper::validate($token);
        if ($user == false) {
            return null;
        }
        return $user;
    }
}
