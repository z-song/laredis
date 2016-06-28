<?php

namespace Encore\Redis\DataType;

class Hash implements DataType
{
    public static function commands()
    {
        return Commands::hash();
    }
}
