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
        $this->app->configure('JAuth');

        $path = realpath(__DIR__.'/../config/JAuth.php');
        $this->mergeConfigFrom($path, 'JAuth');
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
        // driver:token
        $this->app['auth']->viaRequest('token', function ($request) {
            try {
                return AuthBase::getUser();
            } catch (SignatureTokenException $e) {
                return null;
            } catch (ExpiredException $e) {
                return null;
            }
        });
        // driver:session
        $this->app['auth']->viaRequest('session', function ($request) {
            // ..
        });
        // driver:sso
        $this->app['auth']->viaRequest('sso', function ($request) {
            // ..
        });
    }
}
