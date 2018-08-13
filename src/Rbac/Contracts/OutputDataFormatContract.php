<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 10:26
 */

namespace Yunhan\Rbac\Contracts;


interface OutputDataFormatContract
{
    //成功输出
    public function success($data, $msg = 'OK', $code = 200);

    //失败
    public function error($msg = '', $data = [], $code = 500);


}