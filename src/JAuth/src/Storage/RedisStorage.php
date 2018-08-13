<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 20:43
 * author: soliang
 */

namespace Yunhan\JAuth\Storage;

use Illuminate\Support\Facades\Redis;
use Yunhan\JAuth\Util\AuthUtil;

class RedisStorage implements StorageInterface
{
    public function get($token, $guard)
    {
        return Redis::get($this->getKey($token, $guard));
    }

    public function set($uid, $guard, $token, $exp)
    {
        $key = $this->getKey($token, $guard);
        Redis::set($key, $uid, 'EX', $exp);
        return $token;
    }

    public function del($token, $guard)
    {
        return is_integer(Redis::del($this->getKey($token, $guard)));
    }

    protected function getKey($token, $guard)
    {
        $prefix = AuthUtil::getDriverConfig()['redis']['key_prefix'] ?? 'JAuth:';
        return $prefix . $guard . ':' . $token;
    }
}