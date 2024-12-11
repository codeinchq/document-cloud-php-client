<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Pdf2Img;

use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Util\EndpointUrl;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Pdf2Img for converting PDF files to images.
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
readonly class Pdf2Img
{
    public StreamFactoryInterface $streamFactory;
    public RequestFactoryInterface $requestFactory;

    /**
     * Pdf2Img constructor.
     *
     * @param Client|ClientInterface $client               The DocumentCloud client or a PSR-18 client.
     * @param string $apiUrl                               The base URL of the PDF2IMG API.
     * @param StreamFactoryInterface|null $streamFactory   The PSR-17 stream factory.
     * @param RequestFactoryInterface|null $requestFactory The PSR-17 request factory.
     */
    public function __construct(
        private Client|ClientInterface $client = new Client(),
        private string $apiUrl = 'https://eu-v1-0-2ichd5z4.ew.gateway.dev/pdf2img/v1/',
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * @param StreamInterface|resource|string $stream
     * @param ConvertOptions $options
     * @return StreamInterface
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function convert(mixed $stream, ConvertOptions $options = new ConvertOptions()): StreamInterface
    {
        $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
            ->addResource(
                'file',
                $stream,
                [
                    'filename' => 'file.pdf',
                    'headers'  => ['Content-Type' => 'application/pdf']
                ]
            )
            ->addResource('format', $options->format->name)
            ->addResource('density', (string)$options->density)
            ->addResource('height', (string)$options->height)
            ->addResource('width', (string)$options->width)
            ->addResource('background', $options->background)
            ->addResource('quality', (string)$options->quality)
            ->addResource('page', (string)$options->page);

        try {
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", EndpointUrl::getEndpointUrl($this->apiUrl, '/convert'))
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException(
                message: "An error occurred while sending the request to the PDF2IMG API",
                previous: $e
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(
                message: "The PDF2IMG API returned an error {$response->getStatusCode()}: {$response->getBody()}",
            );
        }

        return $response->getBody();
    }

    /**
     * Health check to verify the service is running.
     *
     * @return bool Health check response, expected to be "ok".
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->client->sendRequest(
                $this->requestFactory->createRequest(
                    "GET",
                    EndpointUrl::getEndpointUrl($this->apiUrl, '/health')
                )
            );

            // The response status code should be 200
            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // The response body should be {"status":"up"}
            $responseBody = json_decode((string)$response->getBody(), true);
            return isset($responseBody['status']) && $responseBody['status'] === 'up';
        } catch (ClientExceptionInterface) {
            return false;
        }
    }
}
