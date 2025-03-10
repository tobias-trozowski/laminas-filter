<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\UriNormalize;
use PHPUnit\Framework\TestCase;
use stdClass;

class UriNormalizeTest extends TestCase
{
    /**
     * @dataProvider abnormalUriProvider
     */
    public function testUrisAreNormalized($url, $expected)
    {
        $filter = new UriNormalize();
        $result = $filter->filter($url);
        $this->assertEquals($expected, $result);
    }

    public function testDefaultSchemeAffectsNormalization()
    {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider enforcedSchemeTestcaseProvider
     */
    public function testEnforcedScheme($scheme, $input, $expected)
    {
        $filter = new UriNormalize(['enforcedScheme' => $scheme]);
        $result = $filter->filter($input);
        $this->assertEquals($expected, $result);
    }

    public static function abnormalUriProvider()
    {
        return [
            ['http://www.example.com', 'http://www.example.com/'],
            ['hTTp://www.example.com/ space', 'http://www.example.com/%20space'],
            ['file:///www.example.com/foo/bar', 'file:///www.example.com/foo/bar'], // this should not be affected
            ['file:///home/shahar/secret/../../otherguy/secret', 'file:///home/otherguy/secret'],
            ['https://www.example.com:443/hasport', 'https://www.example.com/hasport'],
            ['/foo/bar?q=%711', '/foo/bar?q=q1'], // no scheme enforced
        ];
    }

    public static function enforcedSchemeTestcaseProvider()
    {
        return [
            ['ftp', 'http://www.example.com', 'http://www.example.com/'], // no effect - this one has a scheme
            ['mailto', 'mailto:shahar@example.com', 'mailto:shahar@example.com'],
            ['http', 'www.example.com/foo/bar?q=q', 'http://www.example.com/foo/bar?q=q'],
            ['ftp', 'www.example.com/path/to/file.ext', 'ftp://www.example.com/path/to/file.ext'],
            ['http', '/just/a/path', '/just/a/path'], // cannot be enforced, no host
        ];
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'http://www.example.com',
                    'file:///home/shahar/secret/../../otherguy/secret',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = new UriNormalize();

        $this->assertEquals($input, $filter($input));
    }
}
