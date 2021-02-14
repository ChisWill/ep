<?php

declare(strict_types=1);

namespace Ep\Tests\Cases;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;

class TestRoute extends TestCase
{
    public function ruleProvider()
    {
        return [
            [
                'rule' => function (RouteCollector $route) {
                    $rule = Ep::getConfig()->defaultRoute;
                    $route->addGroup('/', fn (RouteCollector $r) => $r->addRoute(...$rule));
                },
                'cases' => [
                    [
                        'path' => '/',
                        'handler' => '//',
                        'params' => []
                    ],
                    [
                        'path' => '/ctrl',
                        'handler' => '/ctrl/',
                        'params' => []
                    ],
                    [
                        'path' => '/ctrl/act',
                        'handler' => '/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre/ctrl/act',
                        'handler' => 'pre/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre/ctrl/act/',
                        'handler' => 'pre/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre/fix/ctrl/act',
                        'handler' => 'pre/fix/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre/fix/ctrl/act/',
                        'handler' => 'pre/fix/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre2/3fix/ctrl/act/',
                        'handler' => 'pre2/3fix/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/3pre2/3fix/of/ctrl/act/',
                        'handler' => '3pre2/3fix/of/ctrl/act',
                        'params' => []
                    ],
                ]
            ],
            [
                'rule' => function (RouteCollector $route) {
                    $route->addGroup('/api', function (RouteCollector $r) {
                        $r->get('/v{ver:\d+}/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api//v<ver>/<ctrl>/<act>');
                    });
                },
                'cases' => [
                    [
                        'path' => '/api/v1/user/index',
                        'params' => [],
                        'handler' => 'api//v1/user/index',
                    ], [
                        'path' => '/api/v1/user',
                        'params' => [],
                        'handler' => 'index/miss',
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
                        'params' => ['prefix' => 'te1'],
                        'handler' => 'api//v1/user/index',
                    ], [
                        'path' => '/api/v1/user/index',
                        'params' => [],
                        'handler' => 'index/miss',
                    ], [
                        'path' => '/api/tet/v1/user/',
                        'params' => [],
                        'handler' => 'index/miss',
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
                        'params' => [],
                        'handler' => 'api/a/b/user/index',
                    ], [
                        'path' => '/user/index',
                        'params' => [],
                        'handler' => 'index/miss',
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider ruleProvider
     */
    public function testRules($rule, $cases)
    {
        foreach ($cases as $row) {
            $route = new Route($rule);
            $route->default = false;
            [$handler, $params] = $route->match($row['path']);
            $this->assertSame($row['handler'], $handler, $row['path'] . ' is wrong.');
            $this->assertSame($row['params'], $params, $row['path'] . ' is wrong.');
        }
    }
}
