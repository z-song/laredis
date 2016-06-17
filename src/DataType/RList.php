<?php

namespace Encore\Redis\DataType;

class RList implements DataType
{
    public function commands()
    {
        return Commands::rlist();
    }
}
