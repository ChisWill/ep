<?php

declare(strict_types=1);

namespace Ep\Tests\Cases;

use Ep\Base\Router;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;

class TestRouter extends TestCase
{
    private array $paths = [
        '/' => ['prefix' => '', 'controller' => '', 'action' => ''],
        '/ctrl' => ['prefix' => '', 'controller' => 'ctrl', 'action' => ''],
        '/ctrl/' => ['prefix' => '', 'controller' => 'ctrl', 'action' => ''],
        '/ctrl/act' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act'],
        '/ctrl/act/' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act'],
        '/pre/ctrl/act' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act'],
        '/pre/ctrl/act/' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act'],
        '/pre/fix/ctrl/act' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act'],
        '/pre/fix/ctrl/act/' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act'],
        '/pre2/3fix/ctrl/act/' => ['prefix' => 'pre2/3fix', 'controller' => 'ctrl', 'action' => 'act'],
        '/3pre2/3fix/of/ctrl/act/' => ['prefix' => '3pre2/3fix/of', 'controller' => 'ctrl', 'action' => 'act'],
    ];

    private array $simplePaths = [
        '/' => ['controller' => '', 'action' => ''],
        '/ctrl' => ['controller' => 'ctrl', 'action' => ''],
        '/ctrl/' => ['controller' => 'ctrl', 'action' => ''],
        '/ctrl/act' => ['controller' => 'ctrl', 'action' => 'act'],
        '/ctrl/act/' => ['controller' => 'ctrl', 'action' => 'act'],
    ];

    public function testRules()
    {
        $router = new Router();
        foreach ($this->paths as $path => $result) {
            $routeInfo = $router->match($path);
            $this->assertRuleResult($routeInfo, $result);
        }
    }

    public function testSimpleRules()
    {
        $router = new Router();
        foreach ($this->simplePaths as $path => $expect) {
            $routeInfo = $router->match($path);
            $this->assertRuleResult($routeInfo, $expect);
        }
    }

    public function testMatchRules()
    {
        $router = new Router();
        foreach ($this->paths as $path => $expect) {
            [$handler, $params] = $router->solveRouteInfo($router->match($path));
            $this->assertEquals(trim($path, '/'), trim($handler, '/'));
        }
    }

    private function assertRuleResult($routeInfo, $expect)
    {
        $this->assertEquals($routeInfo[0], Dispatcher::FOUND);
        if ($routeInfo[0] == Dispatcher::FOUND) {
            foreach ($expect as $key => $value) {
                $this->assertEquals($value, trim($routeInfo[2][$key], '/'));
            }
        }
    }
}
