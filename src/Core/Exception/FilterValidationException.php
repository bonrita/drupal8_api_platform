<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Exception;

/**
 * Filter validation exception.
 */
final class FilterValidationException extends \Exception implements ExceptionInterface
{
    private $constraintViolationList;

    public function __construct(array $constraintViolationList, string $message = '', int $code = 0, \Exception $previous = null)
    {
        $this->constraintViolationList = $constraintViolationList;

        parent::__construct($message ?: $this->__toString(), $code, $previous);
    }

    public function __toString(): string
    {
        return implode("\n", $this->constraintViolationList);
    }
}
