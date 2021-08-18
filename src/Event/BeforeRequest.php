<?php

declare(strict_types=1);

namespace Ep\Event;

final class BeforeRequest
{
    /** 
     * @var mixed 
     */
    private $request;

    /** 
     * @var mixed 
     */
    private $response;

    /**
     * @param mixed $request
     * @param mixed $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
