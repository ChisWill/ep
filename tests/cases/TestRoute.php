<?php

declare(strict_types=1);

namespace Ep\Tests\Cases;

use Ep\Base\Route;
use Ep\Helper\Arr;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;

use function FastRoute\simpleDispatcher;

class TestRoute extends TestCase
{
    public function pathProvider(): array
    {
        return [
            [
                'path' => '/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => '', 'action' => '', 'ns' => 'Ep\Tests\App\Controller\IndexController']
            ],
            [
                'path' => '/ctrl',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => '', 'ns' => 'Ep\Tests\App\Controller\CtrlController']
            ],
            [
                'path' => '/ctrl/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => '', 'ns' => 'Ep\Tests\App\Controller\CtrlController']
            ],
            [
                'path' => '/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\Controller\CtrlController']
            ],
            [
                'path' => '/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\Controller\CtrlController']
            ],
            [
                'path' => '/pre/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\pre\Controller\CtrlController']
            ],
            [
                'path' => '/pre/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\pre\Controller\CtrlController']
            ],
            [
                'path' => '/pre/fix/ctrl/act',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\pre\fix\Controller\CtrlController']
            ],
            [
                'path' => '/pre/fix/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre/fix', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\pre\fix\Controller\CtrlController']
            ],
            [
                'path' => '/pre2/3fix/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => 'pre2/3fix', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\pre2\3fix\Controller\CtrlController']
            ],
            [
                'path' => '/3pre2/3fix/of/ctrl/act/',
                'found' => Dispatcher::FOUND,
                'expect' => ['prefix' => '3pre2/3fix/of', 'controller' => 'ctrl', 'action' => 'act', 'ns' => 'Ep\Tests\App\3pre2\3fix\of\Controller\CtrlController']
            ],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testDefaultRules($path, $found, $expect)
    {
        $nsRet = Arr::remove($expect, 'ns');
        $route = new Route();
        $routeInfo = $route->matchRequest($path);
        $this->assertRuleResult($routeInfo, $path, $found, $expect);
        [$handler, $params] = $route->solveRouteInfo($routeInfo);
        $this->assertSame(trim($path, '/'), trim($handler, '/'));
        [$controller, $action] = $route->parseHandler($handler);
        $this->assertSame($nsRet, $controller);
    }

    private function assertRuleResult($routeInfo, $path, $found, $expect)
    {
        $this->assertSame($routeInfo[0], $found, sprintf('%s not found', $path));
        if ($routeInfo[0] == Dispatcher::FOUND) {
            foreach ($expect as $key => $value) {
                $this->assertSame($value, trim($routeInfo[2][$key], '/'), sprintf('%s failed', $path));
            }
        }
    }

    public function ruleProvider()
    {
        return [
            [
                'rule' => function (RouteCollector $route) {
                    $route->addGroup('/api', function (RouteCollector $r) {
                        $r->get('/v{ver:\d+}/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api//v<ver>/<ctrl>/<act>');
                    });
                },
                'cases' => [
                    [
                        'path' => '/api/v1/user/index',
                        'found' => Dispatcher::FOUND,
                        'expect' => ['ns' => 'Ep\Tests\App\api\Controller\v1\UserController', 'a' => 'index', 'p' => []]
                    ], [
                        'path' => '/api/v1/user',
                        'found' => Dispatcher::NOT_FOUND,
                    ]
                ]
            ], [
                'rule' => function (RouteCollector $route) {
                    $route->addGroup('/api/{prefix:\w+}', function (RouteCollector $r) {
                        $r->get('/v{ver:\d+}/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api//v<ver>/<ctrl>/<act>');
                    });
                },
                'cases' => [
                    [
                        'path' => '/api/te1/v1/user/index',
                        'found' => Dispatcher::FOUND,
                        'expect' => ['ns' => 'Ep\Tests\App\api\Controller\v1\UserController', 'a' => 'index', 'p' => ['prefix' => 'te1']]
                    ], [
                        'path' => '/api/v1/user/index',
                        'found' => Dispatcher::NOT_FOUND,
                    ], [
                        'path' => '/api/tet/v1/user/',
                        'found' => Dispatcher::NOT_FOUND,
                    ]
                ]
            ], [
                'rule' => function (RouteCollector $route) {
                    $route->addGroup('/api', function (RouteCollector $r) {
                        $r->get('/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api/a/b/<ctrl>/<act>');
                    });
                },
                'cases' => [
                    [
                        'path' => '/api/user/index',
                        'found' => Dispatcher::FOUND,
                        'expect' => ['ns' => 'Ep\Tests\App\api\a\b\Controller\UserController', 'a' => 'index', 'p' => []]
                    ], [
                        'path' => '/user/index',
                        'found' => Dispatcher::NOT_FOUND,
                    ]
                ]
            ]
        ];
    }

    private function matchRequest($rule, string $path): array
    {
        return simpleDispatcher(function (RouteCollector $route) use ($rule) {
            call_user_func($rule, $route);
        })->dispatch('GET', rtrim($path, '/') ?: '/');
    }

    /**
     * @dataProvider ruleProvider
     */
    public function testRules($rule, $cases)
    {
        $route = new Route();
        foreach ($cases as $row) {
            $routeInfo = $this->matchRequest($rule, $row['path']);
            $this->assertSame($row['found'], $routeInfo[0]);
            if ($routeInfo[0] == $row['found'] && $row['found'] == Dispatcher::FOUND) {
                [$handler, $params] = $route->solveRouteInfo($routeInfo);
                [$controller, $action] = $route->parseHandler($handler);
                $this->assertSame($row['expect']['ns'], $controller);
                $this->assertSame($row['expect']['a'], $action);
                $this->assertSame($row['expect']['p'], $params);
            }
        }
    }
}
