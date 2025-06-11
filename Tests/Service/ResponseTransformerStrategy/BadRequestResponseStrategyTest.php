<?php
/*
* This file is part of the auto1-oss/service-api-client-bundle.
*
* (c) AUTO1 Group SE https://www.auto1-group.com
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
declare(strict_types=1);

namespace Auto1\ServiceAPIClientBundle\Tests\Service\ResponseTransformerStrategy;

use Auto1\ServiceAPIClientBundle\DTO\ErrorResponse;
use Auto1\ServiceAPIClientBundle\Exception\Response\MalformedResponseException;
use Auto1\ServiceAPIClientBundle\Exception\Response\BadRequestException;
use Auto1\ServiceAPIClientBundle\Service\DeserializerInterface;
use Auto1\ServiceAPIClientBundle\Service\ResponseTransformerStrategy\BadRequestResponseStrategy;
use Auto1\ServiceAPIComponentsBundle\Service\Endpoint\EndpointInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class BadRequestResponseStrategyTest extends TestCase
{
    /**
     * @var DeserializerInterface&MockObject
     */
    private $deserializer;

    /**
     * @var LoggerInterface&MockObject
     */
    private $logger;

    /**
     * @var BadRequestResponseStrategy
     */
    private $strategy;

    /**
     * @param string|null $responseClass
     * @param string      $method
     * @param string      $baseUrl
     * @param string      $path
     * @return EndpointInterface|(EndpointInterface&object&MockObject)|(EndpointInterface&MockObject)|(object&MockObject)|MockObject
     */
    public function buildEndpoint(?string $responseClass, string $method, string $baseUrl, string $path)
    {
        $endpoint = $this->createMock(EndpointInterface::class);
        $endpoint
            ->method('getResponseClass')
            ->willReturn($responseClass);
        $endpoint
            ->method('getMethod')
            ->willReturn($method);
        $endpoint
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $endpoint
            ->method('getPath')
            ->willReturn($path);

        return $endpoint;
    }

    protected function setUp(): void
    {
        $this->deserializer = $this->createMock(DeserializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->strategy = new BadRequestResponseStrategy($this->deserializer);
        $this->strategy->setLogger($this->logger);
    }

    public function testSupportsBadRequestResponses(): void
    {
        $this->assertTrue(
            $this->strategy->supports(
                $this->createResponseWithStatus(400)
            )
        );
    }

    /**
     * @dataProvider unsupportedResponses
     */
    public function testDoesntSupportOtherResponses(ResponseInterface $unsupportedResponse): void
    {
        $this->assertFalse(
            $this->strategy->supports($unsupportedResponse)
        );
    }

    public function unsupportedResponses(): array
    {
        return [
            [$this->createResponseWithStatus(100)],
            [$this->createResponseWithStatus(200)],
            [$this->createResponseWithStatus(300)],
            [$this->createResponseWithStatus(404)],
            [$this->createResponseWithStatus(403)],
            [$this->createResponseWithStatus(405)],
            [$this->createResponseWithStatus(500)],
        ];
    }

    /**
     * @dataProvider possibleValidScenarios
     */
    public function testHandlingResponses(string $responseBody, string $method, string $baseUrl, string $path, string $expectedMessage): void
    {
        $endpoint = $this->buildEndpoint("SomeFQDN", $method, $baseUrl, $path);

        $this->deserializer
            ->expects('' === $responseBody ? self::never() : self::once())
            ->method('deserialize')
            ->with($endpoint, ErrorResponse::class, $responseBody)
            ->willReturn(new ErrorResponse());

        $this->logger
            ->expects(self::once())
            ->method('debug')
            ->with($expectedMessage);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->strategy->handle($endpoint, $this->createResponseWithStatus(404), $responseBody);
    }

    public function possibleValidScenarios(): array
    {
        return [
            ['{"error":"error explanation"}', 'GET', 'https://baseUrl', '/v1/path', 'Bad request for GET https://baseUrl/v1/path'],
            ['', 'GET', 'https://baseUrl', '/v1/path', 'Bad request for GET https://baseUrl/v1/path'],
        ];
    }

    public function testHandlingFailsOnDeserializationFailure(): void
    {
        $this->deserializer
            ->expects(self::once())
            ->method('deserialize')
            ->willThrowException($expectedException = new MalformedResponseException());

        $this->expectExceptionObject($expectedException);

        $this->strategy->handle(
            $this->createMock(EndpointInterface::class),
            $this->createMock(ResponseInterface::class),
            'not a json'
        );
    }

    private function createResponseWithStatus(int $statusCode): ResponseInterface
    {
        $mock = $this->createMock(ResponseInterface::class);
        $mock
            ->method('getStatusCode')
            ->willReturn($statusCode);

        return $mock;
    }
}
