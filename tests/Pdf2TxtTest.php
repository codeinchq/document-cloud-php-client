<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Tests;

use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2TxtConvertOptions;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2TxtOutputFormat;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2Txt;
use CodeInc\DocumentCloud\Util\StreamUtils;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final class Pdf2TxtTest extends TestCase
{
    private const string TEST_PDF_PATH = __DIR__.'/fixtures/file.pdf';
    private const string TEST_PDF_RESULT_TXT = __DIR__.'/fixtures/file.txt';
    private const string TEST_PDF_RESULT_JSON = __DIR__.'/fixtures/file.json';

    public function testHealth(): void
    {
        $client = new Client();

        // testing a healthy service
        $pdf2TxtClient = new Pdf2Txt($client);
        $this->assertNotFalse($pdf2TxtClient->isHealthy(), "The service is not healthy.");

        // testing a non-existing service
        $pdf2TxtClient = new Pdf2Txt($client, 'https://example.com');
        $this->assertFalse($pdf2TxtClient->isHealthy(), "The service is healthy.");

        // testing a non-existing url
        $pdf2TxtClient = new Pdf2Txt($client, 'https://example-NQrkB6F6MwuXesMrBhqx.com');
        $this->assertFalse($pdf2TxtClient->isHealthy(), "The service is healthy.");
    }

    /**
     * @throws InvalidResponseException
     * @throws NetworkException
     * @throws FileOpenException
     */
    public function testExtractionFromLocalFileToText(): void
    {
        $client = new Pdf2Txt();

        $stream = $client->extract(StreamUtils::createStreamFromFile(self::TEST_PDF_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $text = (string)$stream;
        $this->assertNotEmpty($text, "The stream is empty");
        $this->assertStringEqualsFile(self::TEST_PDF_RESULT_TXT, $text, "The text is not valid");
    }

    /**
     * @throws InvalidResponseException
     * @throws NetworkException
     * @throws FileOpenException
     */
    public function testExtractionFromLocalFileToRawJson(): void
    {
        $client = new Pdf2Txt();

        $stream = $client->extract(
            StreamUtils::createStreamFromFile(self::TEST_PDF_PATH),
            new Pdf2TxtConvertOptions(format: Pdf2TxtOutputFormat::json)
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $rawJson = (string)$stream;
        $this->assertJson($rawJson, "The JSON is not valid");
        $this->assertStringEqualsFile(self::TEST_PDF_RESULT_JSON, $rawJson, "The JSON is not valid");
    }

    /**
     * @throws InvalidResponseException
     * @throws NetworkException
     * @throws FileOpenException
     */
    public function testExtractionFromLocalFileToProcessedJson(): void
    {
        $client = new Pdf2Txt();

        $stream = $client->extract(
            StreamUtils::createStreamFromFile(self::TEST_PDF_PATH),
            new Pdf2TxtConvertOptions(format: Pdf2TxtOutputFormat::json)
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $rawJson = $stream->getContents();
        self::assertJson($rawJson, "The JSON is not valid");
        $json = json_decode($rawJson, true);
        self::assertIsArray($json, "The processed JSON is not valid");

        $expectedJson = json_decode(file_get_contents(self::TEST_PDF_RESULT_JSON), true);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            $json,
            $expectedJson,
            ["meta", "pages"]
        );
    }
}