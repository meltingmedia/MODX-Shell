<?php

class xdom
{
    public $modx;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    public function __call($method, $arguments)
    {
        if ($method !== 'outputArray') {
            array_unshift($arguments, $this);
            return call_user_func_array(array($this->modx, $method), $arguments);
        }
    }

    public function outputArray(array $array, $count = false)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($count === false) {
            $count = count($array);
        }

        return '{"total":"'.$count.'","results":'.$this->modx->toJSON($array).'}';
    }
}
