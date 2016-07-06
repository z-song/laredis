<?php

namespace Encore\Laredis\Server;

class Logo
{
    public static function display($version, $host = '127.0.0.1', $port = 6379, $pid = 0)
    {
        echo <<<EOT

                _._
           _.-``__ ''-._
      _.-``    `.  `_.  ''-._           Laredis $version
  .-`` .-```.  ```\\/    _.,_ ''-._
 (    '      ,       .-`  | `,    )     Host: $host
 |`-._`-...-` __...-.``-._|'` _.-'|     Port: $port
 |    `-._   `._    /     _.-'    |     PID: $pid
  `-._    `-._  `-./  _.-'    _.-'
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
