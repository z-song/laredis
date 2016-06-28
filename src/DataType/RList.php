<?php

namespace Encore\Redis\DataType;

class RList implements DataType
{
    public static function commands()
    {
        return Commands::rlist();
    }
}
