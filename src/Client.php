<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud;

use CodeInc\DocumentCloud\Exception\MissingApiKeyException;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Code Inc.'s DocumentCloud client.
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
readonly class Client implements ClientInterface
{
    /**
     * The API key.
     *
     * @var string
     */
    private string $apiKey;

    /**
     * The PSR-18 HTTP client.
     *
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * Client constructor.
     *
     * @param string|null $apiKey          The API key.
     * @param ClientInterface|null $client The HTTP client (optional, uses the PSR-18 discovery by default).
     * @throws MissingApiKeyException
     */
    public function __construct(
        ?string $apiKey = null,
        ?ClientInterface $client = null,
    ) {
        $apiKey = $apiKey ?? getenv('DOCUMENT_CLOUD_API_KEY') ?? null;
        if (!$apiKey) {
            throw new MissingApiKeyException();
        }

        $this->apiKey = (string)$apiKey;
        $this->client = $client ?? Psr18ClientDiscovery::find();
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $request->withHeader('X-Api-Key', $this->apiKey);

        return $this->client->sendRequest($request);
    }
}