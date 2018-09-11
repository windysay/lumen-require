<?php

namespace Yunhan\Rbac\Commands;

use Illuminate\Console\Command;
use Yunhan\Rbac\Contracts\Config;

class createMenuFile extends Command
{
    /**
     * 菜单文件
     *
     * @var string
     */
    protected $menuPath;

    /**
     * @var string
     */
    protected $module;

    /**
     * 控制台命令名称
     * @var string
     */
    protected $signature = 'rbac-menu:create-file
                                {module : 对应的模块.对应中间件加的gurade:例如`"middleware" => "setGuard:admin"`模块为`admin`}';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成菜单文件';

    /**
     * 生成菜单文件
     */
    public function handle()
    {
        $this->module = $this->argument('module');

        $this->menuPath = storage_path("menus");

        if (!file_exists($this->menuPath)) {
            mkdir($this->menuPath, 0777, true);
        }

        /** @var Config $config */
        $config = app(Config::class);
        $menus = $config->getMenuList($this->module);

        $tree = $this->getTree($menus);
        $this->cleanNullChildren($tree);
        $content = preg_replace("#\d+\s*=>#", '', var_export($tree, true));
        $menuFile = $this->menuPath . "/{$this->module}.php";
        $result = file_put_contents($menuFile, "<?php \n\r  return " . $content . ';');
        if ($result !== false) {
            $this->alert("菜单生成成功:$menuFile");
        } else {
            $this->error('文件生成失败');
        }
    }

    /**
     * @param array $data
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public function getTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['pid'] == $parent_id) {
                    $tree[] = [
                        'name' => $v['name'],
                        'icon' => $v['icon'],
                        'path' => $v['path'],
                        'sort' => $v['sort'],
                        'children' => $this->getTree($data, $v['id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }

    /**
     * 清理无限极分类里面的空column
     *
     * @param $data
     * @param string $column
     */
    public function cleanNullChildren(&$data, $column = 'children')
    {
        foreach ($data as &$item) {
            if (is_array($item[$column]) && !empty($item[$column])) {
                $this->cleanNullChildren($item[$column], $column);
            }
            if (empty($item[$column])) {
                unset($item[$column]);
            }
        }
    }
}