<?php

namespace Yunhan\Rbac\Utils;

use Yunhan\Rbac\Contracts\Config;

class RbacHelper
{
    /**
     * @var string
     */
    const USER_MENUS = '/api/rbac/menu/user-menus';

    /**
     * @param $path
     * @return array
     */
    public static function loadRouteFile($path)
    {
        $routeFiles = [];
        $files = glob($path);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $temp = static::loadRouteFile($file . '/*');
                if ($temp) {
                    array_push($routeFiles, ...$temp);
                }
            }
            if (is_file($file) && pathinfo($file)['extension'] == 'php') {
                $routeFiles[] = $file;
            }
        }

        return $routeFiles;
    }

    /**
     * 获取菜单
     *
     * @return array
     */
    public static function menus()
    {
        $config = app(Config::class);
        $uid = $config->getUserId();

        $data = [
            'uid' => $uid,
            'guard' => config('auth.defaults.guard'),
        ];

        $data['sign'] = $config->getSign($data);
        $url = rtrim($config->getDomain(), '/') . static::USER_MENUS;

        $result = RbacHelper::curlPost($url, $data);

        if (isset($result['code']) && $result['code'] == 18000) {
            return $result['data'];
        }

        return [];
    }

    /**
     * @param $url
     * @param $params
     * @return bool|string
     */
    public static function curlPost($url, $params)
    {
        $client = new \GuzzleHttp\Client();
        $options = [
            'form_params' => $params,
        ];
        if (strpos($url, 'https://') === 0) {
            $options['verify'] = false;
        }
        try {
            $res = $client->post($url, $options);
        } catch (\Exception $e) {
            return false;
        }
        if ($res->getStatusCode() != 200) {
            return false;
        }
        return json_decode((string)$res->getBody(), true);
    }

}