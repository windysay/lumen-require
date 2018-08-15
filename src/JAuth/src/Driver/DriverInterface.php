<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 11:14
 * author: soliang
 */

namespace Yunhan\JAuth\Driver;

interface DriverInterface
{
    public function login($uid, $guard, $token, $expiration);

    public function logout($token, $guard);

    public static function getUser();

    public function user();

    public function id();
}