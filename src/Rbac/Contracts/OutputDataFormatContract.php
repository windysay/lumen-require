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
    /**
     * 成功输出
     * @param $data
     * @param string $msg
     * @param int $code
     * @return mixed
     */
    public function success($data, $msg = 'OK', $code = 200);


    /**
     * 失败时输出
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return mixed
     */
    public function error($msg = '', $data = [], $code = 500);


}