<?php

declare(strict_types=1);

namespace Coduo\PHPMatcher\Tests\Matcher\Pattern\Expander;

use Coduo\PHPMatcher\Backtrace;
use Coduo\PHPMatcher\Matcher\Pattern\Expander\EndsWith;
use PHPUnit\Framework\TestCase;
use DateTime;

class EndsWithTest extends TestCase
{
    /**
     * @dataProvider notIgnoringCaseExamplesProvider
     */
    public function test_examples_not_ignoring_case($stringEnding, $value, $expectedResult)
    {
        $expander = new EndsWith($stringEnding);
        $expander->setBacktrace(new Backtrace());
        $this->assertEquals($expectedResult, $expander->match($value));
    }

    public static function notIgnoringCaseExamplesProvider()
    {
        return [
            ['ipsum', 'lorem ipsum', true],
            ['ipsum', 'Lorem IPSUM', false],
            ['', 'lorem ipsum', true],
            ['ipsum', 'lorem ipsum', true],
            ['lorem', 'lorem ipsum', false]
        ];
    }

    /**
     * @dataProvider ignoringCaseExamplesProvider
     */
    public function test_examples_ignoring_case($stringEnding, $value, $expectedResult)
    {
        $expander = new EndsWith($stringEnding, true);
        $expander->setBacktrace(new Backtrace());
        $this->assertEquals($expectedResult, $expander->match($value));
    }

    public static function ignoringCaseExamplesProvider()
    {
        return [
            ['Ipsum', 'Lorem ipsum', true],
            ['iPsUm', 'lorem ipsum', true],
            ['IPSUM', 'LoReM ipsum', true],
        ];
    }

    /**
     * @dataProvider invalidCasesProvider
     */
    public function test_error_when_matching_fail($stringBeginning, $value, $errorMessage)
    {
        $expander = new EndsWith($stringBeginning);
        $expander->setBacktrace(new Backtrace());
        $this->assertFalse($expander->match($value));
        $this->assertEquals($errorMessage, $expander->getError());
    }

    public static function invalidCasesProvider()
    {
        return [
            ['ipsum', 'ipsum lorem', "string \"ipsum lorem\" doesn't ends with string \"ipsum\"."],
            ['lorem', new DateTime(), 'EndsWith expander require "string", got "\\DateTime".'],
        ];
    }
}
