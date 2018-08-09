<?php

namespace Yunhan\JAuth;

use Illuminate\Support\Facades\Auth as LAuth;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Util\AuthUtil;

class Auth
{
    /**
     * 登录获取 token
     * @param $uid
     * @return bool|string token
     */
    public static function login($uid)
    {
        $ticketModel = new Ticket();
        return $ticketModel->login($uid);
    }

    /**
     * 退出登录
     * @return int|bool
     */
    public static function logout()
    {
        $ticketModel = new Ticket();
        $token = AuthUtil::requestToken();
        return $ticketModel->logout($token) > 0;
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
}
