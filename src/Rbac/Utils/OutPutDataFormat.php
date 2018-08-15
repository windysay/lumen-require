<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 10:29
 */

namespace Yunhan\Rbac\Utils;


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;

class OutPutDataFormat implements OutputDataFormatContract
{

    public function error($msg = '', $data = [], $code = 500)
    {
        $msg = $msg ?: '服务器错误，请稍后重试';
        return $this->success($data, $msg, $code);
    }

    public function success($data, $msg = 'OK', $code = 200)
    {
        //文件下载
        if ($data instanceof BinaryFileResponse) {
            return $data;
        }

        $return = [
            'status_code' => $code,
            'message' => $msg,
            'data' => $data,
        ];
        $this->clearNull($return['data']);
        return $return;
    }

    public function clearNull(&$data = '')
    {
        $data = json_decode(json_encode($data), true);
        if ($data === null || $data === false) {
            $data = '';
        }
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$v) {
                if ($v === null || $v === false) {
                    $v = '';
                } elseif (is_array($v)) {
                    self::clearNull($v);
                } elseif (is_string($v) && stripos($v, '.') === 0) {
                    $v = '0' . $v;
                }
            }
        }
    }
}