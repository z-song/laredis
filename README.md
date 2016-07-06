# laredis

Laredis can help you to make your laravel application become a redis server.


##Installation

```
composer require encore/laredis "dev-master"
```

Add `ServerServiceProvider` to `config/app.php`:

```
Encore\Redis\ServerServiceProvider::class
```

Then run these commands to finish installation:

```
php artisan vendor:publish
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

        return $user->$key;
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

127.0.0.1:6379>auth 123456
OK

127.0.0.1:6379>ping
PONG

127.0.0.1:6379>get users:10:name
"Joe Doe"

127.0.0.1:6379>hgetall users:10
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

#License

[WTFPL](http://www.wtfpl.net/)
