<?php

declare(strict_types=1);

namespace Ep\Tests\Unit;

use Ep;
use Ep\Base\Route;
use Ep\Contract\NotFoundException;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;

class TestRoute extends TestCase
{
    public function ruleProvider()
    {
        return [
            [
                'rule' => function (RouteCollector $route) {
                    $rule = Ep::getConfig()->getDefaultRoute();
                    $route->addGroup(Ep::getConfig()->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$rule));
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
                        'path' => '/ctrl-suffix',
                        'handler' => '/ctrl-suffix/',
                        'params' => []
                    ],
                    [
                        'path' => '/ctrl/act',
                        'handler' => '/ctrl/act',
                        'params' => []
                    ],
                    [
                        'path' => '/ctrl-suffix/act-suf',
                        'handler' => '/ctrl-suffix/act-suf',
                        'params' => []
                    ],
                    [
                        'path' => '/pre-s/ctrl-f/act-x',
                        'handler' => 'pre-s/ctrl-f/act-x',
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
                        'path' => '/pre/fix/ctrl-suffix/act',
                        'handler' => 'pre/fix/ctrl-suffix/act',
                        'params' => []
                    ],
                    [
                        'path' => '/pre-2/fix-n/ctrl-f/act-a',
                        'handler' => 'pre-2/fix-n/ctrl-f/act-a',
                        'params' => []
                    ]
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
                        'handler' => false,
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
                        'handler' => false,
                    ], [
                        'path' => '/api/tet/v1/user/',
                        'params' => [],
                        'handler' => false,
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
                        'handler' => false,
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
        $route = Ep::getDi()->get(Route::class)->configure([
            'defaultRoute' => false
        ]);
        foreach ($cases as $row) {
            try {
                [, $handler, $params] = $route
                    ->clone([
                        'rule' => $rule,
                    ])
                    ->match($row['path']);
                $this->assertSame($row['handler'], $handler, $row['path'] . ' is wrong.');
                $this->assertSame($row['params'], $params, $row['path'] . ' is wrong.');
            } catch (NotFoundException $e) {
                $this->assertSame($row['handler'], false);
            }
        }
    }
}
