<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Watermarker;

use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;
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
 * Watermarker class for applying watermarks to images using the WATERMARKER API.
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
readonly class Watermarker
{
    private StreamFactoryInterface $streamFactory;
    private RequestFactoryInterface $requestFactory;

    /**
     * Watermarker constructor.
     *
     * @param Client|ClientInterface $client               The DocumentCloud client or a PSR-18 client.
     * @param string $apiUrl                               The base URL of the WATERMARKER API.
     * @param StreamFactoryInterface|null $streamFactory   The PSR-17 stream factory.
     * @param RequestFactoryInterface|null $requestFactory The PSR-17 request factory.
     */
    public function __construct(
        private Client|ClientInterface $client = new Client(),
        private string $apiUrl = 'https://eu-v1-0-2ichd5z4.ew.gateway.dev/watermarker/v1/',
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * Applies a watermark to an image using the WATERMARKER API.
     *
     * @param StreamInterface|resource|string $imageStream     The PDF content.
     * @param StreamInterface|resource|string $watermarkStream The watermark content.
     * @param ConvertOptions $options                          The convert options.
     * @return StreamInterface
     * @throws InvalidResponseException
     * @throws NetworkException
     */
    public function apply(
        mixed $imageStream,
        mixed $watermarkStream,
        ConvertOptions $options = new ConvertOptions()
    ): StreamInterface {
        try {
            // building the multipart stream
            $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
                ->addResource('image', $imageStream)
                ->addResource('watermark', $watermarkStream)
                ->addResource('size', (string)$options->size)
                ->addResource('position', $options->position->value)
                ->addResource('format', $options->format->value)
                ->addResource('quality', (string)$options->quality);

            if ($options->blur !== null) {
                $multipartStreamBuilder->addResource('blur', (string)$options->blur);
            }
            if ($options->opacity !== null) {
                $multipartStreamBuilder->addResource('opacity', (string)$options->opacity);
            }

            // sending the request
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", EndpointUrl::getEndpointUrl($this->apiUrl, '/apply'))
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException(
                message: "An error occurred while sending the request to the WATERMARKER API",
                previous: $e
            );
        }

        // checking the response
        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(
                message: "The WATERMARKER API returned an error {$response->getStatusCode()}: {$response->getBody()}",
            );
        }

        // returning the response
        return $response->getBody();
    }

    /**
     * Opens a local file and creates a stream from it.
     *
     * @param string $path     The path to the file.
     * @param string $openMode The mode used to open the file.
     * @return StreamInterface
     * @throws FileOpenException
     */
    public function createStreamFromFile(string $path, string $openMode = 'r'): StreamInterface
    {
        $f = fopen($path, $openMode);
        if ($f === false) {
            throw new FileOpenException("The file '$path' could not be opened");
        }

        return $this->streamFactory->createStreamFromResource($f);
    }

    /**
     * Saves a stream to a local file.
     *
     * @param StreamInterface $stream
     * @param string $path     The path to the file.
     * @param string $openMode The mode used to open the file.
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function saveStreamToFile(StreamInterface $stream, string $path, string $openMode = 'w'): void
    {
        $f = fopen($path, $openMode);
        if ($f === false) {
            throw new FileOpenException("The file '$path' could not be opened");
        }

        if (stream_copy_to_stream($stream->detach(), $f) === false) {
            throw new FileWriteException("The stream could not be copied to the file '$path'");
        }

        fclose($f);
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
                    EndpointUrl::getEndpointUrl($this->apiUrl, "/health")
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
