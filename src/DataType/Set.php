<?php

namespace Encore\Redis\DataType;

class Set implements DataType
{
    public static function commands()
    {
        return Commands::set();
    }
}
