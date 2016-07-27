# laredis

Laredis can help you to make your `Laravel`/`Lumen` application become a redis server.

[中文文档](/docs/zh.md)

## Installation

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

## Usage
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

## Routing

Use redis command name as method name to route your request.

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

## Supported commands

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
