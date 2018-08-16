<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 12:14
 * author: soliang
 */

namespace Yunhan\JAuth\Driver;

use http\Exception\InvalidArgumentException;
use Yunhan\JAuth\Util\AuthUtil;

class DriverEntity
{
    protected $driver;

    public function __construct($guard)
    {
        if (! AuthUtil::guardNameIsValid($guard)) {
            throw new SystemException('无效guard');
        }
        $dirver = AuthUtil::getDriverByGuard($guard);
        switch ($dirver) {
            case "token" :
                $this->driver = new TokenDriver();break;
            case "sso" :
                $this->driver = new SsoDriver();break;
//            case "session" :
//                $this->driver = new SsoDriver();break;
            default :
                throw new InvalidArgumentException("{$guard} dirver 类型错误");
        }
    }

    /**
     * @return TokenDriver|
     */
    public function getDriver()
    {
        return $this->driver;
    }

    public static function getInstance($guard = null)
    {
        $guard = $guard ?? AuthUtil::getCurrentGuard();
        return (new self($guard))->getDriver();
    }
}