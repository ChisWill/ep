<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Controller extends \Ep\Base\Controller
{
    /**
     * @param ServerRequestInterface $request
     */
    protected function beforeAction($request): bool
    {
        return true;
    }

    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * 
     * @return ResponseInterface
     */
    protected function afterAction($request, $response)
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

    private ?View $view = null;

    protected function getView(): View
    {
        if ($this->view === null) {
            $this->view = Ep::getInjector()->make(View::class, ['context' => $this, 'viewPath' => Ep::getConfig()->viewPath]);
        }
        return $this->view;
    }

    protected function string(string $data = '', int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->getService()->string($data, $statusCode);
    }

    protected function json(array $data = []): ResponseInterface
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
