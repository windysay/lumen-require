<?php

namespace Yunhan\JAuth;

use Illuminate\Support\ServiceProvider;
use Yunhan\JAuth\Exceptions\ExpiredException;
use Yunhan\JAuth\Exceptions\SignatureTokenException;
use Yunhan\JAuth\Exceptions\UnauthorizedException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     * @suppress PhanTypeArraySuspicious
     * @return void
     */
    public function boot()
    {
        // 默认报错
        $this->app['auth']->viaRequest('default', function ($request) {
            throw new UnauthorizedException('未进行身份认证');
        });
        // 获取用户
        $this->app['auth']->viaRequest('JAuth', function ($request) {
            try {
                return AuthBase::getUser();
            } catch (SignatureTokenException $e) {
                return null;
            } catch (ExpiredException $e) {
                return null;
            }
        });
    }
}
