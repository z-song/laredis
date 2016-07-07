# laredis

使用`Laredis`将你的`laravel`应用变成redis服务器.


##安装

```
composer require encore/laredis "dev-master"
```

在`config/app.php`加入`ServerServiceProvider`:

```
Encore\Laredis\ServerServiceProvider::class,
```

运行下面的命令完成安装：
```
php artisan vendor:publish --tag=laredis
```

##使用
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

##使用控制器来处理请求


```
// in routes.php
$router->string('users:{id}:{key}', UserController::class);
$router->set('users:{id}:friends', FriendController::class);

// in UserController.php

// handle `get users:{id}:{key}` command
public function get($id, $key)
{
    $user = User::find($id);

    return $user->$key;
}

// handle `set users:{id}:{key} $val` command
public function set($id, $key, $val)
{
    $user = User::find($id);

    $user->$key = $val;

    return $user->save();
}

// handle `strlen users:{id}:{key}` command
public function strlen($id, $key)
{
    $user = User::find($id);

    return strlen($user->$key);
}

// in FriendController.php

// handle `sadd users:{id}:friends {fid}` command
public function sadd($id, $fid)
{
    $user = User::findOrFail($id);

    return $user->friends()->attach($fid);
}

// handle `srem users:{id}:friends {fid}` command
public function srem($id, $fid)
{
    $user = User::findOrFail($id);

    return $user->friends()->detach($fid);
}

// handle `smembers users:{id}:friends` command
public function smembers($id)
{
    $user = User::findOrFail($id);

    return $user->friends->pluck('id');
}

// handle `scard users:{id}:friends` command
public function scard($id)
{
    $user = User::findOrFail($id);

    return $user->friends()->count();
}
```

##目前支持以下命令

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

#License

[WTFPL](http://www.wtfpl.net/)
