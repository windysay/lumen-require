<?php

namespace Yunhan\JAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Yunhan\JAuth\Util\AuthUtil;

/**
 * Class Ticket
 * @property int $id
 * @property int $uid
 * @property string $token
 * @property string $ip
 * @property string $expiration
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @package JMD\Auth\Models
 */
class Ticket extends Model
{
    protected $guarded = [];

    protected $status_normal = 0;

    protected $status_del = 2;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(AuthUtil::getTicketTableName());
    }

    public function login($uid)
    {
        $token = AuthUtil::generateUUID($uid);
        $add = [
            'uid' => $uid,
            'token' => $token,
            'status' => 0,
            'ip' => AuthUtil::request()->ip() ?: '',
            'expiration' => AuthUtil::currentTime() + (int)AuthUtil::getTokenExpiration(),
        ];
        $res = $this->create($add)->id;
        return $res ? $token : false;
    }

    public function logout($token)
    {
        return static::where(['token' => $token])->update(['status' => $this->status_del]);
    }

    /**
     * 根据 token 查找
     * @param $token
     * @param array $columns
     * @return mixed
     */
    public function findOneByToken($token, $columns = ['*'])
    {
        return static::where([
            'token' => $token,
            'status' => $this->status_normal,
        ])->first($columns);
    }
}
