<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Base\Config;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Web\Event\EndBody;
use Psr\Http\Message\ServerRequestInterface;

final class Event
{
    /**
     * @Inject
     */
    private Config $config;

    public function before(BeforeRequest $beforeRequest)
    {
        $request = $beforeRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
    }

    public function after(AfterRequest $afterRequest)
    {
        $request = $afterRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
    }

    public function endBody(EndBody $endBody)
    {
        $view = $endBody->getView();
        echo $view->renderPartial('/index/index', ['message' => 'end body event message']);
    }
}
