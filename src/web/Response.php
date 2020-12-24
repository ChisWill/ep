<?php

namespace ep\web;

use ep\base\Response as BaseResponse;
use ep\helper\Ep;

class Response extends BaseResponse
{
    public View $view;

    public function render($path = [], $params = []): Response
    {
        if (is_array($path)) {
            $params = $path;
            $caller = debug_backtrace()[1];
            $path = Ep::parseControllerName($caller['class']) . '/' . $caller['function'];
        }
        return $this->string($this->view->render($path, $params));
    }

    public function json(array $data = []): Response
    {
        $this->setHeader('Content-type', 'application/json');
        return $this->string(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function jsonSuccess($data = []): Response
    {
        return $this->json([
            'code' => 0,
            'msg' => 'OK',
            'body' => $data
        ]);
    }

    public function jsonError(string $msg, $code = 500): Response
    {
        return $this->json([
            'code' => $code,
            'msg' => $msg,
            'body' => []
        ]);
    }

    public function setHeader(string $name, string $value): void
    {
        header(sprintf('%s: %s', $name, $value));
    }

    public function setHeaders(array $headers = []): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }
}
