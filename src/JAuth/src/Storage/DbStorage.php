<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 20:43
 * author: soliang
 */

namespace Yunhan\JAuth\Storage;

use phpDocumentor\Reflection\Types\Null_;
use Yunhan\JAuth\Models\Ticket;
use Yunhan\JAuth\Util\AuthUtil;

class DbStorage implements StorageInterface
{
    protected function getTicket()
    {
        return new Ticket();
    }

    public function set($uid, $guard, $token, $exp)
    {
        return $this->getTicket()->add($uid, $token, $guard, $exp);
    }

    public function get($token, $guard)
    {
        $ticket = $this->getTicket()->get($token, $guard);
        if (empty($ticket)) {
            return null;
        }
        return $ticket->expiration > AuthUtil::currentTime() ? $ticket->uid : null;
    }

    public function del($token, $guard)
    {
        return $this->getTicket()->del($token, $guard);
    }
}
