<?php

namespace Yunhan\Tests\Utils;

use Yunhan\Tests\BaseTestCase;
use Yunhan\Utils\Env;

class EnvTest extends BaseTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        // 清空缓存，防止有缓存
        Env::clearCache();
    }

    public function testIsDevFalse()
    {
        $this->assertFalse(Env::isDev());
    }

    public function testIsDevTrue()
    {
        putenv('APP_ENV=local');
        $this->assertTrue(Env::isDev());

        // 测试缓存
        putenv('APP_ENV=testing');
        $this->assertTrue(Env::isDev());
    }
}
