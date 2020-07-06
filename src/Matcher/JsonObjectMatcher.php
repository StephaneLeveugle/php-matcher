<?php

declare(strict_types=1);

namespace Coduo\PHPMatcher\Matcher;

use Coduo\PHPMatcher\Backtrace;
use Coduo\PHPMatcher\Matcher\Pattern\Assert\Json;
use Coduo\PHPMatcher\Parser;
use Coduo\ToString\StringConverter;
use function gettype;
use function is_array;
use function is_null;
use function is_string;
use function sprintf;

final class JsonObjectMatcher extends Matcher
{
    const JSON_PATTERN = 'json';

    /**
     * @var Backtrace
     */
    private $backtrace;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Backtrace $backtrace, Parser $parser)
    {
        $this->backtrace = $backtrace;
        $this->parser = $parser;
    }

    public function match($value, $pattern) : bool
    {
        if (!$this->isJsonPattern($pattern)) {
            $this->error = sprintf('%s "%s" is not a valid json.', gettype($value), new StringConverter($value));
            $this->backtrace->matcherFailed(self::class, $value, $pattern, $this->error);

            return false;
        }

        if (!Json::isValid($value) && !is_null($value) && !is_array($value)) {
            $this->error = sprintf('Invalid given JSON of value. %s', Json::getErrorMessage());
            $this->backtrace->matcherFailed(self::class, $value, $pattern, $this->error);

            return false;
        }

        if ($this->isJsonPattern($pattern)) {
            return $this->allExpandersMatch($value, $pattern);
        }

        $this->backtrace->matcherFailed(self::class, $value, $pattern, $this->error);

        return false;
    }

    public function canMatch($pattern) : bool
    {
        $result = is_string($pattern) && $this->isJsonPattern($pattern);
        $this->backtrace->matcherCanMatch(self::class, $pattern, $result);

        return $result;
    }

    private function isJsonPattern($pattern): bool
    {
        if (!is_string($pattern)) {
            return false;
        }

        return $this->parser->hasValidSyntax($pattern) && $this->parser->parse($pattern)->is(self::JSON_PATTERN);
    }

    private function allExpandersMatch($value, $pattern): bool
    {
        $typePattern = $this->parser->parse($pattern);

        if (!$typePattern->matchExpanders($value)) {
            $this->error = $typePattern->getError();
            $this->backtrace->matcherFailed(self::class, $value, $pattern, $this->error);
            return false;
        }

        $this->backtrace->matcherSucceed(self::class, $value, $pattern);

        return true;
    }
}
