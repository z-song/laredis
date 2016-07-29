<?php

namespace Encore\Laredis\Server;

class Logo
{
    /**
     * Display the logo of laredis.
     *
     * @param string $version
     * @param string $host
     * @param int    $port
     * @param int    $pid
     *
     * @return void
     */
    public static function display($version, $host = '127.0.0.1', $port = 6379, $pid = 0)
    {
        $phpVersion = PHP_VERSION;

        echo <<<EOT

                _._
           _.-``__ ''-._
      _.-``    `.  `_.  ''-._           Laredis $version
  .-`` .-```.  ```\\/    _.,_ ''-._      PHP $phpVersion
 (    '      ,       .-`  | `,    )
 |`-._`-...-` __...-.``-._|'` _.-'|     Host: $host
 |    `-._   `._    /     _.-'    |     Port: $port
  `-._    `-._  `-./  _.-'    _.-'      PID: $pid
 |`-._`-._    `-.__.-'    _.-'_.-'|
 |    `-._`-._        _.-'_.-'    |
  `-._    `-._`-.__.-'_.-'    _.-'
 |`-._`-._    `-.__.-'    _.-'_.-'|
 |    `-._`-._        _.-'_.-'    |
  `-._    `-._`-.__.-'_.-'    _.-'
      `-._    `-.__.-'    _.-'
          `-._        _.-'      https://github.com/z-song/laredis
              `-.__.-'


EOT;
    }
}
