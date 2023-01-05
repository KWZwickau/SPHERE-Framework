<?php

declare(strict_types=1);

namespace OpenIDConnectClient\Validator;

use Webmozart\Assert\Assert;

final class GreaterOrEqualsTo implements ValidatorInterface
{
    use ValidatorTrait;

    public function isValid($expectedValue, $actualValue): bool
    {
        Assert::nullOrIntegerish($expectedValue);

        if($actualValue instanceof \DateTimeImmutable) {
            /** @var \DateTimeImmutable|string $actualValue*/
            $actualValueString = $actualValue->getTimestamp();
            Assert::nullOrIntegerish($actualValueString);
        } else {
            $actualValueString = $actualValue;
            Assert::nullOrIntegerish($actualValueString);
        }

        if ($actualValueString >= $expectedValue) {
            return true;
        }

        $this->message = sprintf('%s is invalid as it is not greater than %s', $actualValue, $expectedValue);

        return false;
    }
}
