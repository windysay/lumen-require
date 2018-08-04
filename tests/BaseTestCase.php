<?php

namespace Yunhan\Tests;

abstract class BaseTestCase extends \Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return new \Laravel\Lumen\Application();
    }
}
