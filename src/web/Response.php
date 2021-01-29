<?php

namespace Ep\Web;

class Response
{
    public View $view;

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

    public function render($path = [], $params = []): Response
    {
        if (is_array($path)) {
            $params = $path;
            $caller = debug_backtrace()[1];
            $path = Ep::parseControllerName($caller['class']) . '/' . $caller['function'];
        }
        return $this->string($this->view->render($path, $params));
    }

    public function redirect(string $url, $code = 302)
    {
        $this->_statusCode = $code;
        $this->setHeader('Location', $url);
        return $this->string('');
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

    public function jsonError($msg, $code = 500): Response
    {
        return $this->json([
            'code' => $code,
            'msg' => $msg,
            'body' => []
        ]);
    }

    private $_statusCode = 200;

    protected function setStatusCode()
    {
        http_response_code($this->_statusCode);
    }

    public function sendContent()
    {
        $this->setStatusCode();
        $this->sendHeaders();

        parent::sendContent();
    }

    private array $_headers = [];

    public function setHeader(string $name, string $value): void
    {
        $this->_headers[$name] = $value;
    }

    public function setHeaders(array $headers = []): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    protected function sendHeaders()
    {
        foreach ($this->_headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
    }
}
