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

    public function set($uid)
    {
        $token = AuthUtil::generateUUID($uid);
        return $this->getTicket()->add($token, $uid);
    }

    public function get($token)
    {
        $ticket = $this->getTicket()->get($token);
        if (empty($ticket)) {
            return null;
        }
        return $ticket->expiration > AuthUtil::currentTime() ? $ticket->uid : null;
    }

    public function del($token)
    {
        return $this->getTicket()->del($token);
    }
}
