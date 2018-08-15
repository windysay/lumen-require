<?php

namespace Yunhan\Rbac\Exceptions;

use InvalidArgumentException;

class MenuDoesNotExist extends InvalidArgumentException
{
    public static function named(string $menuName)
    {
        return new static("不存在名称为:{$menuName}的菜单");
    }

    public static function withId(int $roleId)
    {
        return new static("不存在Id为:{$roleId}的菜单");
    }
}
