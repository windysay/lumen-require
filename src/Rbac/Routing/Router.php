<?php

namespace Yunhan\Rbac\Routing;


use Illuminate\Support\Arr;

class Router extends \Laravel\Lumen\Routing\Router
{

    public function __construct(\Laravel\Lumen\Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Merge the given group attributes.
     *
     * @param  array $new
     * @param  array $old
     * @return array
     */
    public function mergeGroup($new, $old)
    {
        $new['namespace'] = static::formatUsesPrefix($new, $old);

        $new['prefix'] = static::formatGroupPrefix($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        if (isset($old['as'])) {
            $new['as'] = $old['as'] . (isset($new['as']) ? '.' . $new['as'] : '');
        }

        if (isset($old['path'])) {
            $new['path'] = $old['path'] . (isset($new['path']) ? '.' . $new['path'] : '');
        }

        if (isset($old['suffix']) && !isset($new['suffix'])) {
            $new['suffix'] = $old['suffix'];
        }

        return array_merge_recursive(Arr::except($old, ['namespace', 'prefix', 'as', 'path', 'suffix']), $new);
    }

    /**
     * Merge the group attributes into the action.
     *
     * @param  array $action
     * @param  array $attributes The group attributes
     * @return array
     */
    protected function mergeGroupAttributes(array $action, array $attributes)
    {
        $namespace = $attributes['namespace'] ?? null;
        $middleware = $attributes['middleware'] ?? null;
        $as = $attributes['as'] ?? null;
        if (isset($attributes['path']) && $attributes['path']) {
            if (isset($action['path'])) {
                $action['path'] .= '.' . $attributes['path'];
            } else {
                $action['path'] = $attributes['path'];
            }
        }

        return $this->mergeNamespaceGroup(
            $this->mergeMiddlewareGroup(
                $this->mergeAsGroup($action, $as),
                $middleware),
            $namespace
        );
    }

}