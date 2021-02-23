<?php

declare(strict_types=1);

namespace Ep\Tests\Unit;

use Ep\Helper\Curl;
use PHPUnit\Framework\TestCase;

class TestCurl extends TestCase
{
    public function singleGetProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/json';
        return [
            [
                'url' => $baseUrl . '',
                'data' => '',
                'expect' => ['hello' => 'world']
            ], [
                'url' => $baseUrl . '?name=peter',
                'data' => '',
                'expect' => ['name' => 'peter']
            ], [
                'url' => $baseUrl . '?name=peter2',
                'data' => 'age=123',
                'expect' => [
                    'body' => 'age=123',
                    'get' => ['name' => 'peter2']
                ]
            ], [
                'url' => $baseUrl . '?name=peter3',
                'data' => 'age=123',
                'expect' => [
                    'body' => 'age=123',
                    'get' => ['name' => 'peter3']
                ]
            ],
        ];
    }

    /**
     * @dataProvider singleGetProvider
     */
    public function testSingleGet($url, $data, $expect)
    {
        $result = Curl::get($url, $data);
        $this->assertSame($expect, json_decode($result, true));
    }

    public function singlePostProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/json';
        return [
            [
                'url' => $baseUrl . '',
                'data' => [
                    'title' => 'good'
                ],
                'expect' => [
                    'post' => ['title' => 'good'],
                    'get' => [],
                ]
            ], [
                'url' => $baseUrl . '?name=mary',
                'data' => [
                    'title' => 'good'
                ],
                'expect' => [
                    'post' => ['title' => 'good'],
                    'get' => ['name' => 'mary'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider singlePostProvider
     */
    public function testSinglePost($url, $data, $expect)
    {
        $result = Curl::post($url, $data);
        $this->assertSame($expect, json_decode($result, true));
    }

    public function multiGetProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/json';
        $singleProvider = $this->singleGetProvider();
        $count = count($singleProvider) - 1;
        return [
            [
                'urls' => $baseUrl,
                'data' => '',
                'batch' => 1,
                'expect' => [
                    ['hello' => 'world']
                ]
            ], [
                'urls' => $baseUrl . '?name=peter',
                'data' => 'age=1',
                'batch' => 1,
                'expect' => [
                    [
                        'body' => 'age=1',
                        'get' => ['name' => 'peter']
                    ],
                    [
                        'body' => 'age=1',
                        'get' => ['name' => 'peter']
                    ]
                ]
            ], [
                'urls' => $baseUrl . '?name=bob',
                'data' => 'age=12',
                'batch' => 2,
                'expect' => [
                    [
                        'body' => 'age=12',
                        'get' => ['name' => 'bob']
                    ],
                    [
                        'body' => 'age=12',
                        'get' => ['name' => 'bob']
                    ]
                ]
            ], [
                'urls' => [$baseUrl, $singleProvider[$n = mt_rand(0, $count)]['url']],
                'data' => ['', $singleProvider[$n]['data']],
                'batch' => 1,
                'expect' => [
                    ['hello' => 'world'],
                    $singleProvider[$n]['expect']
                ]
            ], [
                'urls' => [$singleProvider[$m = mt_rand(0, $count)]['url'], $singleProvider[$n = mt_rand(0, $count)]['url']],
                'data' => [$singleProvider[$m]['data'], $singleProvider[$n]['data'], ''],
                'batch' => 13,
                'expect' => [
                    $singleProvider[$m]['expect'],
                    $singleProvider[$n]['expect']
                ]
            ], [
                'urls' => $baseUrl . '?name=sai',
                'data' => ['a=1', 'b=1', 'c=1'],
                'batch' => 2,
                'expect' => [
                    [
                        'body' => 'a=1',
                        'get' => ['name' => 'sai']
                    ],
                    [
                        'body' => 'b=1',
                        'get' => ['name' => 'sai']
                    ],
                    [
                        'body' => 'c=1',
                        'get' => ['name' => 'sai']
                    ]
                ]
            ], [
                'urls' => [$baseUrl . '?name=sai', $baseUrl . '?name=sai2', $baseUrl . '?name=sai3'],
                'data' => 'age=3',
                'batch' => 2,
                'expect' => [
                    [
                        'body' => 'age=3',
                        'get' => ['name' => 'sai']
                    ],
                    [
                        'body' => 'age=3',
                        'get' => ['name' => 'sai2']
                    ],
                    [
                        'body' => 'age=3',
                        'get' => ['name' => 'sai3']
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider multiGetProvider
     */
    public function testMultiGet($urls, $data, $batch, $expect)
    {
        $result = Curl::getMulti($urls, $data, [], $batch);
        foreach ($result as $k => $ret) {
            $this->assertSame($expect[$k], json_decode($ret, true));
        }
    }

    public function multiPostProvider(): array
    {
        $baseUrl = 'http://ep.cc/demo/json';
        $singleProvider = $this->singlePostProvider();
        $count = count($singleProvider) - 1;
        return [
            [
                'urls' => $baseUrl . '?a=1',
                'data' => ['desc' => 'hello'],
                'batch' => 1,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ]
                ]
            ], [
                'urls' => $baseUrl . '?a=1',
                'data' => ['desc' => 'hello'],
                'batch' => 2,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ],
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ]
                ]
            ], [
                'urls' => $baseUrl . '?a=1',
                'data' => [['desc' => 'hello'], ['title' => 'wolrd']],
                'batch' => 12,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ], [
                        'post' => [
                            'title' => 'wolrd'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ]
                ]
            ], [
                'urls' => [$baseUrl . '?a=1', $baseUrl . '?a=2'],
                'data' => ['desc' => 'hello'],
                'batch' => 12,
                'expect' => [
                    [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '1'
                        ]
                    ], [
                        'post' => [
                            'desc' => 'hello'
                        ],
                        'get' => [
                            'a' => '2'
                        ]
                    ]
                ]
            ], [
                'urls' => [$singleProvider[$m = mt_rand(0, $count)]['url'], $singleProvider[$n = mt_rand(0, $count)]['url']],
                'data' => [$singleProvider[$m]['data'], $singleProvider[$n]['data'], ''],
                'batch' => 13,
                'expect' => [
                    $singleProvider[$m]['expect'],
                    $singleProvider[$n]['expect']
                ]
            ],
        ];
    }

    /**
     * @dataProvider multiPostProvider
     */
    public function testMultiPost($urls, $data, $batch, $expect)
    {
        $result = Curl::postMulti($urls, $data, [], $batch);
        foreach ($result as $k => $ret) {
            $this->assertSame($expect[$k], json_decode($ret, true));
        }
    }
}
