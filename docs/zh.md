# laredis

使用`Laredis`将你的`laravel`/`Lumen`应用变成redis服务器.


## 安装

```
composer require encore/laredis "dev-master"
```

在`laravel`框架中，在`config/app.php`加入:
```
Encore\Laredis\ServerServiceProvider::class,
```

在`Lumen`框架中，在`bootstrap/app.php`中加入：
```
$app->register(Encore\Laredis\ServerServiceProvider::class);
```

运行下面的命令完成安装：
```
php artisan vendor:publish --tag=laredis
```

## 使用
在`routes.php`文件里面添加路由：
```
app('redis.router')->group([
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'redis.auth'
], function ($router) {

    $router->command('get', 'users:{id}:{key}', function ($id, $key) {

        $user = \App\User::findOrFail($id);

        return $user->$key;
    });

    $router->command('hgetall', 'users:{id}', function ($id) {
    
        $user = \App\User::findOrFail($id);
        
        return $user->toArray();
        
    });
});
```

启动服务:
```
php artisan redis-server start
```

可以使用`-d`参数让服务在后台运行

接下来就可以用任何redis客户端来连接操作redis服务了:

```

// 控制台
$ redis-cli

127.0.0.1:6379> auth 123456
OK

127.0.0.1:6379> ping
PONG

127.0.0.1:6379> get users:10:name
"Joe Doe"

127.0.0.1:6379> hgetall users:10
 1) "id"
 2) (integer) 10
 3) "name"
 4) "Joe Doe"
 5) "email"
 6) "joe@gmail.com"
 7) "avatar"
 8) (nil)
 9) "created_at"
10) "2016-01-27 16:51:49"
11) "updated_at"
12) "2016-06-30 13:50:38"

```


## 路由

在lumen框架中使用命令作为路由方法来定义路由：


```

// in routes.php
$router->get('users:{id}:{key}', 'UserController@get');
$router->set('users:{id}:{key}', 'UserController@set');
$router->hgetall('users:{id}',   'UserController@hgetall');
$router->hget('users:{id}',   'UserController@hget');

// in UserController.php

use Encore\Laredis\Routing\Controller;

class UserController extends Controller
{
    public function get($id, $key)
    {
        $user = User::findOrFail($id);

        return $user->$key;
    }
    
    public function set($id, $key, $val)
    {
        $user = User::findOrFail($id);

        return $user->$key;
    }

    public function hgetall($id)
    {
        $user = User::findOrFail($id);

        return $user->toArray();
    }
    
    public function hget($id, $field)
    {
        $user = User::findOrFail($id);

        return $user->$field;
    }
}


```

## 目前支持的命令

+ AUTH
+ ECHO
+ PING
+ QUIT
+ SELECT

+ TIME

+ GET
+ SET
+ GETSET
+ TRLEN
+ MGET
+ MSET

+ DEL
+ EXISTS

+ HGET
+ HSET
+ HGETALL
+ HVALS
+ HKEYS
+ HDEL
+ HLEN
+ HMGET
+ HMSET

+ LINDEX
+ LRANGE
+ LLEN

+ SADD
+ SCARD
+ SDIFF
+ SISMEMBER
+ SMEMBERS
+ SREM
+ SRANDMEMBER

+ MULTI
+ EXEC
+ DISCARD

# License

[WTFPL](http://www.wtfpl.net/)

