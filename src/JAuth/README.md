## Auth 配置

**1、配置文件**    
复制      
JAuth/config/JAuth.php 到 /config       
JAuth/config/auth.php 到 /config
修改配置为所需  

**2、修改 /bootstrap/app.php**

    //取消注释      
    $app->withFacades();
    
    //取消注释      
    $app->withEloquent();
    
    //添加配置    
    $app->configure('JAuth');   
    $app->configure('auth');
    
    //注册服务
    $app->register(JMD\Auth\AuthServiceProvider::class);

**3、表迁移**             
复制      
JAuth/database/mgrations/create_ticket_table.php 到 database/migrations/2018_01_01_000000_create_ticket_table.php     
JAuth/database/mgrations/create_users_table.php 到 database/migrations/2018_01_01_000000_create_users_table.php

php artisan migrate

**5、配置用户model**          

user model 引入trait

    use Authenticatable, Authorizable, JAuthTrait;

可重写JAuthTrait中方法进行相应配置

model例：

    <?php

    namespace App;
    
    use Illuminate\Auth\Authenticatable;
    use JMD\Auth\Traits\JAuthTrait;
    use Laravel\Lumen\Auth\Authorizable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
    
    class User extends Model implements AuthenticatableContract, AuthorizableContract
    {
        use Authenticatable, Authorizable;
        use JAuthTrait;
    
        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'name', 'email',
        ];
    
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        protected $hidden = [
            'password',
        ];
    }
    
**6、配置中间件**         
可将 src/Middleware/JAuth.php 复制出来自定义或直接使用

bootstrap/app.php 中添加路由中间件 

     $app->routeMiddleware([
         'JAuth' => Yunhan\JAuth\Middleware\JAuthMiddleware::class,
     ]);

使用：   
需传递两个参数，user表示当前接口使用的guard(在config/auth.php配置)，第二个参数表示当前接口需登录状态。        
第二个参数传任意值表示需登录


    $router->get('test', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@test'
    ]);
    
    $router->group(['middleware' => 'JAuth:user,1'], function (\Laravel\Lumen\Routing\Router $api) {
        $api->get('test', 'Controller@test');
    });
    
> 建议为每个接口配置此中间件，无需登录认证的接口第二个参数不传即可

## 使用

填充user数据        
//php artisan db:seed

登录

    $router->get('login', 'Controller@login');

    use JMD\Auth\Auth;

    public function login()
    {
        //执行相应用户名密码认证
        //...
        $uid = 1;
        //token操作
        $token = Auth::login($uid);
        return $token;
    }
    
注销登录

    $router->get('logout', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@logout'
    ]);
    
    
    use JMD\Auth\Auth;
    
    public function logout()
    {
        Auth::logout();
        //...
    }
    
获取当前登录用户信息

    $router->get('selfInfo', [
        'middleware' => 'JAuth:user,1',
        'uses' => 'Controller@selfInfo'
    ]);
    
    public function selfInfo()
    {
        $user = Auth::user();
        $access = Auth::identity();
        return $user->email;
    }
    
### 自定义user与identity

JAuthTrait内定义有user返回方式与identity返回值得方法，可进行重写自定义返回。
