<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Office2Pdf;

use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\UnsupportedFileTypeException;
use CodeInc\DocumentCloud\Util\UrlUtils;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Office2Pdf for converting Office files to PDF.
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @link    https://github.com/codeinchq/office2pdf
 * @license https://opensource.org/licenses/MIT MIT
 */
readonly class Office2Pdf
{
    public StreamFactoryInterface $streamFactory;
    public RequestFactoryInterface $requestFactory;

    /**
     * Office2PdfClient constructor.
     *
     * @param Client|ClientInterface $client               The DocumentCloud client or a PSR-18 client.
     * @param string $apiUrl                               The base URL of the OFFICE2PDF API.
     * @param StreamFactoryInterface|null $streamFactory   The PSR-17 stream factory.
     * @param RequestFactoryInterface|null $requestFactory The PSR-17 request factory.
     */
    public function __construct(
        private Client|ClientInterface $client = new Client(),
        private string $apiUrl = 'https://eu-v1-0-2ichd5z4.ew.gateway.dev/office2pdf/v1/',
        StreamFactoryInterface|null $streamFactory = null,
        RequestFactoryInterface|null $requestFactory = null,
    ) {
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
    }

    /**
     * Converts an Office file to PDF using the OFFICE2PDF API.
     *
     * @param StreamInterface|resource|string $stream The PDF content as a stream, a resource or a string.
     * @param string $filename                        The filename associated with the stream (optional).
     * @param bool $skipTypeCheck                     If enabled, the method will not check if the file extension is
     *                                                supported.
     * @return StreamInterface The PDF content as a stream.
     * @throws UnsupportedFileTypeException
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function convert(mixed $stream, string $filename = 'file', bool $skipTypeCheck = false): StreamInterface
    {
        // checking the file extension
        if (!$this->supports($filename) && !$skipTypeCheck) {
            throw new UnsupportedFileTypeException($filename);
        }

        // building the multipart stream
        $multipartStreamBuilder = (new MultipartStreamBuilder($this->streamFactory))
            ->addResource(
                'file',
                $stream,
                [
                    'filename' => $filename,
                    'headers'  => ['Content-Type' => 'application/pdf']
                ]
            );

        // sending the request
        try {
            $response = $this->client->sendRequest(
                $this->requestFactory
                    ->createRequest("POST", UrlUtils::getEndpointUrl($this->apiUrl, "/convert"))
                    ->withHeader(
                        "Content-Type",
                        "multipart/form-data; boundary={$multipartStreamBuilder->getBoundary()}"
                    )
                    ->withBody($multipartStreamBuilder->build())
            );
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException(
                message: "An error occurred while sending the request to the OFFICE2PDF API",
                previous: $e
            );
        }

        // checking the response
        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(
                message: "The OFFICE2PDF API returned an error {$response->getStatusCode()}: {$response->getBody()}"
            );
        }

        // returning the response
        return $response->getBody();
    }

    /**
     * Verifies if the client supports a file.
     *
     * @param string $filename The filename.
     * @param bool $strictMode If enabled, the method will return true for files without extension.
     * @return bool
     */
    public function supports(string $filename, bool $strictMode = false): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension) {
            return Office2PdfSupportedExtensions::hasExtension($extension);
        }
        return !$strictMode;
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