<?php

namespace Encore\Redis\DataType;

class Hash implements DataType
{
    public function commands()
    {
        return Commands::hash();
    }
}
