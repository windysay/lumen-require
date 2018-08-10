<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 20:40
 * author: soliang
 */

namespace Yunhan\JAuth\Storage;

interface StorageInterface
{
    public function get($token);

    public function set($uid);

    public function del($token);
}