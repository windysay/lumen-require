<?php

namespace Yunhan\Rbac\Middleware;

use Closure;
use Illuminate\Http\Request;
use Yunhan\Rbac\Contracts\Config;
use Yunhan\Rbac\Utils\RbacHelper;

class RbacAuthCheck
{
    /**
     * @var string
     */
    protected $action = '/api/rbac/permission/check';

    public function handle(Request $request, Closure $next)
    {
        if (isset($request->route()[1]['as'])) {
            $alias = $request->route()[1]['as'];
            //通过别名获取路由
            $route = $request->method() . '/' . str_replace_first('/', '', app('router')->namedRoutes[$alias]);
        } else {
            $route = $request->method() . '/' . $request->path();
        }

        /** @var Config $config */
        $config = app(Config::class);
        $params = [
            'guard' => config('auth.defaults.guard'),
            'route' => $route,
            'uid' => $config->getUserId(),
            'app_key' => $config->getAppKey(),
        ];
        $params['sign'] = $config->getSign($params);
        $url = rtrim($config->getDomain(), '/') . $this->action;

        $result = RbacHelper::curlPost($url, $params);
        if (isset($result['code']) && $result['code'] === 18000) {
            return $next($request);
        }

        abort(403, $result['msg']);
    }
}
