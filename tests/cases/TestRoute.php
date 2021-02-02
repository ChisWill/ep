<?php

declare(strict_types=1);

namespace Ep\Tests\Cases;

use Ep\Base\Route;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;

class TestRoute extends TestCase
{
    public function pathProvider(): array
    {
        return [
            [
                'path' => '/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => '', 'action' => '']
            ],
            [
                'path' => '/ctrl',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => '']
            ],
            [
                'path' => '/ctrl/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => '']
            ],
            [
                'path' => '/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/pre/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/pre/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/pre/fix/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/pre/fix/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/pre2/3fix/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre2/3fix', 'controller' => 'ctrl', 'action' => 'act']
            ],
            [
                'path' => '/3pre2/3fix/of/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '3pre2/3fix/of', 'controller' => 'ctrl', 'action' => 'act']
            ],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testRules($path, $found, $expect)
    {
        $route = new Route();
        $routeInfo = $route->match($path);
        $this->assertRuleResult($routeInfo, $path, $found, $expect);
        [$handler, $params] = $route->solveRouteInfo($routeInfo);
        $this->assertEquals(trim($path, '/'), trim($handler, '/'));
    }

    private function assertRuleResult($routeInfo, $path, $found, $expect)
    {
        $this->assertEquals($routeInfo[0], $found, sprintf('%s not found', $path));
        if ($routeInfo[0] == Dispatcher::FOUND) {
            foreach ($expect as $key => $value) {
                $this->assertEquals($value, trim($routeInfo[2][$key], '/'), sprintf('%s failed', $path));
            }
        }
    }
}
