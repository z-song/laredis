<?php

namespace Encore\Redis\DataType;

Trait DataTypeTrait
{
    /**
     * Get commands of this data type.
     *
     * @return array
     */
    public static function commands()
    {
        $class = explode('\\', get_called_class());
        $type = strtolower(end($class));

        return Commands::$type();
    }

    /**
     * Determine if this data type has given command.
     *
     * @param $command
     * @return bool
     */
    public static function hasCommand($command)
    {
        return in_array(strtoupper($command), static::commands());
    }
}
