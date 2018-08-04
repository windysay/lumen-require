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

    public function testIsProdFalse()
    {
        $this->assertFalse(Env::isProd());
    }

    public function testIsProdTrue()
    {
        putenv('APP_ENV=production');
        $this->assertTrue(Env::isProd());

        // 测试缓存
        putenv('APP_ENV=testing');
        $this->assertTrue(Env::isProd());
    }
}
