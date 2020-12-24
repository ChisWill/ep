<?php

namespace ep\base;

class Response
{
    private $_content;
    private $_stream;

    public function sendContent()
    {
        echo $this->_content;
    }

    public function string(String $string): Response
    {
        $this->_content = $string;
        return $this;
    }

    public function stream()
    {
    }
}
