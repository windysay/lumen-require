<?php

namespace Yunhan\JAuth\Traits;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use Yunhan\JAuth\Util\SsoHelper;

trait SsoTrait
{
    /**
     * 获取sso返回值中主键名，此字段值为 Auth::id() 返回值
     * @return string
     */
    public function getSsoKeyName()
    {
        return $this->primaryKey ?? 'id';
    }

    /**
     * 定义oss user返回
     * @param string $token 表单或头部的 token 字段，可在JAuth自定义token键名
     * @return \stdClass|null 返回 带有用户信息的\stdClass 或 null
     */
    public function getUserByTokenToSso($token)
    {
        $user = SsoHelper::validate($token);
        if ($user === false) {
            return null;
        }
        $userModel = new self();
        foreach ($user as $k => $v) {
            $userModel->{$k} = $v;
        }
        return $userModel;
    }
}
