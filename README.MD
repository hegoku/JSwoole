### 环境

php>=7.2

swoole>=4.1.0

### 安装

```sh
$ composer install
$ cp env.example env
```

### 运行

```sh
$ php server.php
```

### 路由

编辑 `/config/route.php`

```php
return [
    ['get', '/', 'IndexController@index'],
    ['post', 'login', 'IndexController@login'],
    ['HEAD/get', 'c', 'IndexController@c', 'middleware'=>['\App\Middlewares\CustomMiddlewares']],
    ['*', 'b', 'IndexController@b']
];
```

### Controller

放在 `/app/Controllers` 目录, 继承 `JSwoole\Controller` 类

```php
class IndexController extends JSwoole\Controller
{
    public function index()
    {
        $data=$this->request->post('data');
        return $this->asJson(['code'=>200, 'data'=>$data]);
    }
}
```

### Request

`JSwoole\Request` 继承Laravel的Request类, 详见[https://laravel.com/docs/5.6/requests](https://laravel.com/docs/5.6/requests), 每次请求进来会赋给Controller的$request属性

```php
class IndexController extends JSwoole\Controller
{
    public function index()
    {
        $data=$this->request->post('data', []);
        $query_a=$this->request->get('a', 1);
        return $this->asJson(['code'=>200, 'data'=>$data]);
    }
}
```

### 数据库

配置放在 `/config/app.php` 的 `connection_config` 数组里

`\JSwoole\Database\DB` 类是对Laravel的数据库模块和Swoole的Mysql协程的组装, 详见[https://laravel.com/docs/5.6/queries](https://laravel.com/docs/5.6/queries)

```php
use JSwoole\JSwoole;

class IndexController extends JSwoole\Controller
{
    public function index()
    {
        $data=$this->request->post('data');
        $a=JSwoole::app()->db->connection('default')->table('user')->select('*')->limit(1)->get();
        JSwoole::app()->db->connection('default')->insert("INSERT INTO user (name) VALUES ('fdsfa')");
        $c=\App\Models\UserModel::take(5)->get();
        return $this->asJson(['code'=>200, 'data'=>$data]);
    }
}
```

### Model

继承 `JSwoole\Model` 类, `JSwoole\Model` 类继承Laravel的Model类, 详见[https://laravel.com/docs/5.6/eloquent](https://laravel.com/docs/5.6/eloquent)

```php
class UserModel extends \JSwoole\Database\Model
{
    protected $connection = 'default';
    protected $table = 'user';
    public $timestamps=false;
}
```