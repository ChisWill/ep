<?php

declare(strict_types=1);

namespace Ep\Tests\Unit;

use Ep\Helper\Url;
use PHPUnit\Framework\TestCase;

class TestUrl extends TestCase
{
    public function pathProvider(): array
    {
        return [
            [
                'url' => '',
                'params' => [],
                'expect' => ''
            ], [
                'url' => 'a?b=1',
                'params' => [],
                'expect' => 'a?b=1'
            ], [
                'url' => 'a',
                'params' => ['a' => 'a1'],
                'expect' => 'a?a=a1'
            ], [
                'url' => '//a.b?d=3',
                'params' => ['d' => '4'],
                'expect' => '//a.b?d=4'
            ], [
                'url' => 'http://a.b?c=3&d=4',
                'params' => ['f' => 5, 'e' => 6],
                'expect' => 'http://a.b?f=5&e=6&c=3&d=4'
            ], [
                'url' => 'http://a.b?c=3&d=4',
                'params' => ['c' => 1, 'f' => 5],
                'expect' => 'http://a.b?c=1&f=5&d=4'
            ]
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testAddParams($url, $params, $expect)
    {
        $result = Url::addParams($url, $params);
        $this->assertSame($expect, $result);
    }
}
