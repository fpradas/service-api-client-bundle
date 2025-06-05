<?php

declare(strict_types=1);

namespace Auto1\ServiceAPIClientBundle\Tests\Service\Request;

use Auto1\ServiceAPIRequest\ServiceRequestInterface;

class RequestWithTwoParamsStub implements ServiceRequestInterface
{
    /** @var int */
    private $firstParam;

    /** @var string */
    private $secondParam;

    public function __construct(int $firstParam, string $secondParam)
    {
        $this->firstParam = $firstParam;
        $this->secondParam = $secondParam;
    }

    public function getFirstParam(): int
    {
        return $this->firstParam;
    }

    public function getSecondParam(): string
    {
        return $this->secondParam;
    }
}