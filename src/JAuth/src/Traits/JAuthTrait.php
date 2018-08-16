<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/7
 * Time: 18:00
 * author: soliang
 */

namespace Yunhan\JAuth\Traits;

trait JAuthTrait
{
    public $access;

    public function getUserByIdToJAuth($id)
    {
        return static::where($this->primaryKey, $id)->first();
    }

    public function accessIdentity()
    {
        //do something...
        return null;
    }
}
