<?php

namespace Encore\Redis\DataType;

Trait DataTypeTrait
{
    public static function commands()
    {
        return;
    }

    public static function hasCommand($command)
    {
        return in_array(strtoupper($command), static::commands());
    }
}
