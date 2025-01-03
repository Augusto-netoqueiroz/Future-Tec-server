<?php

namespace App\Services;

class Message
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }
}
