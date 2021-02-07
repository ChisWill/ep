<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Standard\ViewInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Controller extends \Ep\Base\Controller
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function beforeAction($request): bool
    {
        return true;
    }

    /**
     * @param  ResponseInterface $response
     * 
     * @return ResponseInterface
     */
    protected function afterAction($response)
    {
        return $response;
    }

    private ?ViewInterface $view = null;

    protected function getView(): ViewInterface
    {
        if ($this->view === null) {
            $this->view = Ep::getInjector()->make(View::class, ['context' => $this, 'viewPath' => Ep::getConfig()->viewPath]);
        }
        return $this->view;
    }

    protected function render(string $view, array $params = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($this->getView()->render($view, $params));
        return $response;
    }

    protected function renderPartial(string $view, array $params = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($this->getView()->renderPartial($view, $params));
        return $response;
    }

    protected function redirect(string $url, $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader(Header::LOCATION, $url);
    }


    protected function jsonSuccess()
    {
    }

    protected function jsonError()
    {
    }
}
