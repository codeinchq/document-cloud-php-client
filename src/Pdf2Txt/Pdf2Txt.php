<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Pdf2Txt;

use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Util\UrlUtils;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Pdf2Txt for the extraction of text from PDF files using the PDF2TEXT API.
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
readonly class Pdf2Txt
{
    public StreamFactoryInterface $streamFactory;
    public RequestFactoryInterface $requestFactory;

    /**
     * Pdf2TxtClient constructor.
     *
     * @param Client|ClientInterface $client               The DocumentCloud client or a PSR-18 client.
     * @param string $apiUrl                               The base URL of the PDF2TXT API.
     * @param StreamFactoryInterface|null $streamFactory   The PSR-17 stream factory.
     * @param RequestFactoryInterface|null $requestFactory The PSR-17 request factory.
     */
    public function __construct(
        private Client|ClientInterface $client = new Client(),
        private string $apiUrl = 'https://eu-v1-0-2ichd5z4.ew.gateway.dev/pdf2txt/v1/',
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory ??= $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * Converts a PDF to text using streams and the PDF2TEXT API.
     *
     * @param StreamInterface|resource|string $stream The PDF content.
     * @param Pdf2TxtConvertOptions $options          The convert options.
     * @return StreamInterface
     * @throws InvalidResponseException
     * @throws NetworkException
     */
    public function extract(mixed $stream, Pdf2TxtConvertOptions $options = new Pdf2TxtConvertOptions()): StreamInterface
    {
        try {
            // building the multipart stream
            $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
                ->addResource(
                    'file',
                    $stream,
                    [
                        'filename' => 'file.pdf',
                        'headers'  => ['Content-Type' => 'application/pdf']
                    ]
                )
                ->addResource('firstPage', (string)$options->firstPage)
                ->addResource('normalizeWhitespace', (string)$options->normalizeWhitespace)
                ->addResource('format', $options->format->name);

            if ($options->lastPage !== null) {
                $multipartStreamBuilder->addResource('lastPage', (string)$options->lastPage);
            }
            if ($options->password !== null) {
                $multipartStreamBuilder->addResource('password', (string)$options->password);
            }

            // sending the request
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", UrlUtils::getEndpointUrl($this->apiUrl, '/extract'))
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException(
                message: "An error occurred while sending the request to the PDF2TEXT API",
                previous: $e
            );
        }

        // checking the response
        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(
                message: "The PDF2TEXT API returned an error {$response->getStatusCode()}: {$response->getBody()}",
            );
        }

        // returning the response
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
                    UrlUtils::getEndpointUrl($this->apiUrl, "/health")
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
