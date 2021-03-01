<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ContextTrait;
use Ep\Contract\ControllerInterface;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Controller implements ControllerInterface
{
    use ContextTrait;

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function before($request)
    {
        return true;
    }

    /**
     * @param  ServerRequestInterface $request
     * @param  mixed                  $response
     * 
     * @return mixed
     */
    public function after($request, $response)
    {
        return $response;
    }

    private ?Service $service = null;

    protected function getService(): Service
    {
        if ($this->service === null) {
            $this->service = Ep::getDi()->get(Service::class);
        }
        return $this->service;
    }

    public function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }

    protected function string(string $data = '', int $statusCode = Status::OK): ResponseInterface
    {
        return $this->getService()->string($data, $statusCode);
    }

    /**
     * @param mixed $data
     */
    protected function json($data = []): ResponseInterface
    {
        return $this->getService()->json($data);
    }

    protected function render(string $view, array $params = []): ResponseInterface
    {
        return $this->getService()->string($this->getView()->render($view, $params));
    }

    protected function renderPartial(string $view, array $params = []): ResponseInterface
    {
        return $this->getService()->string($this->getView()->renderPartial($view, $params));
    }

    protected function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->getService()->redirect($url, $statusCode);
    }
}
