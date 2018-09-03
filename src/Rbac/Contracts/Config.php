<?php

namespace Yunhan\Rbac\Contracts;

interface Config
{
    /**
     * 获取认证域名
     *
     * @return string
     */
    public function getDomain(): string;

    /**
     * 获取AppKey
     *
     * @return string
     */
    public function getAppKey(): string;

    /**
     * 获取用户id
     *
     * @return int
     */
    public function getUserId(): int;

    /**
     * 获取签名
     *
     * @param array $params
     * @return string
     */
    public function getSign($params): string;
}