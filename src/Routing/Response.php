<?php

namespace Encore\Redis\Routing;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Response
{
    const ERR = 0;

    const OK  = 1;

    protected $content;

    protected $status;

    public function __construct($value, $status = 1)
    {
        $this->value = $value;
        $this->status = $status;
    }

    public function value()
    {
        return $this->value;
    }

    protected function error($message = '')
    {
        if (empty($message)) {
            $message = $this->value;
        }

        if ($message) {
            $message = " $message";
        }

        return "-Error$message\r\n";
    }

    protected function ok()
    {
        return "+OK\r\n";
    }

    protected function encode($payload)
    {
        $output = '';

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        }

        if (is_null($payload)) {
            return "$-1\r\n";
        }

        if (is_string($payload)) {
            $len = strlen($payload);
            return "\$$len\r\n$payload\r\n";
        }

        if (is_int($payload)) {
            return ":$payload\r\n";
        }

        if (is_bool($payload)) {
            return $payload ? $this->ok() : $this->error();
        }

        if (is_array($payload)) {

            if (Arr::isAssoc($payload)) {
                $bulk = "*".count($payload)*2 . "\r\n";
                foreach ($payload as $key => $value) {
                    $bulk .= $this->encode($key);
                    $bulk .= $this->encode($value);
                }

                return $bulk;
            }

            //foreach ($payload as $value) {
            $bulk = "*".count($payload) . "\r\n";
            foreach ($payload as $key => $value) {
                $bulk .= $this->encode($value);
            }

            return $bulk;
            //}
        }

        return $output;
    }

    public function render()
    {
        if ($this->status == static::ERR) {
            return $this->error();
        }

        return $this->encode($this->value);
    }

    public function __toString()
    {
        return $this->render();
    }
}
