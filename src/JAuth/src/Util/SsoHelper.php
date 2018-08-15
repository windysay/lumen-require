<?php

namespace Yunhan\JAuth\Util;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SsoHelper
{
    public static function validate($token)
    {
        if (!$token) {
            return false;
        }
        $result = self::getUserInfo($token);
        if (!$result) {
            return false;
        }
        if (!isset($result->code) || $result->code != 200) {
            return false;
        }
        return $result;
    }

    public static function baseDomain()
    {
        return [
            'login_redirect_url' => 'https://sso.jiumiaodai.com/sso/login',
            'ticket_cookie_name' => 'ticket_prod',
            'http_sso_check' => 'https://sso.jiumiaodai.com/sso/token',
        ];
    }

    public static function getConfig()
    {
        return config('Jauth.driver.sso');
    }

    public static function getUserInfo($ticket = null)
    {
        $request = app(Request::class);
        $actionId = trim($request->path(), '/');
        $loadDomainConfig = self::baseDomain();
        $ticket = $ticket ?? @$_COOKIE[$loadDomainConfig['ticket_cookie_name']];
        if (empty($ticket)) {
            return false;
        }
        $ip = self::getIp();
        $body = json_decode(self::httpSsoCheck($ticket, $ip, $actionId));
        return $body;
    }

    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (strpos($ip, ",") !== false) {
            $ip = explode(",", $ip)[0];
        }
        return $ip;
    }

    public static function httpSsoCheck($ticket, $ip, $actionId)
    {
        $client = new Client();
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $load_domain_config = self::baseDomain();
        $res = $client->request('get', $load_domain_config['http_sso_check'], [
            'headers' => [
                'User-Agent' => 'testing/1.0',
                'Accept' => 'application/json',
                'ticket' => $ticket,
                'ip' => $ip,
                'Referer' => $host,
                'route' => $actionId,
            ],
            ['timeout' => 3]
        ]);
        return $res->getBody();
    }
}