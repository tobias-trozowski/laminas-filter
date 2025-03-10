<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\PregReplace as PregReplaceFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function preg_match;

class PregReplaceTest extends TestCase
{
    /** @var PregReplaceFilter */
    protected $filter;

    public function setUp(): void
    {
        $this->filter = new PregReplaceFilter();
    }

    public function testDetectsPcreUnicodeSupport()
    {
        $enabled = (bool) @preg_match('/\pL/u', 'a');
        $this->assertEquals($enabled, PregReplaceFilter::hasPcreUnicodeSupport());
    }

    public function testPassingPatternToConstructorSetsPattern()
    {
        $pattern = '#^controller/(?P<action>[a-z_-]+)#';
        $filter  = new PregReplaceFilter($pattern);
        $this->assertEquals($pattern, $filter->getPattern());
    }

    public function testPassingReplacementToConstructorSetsReplacement()
    {
        $replace = 'foo/bar';
        $filter  = new PregReplaceFilter(null, $replace);
        $this->assertEquals($replace, $filter->getReplacement());
    }

    public function testPatternIsNullByDefault()
    {
        $this->assertNull($this->filter->getPattern());
    }

    public function testPatternAccessorsWork()
    {
        $pattern = '#^controller/(?P<action>[a-z_-]+)#';
        $this->filter->setPattern($pattern);
        $this->assertEquals($pattern, $this->filter->getPattern());
    }

    public function testReplacementIsEmptyByDefault()
    {
        $replacement = $this->filter->getReplacement();
        $this->assertEmpty($replacement);
    }

    public function testReplacementAccessorsWork()
    {
        $replacement = 'foo/bar';
        $this->filter->setReplacement($replacement);
        $this->assertEquals($replacement, $this->filter->getReplacement());
    }

    public function testFilterPerformsRegexReplacement()
    {
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

        $string   = 'controller/action';
        $filtered = $filter($string);
        $this->assertNotEquals($string, $filtered);
        $this->assertEquals('foo/bar', $filtered);
    }

    public function testFilterPerformsRegexReplacementWithArray()
    {
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

        $input = [
            'controller/action',
            'This should stay the same',
        ];

        $filtered = $filter($input);
        $this->assertNotEquals($input, $filtered);
        $this->assertEquals([
            'foo/bar',
            'This should stay the same',
        ], $filtered);
    }

    public function testFilterThrowsExceptionWhenNoMatchPatternPresent()
    {
        $filter = $this->filter;
        $string = 'controller/action';
        $filter->setReplacement('foo/bar');
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('does not have a valid pattern set');
        $filtered = $filter($string);
    }

    public function testPassingPatternWithExecModifierRaisesException()
    {
        $filter = new PregReplaceFilter();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('"e" pattern modifier');
        $filter->setPattern('/foo/e');
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @return void
     */
    public function testReturnUnfiltered($input)
    {
        $filter = $this->filter;
        $filter->setPattern('#^controller/(?P<action>[a-z_-]+)#')->setReplacement('foo/bar');

        $this->assertEquals($input, $filter->filter($input));
    }
}
