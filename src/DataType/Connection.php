<?php

namespace Encore\Redis\DataType;

class Connection implements DataType
{
    public static function commands()
    {
        return Commands::connection();
    }


}
