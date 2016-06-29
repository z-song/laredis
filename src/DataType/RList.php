<?php

namespace Encore\Redis\DataType;

class RList implements DataType
{
    use DataTypeTrait;

    public static function commands()
    {
        return Commands::rlist();
    }
}
