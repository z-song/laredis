<?php

namespace Encore\Redis\DataType;

class String implements DataType
{
    public function commands()
    {
        return Commands::string();
    }
}
