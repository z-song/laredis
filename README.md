# laredis

Laredis can help you to make your `Laravel`/`Lumen` application become a redis server.

[中文文档](/docs/zh.md)

##Installation

```
composer require encore/laredis "dev-master"
```

For laravel add `ServerServiceProvider` to `config/app.php`:
```
Encore\Laredis\ServerServiceProvider::class,
```

For lumen in `bootstrap/app.php` add 
```
$app->register(Encore\Laredis\ServerServiceProvider::class);
```

Then run these commands to finish installation:

```
php artisan vendor:publish --tag=laredis
```

##Usage
In routes.php add:
```
app('redis.router')->group([
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'redis.auth'
], function ($router) {

    $router->command('get', 'users:{id}:{key}', function ($id, $key) {

        $user = \App\User::findOrFail($id);

        return $user->$key;`
    });

    $router->command('hgetall', 'users:{id}', function ($id) {
    
        $user = \App\User::findOrFail($id);
        
        return $user->toArray();
        
    });
});
```

Start the server:
```
php artisan redis-server start
```

Or use `-d` option to run service in daemon mode.

Then use any kind of redis client to access the server:

```

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

##Laravel Routing


In laravel use methods `string()` `set()` `hash()` `list()` to map request to controller method.
```
// in routes.php
$router->string('users:{id}:{key}', UserController::class);
$router->set('users:{id}:friends', FriendController::class);
//$router->hash('article:{id}', ArticleController::class);
//$router->list('users:{id}:books', BookController::class);

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

##Lumen Routing

In lumen use command name as method name to route your request.

```
$router->get('users:{id}:{key}', 'UserController@get');

$router->set('users:{id}:{key}', 'UserController@set');

$router->hgetall('users:{id}',   'UserController@hgetall');

$router->hget('users:{id}:{key}',   'UserController@hget');
```

##Supported commands

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
