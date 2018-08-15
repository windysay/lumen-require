<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/10
 * Time: 9:44
 * author: soliang
 */

namespace Yunhan\JAuth\Storage;

use http\Exception\InvalidArgumentException;
use Yunhan\JAuth\Util\AuthUtil;

class StorageEntity
{
    protected $storage;

    public function __construct()
    {
        $sNmae = AuthUtil::storageName();
        switch ($sNmae) {
            case "database" :
                $this->storage = new DbStorage();break;
            case "redis" :
                $this->storage = new RedisStorage();break;
            default :
                throw new InvalidArgumentException("storage 参数错误：{$sNmae}");
        }
    }

    /**
     * @return DbStorage|RedisStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    public static function getInstance()
    {
        return (new self())->getStorage();
    }
}