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
use CodeInc\DocumentCloud\Exception\FileWriteException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2ImgConvertOptions;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2Img;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2ImgOutputFormat;
use CodeInc\DocumentCloud\Util\StreamUtils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class Pdf2ImgClientTest
 *
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final class Pdf2ImgTest extends TestCase
{
    private const string TEST_PDF_PATH = __DIR__.'/fixtures/file.pdf';

    public function testHealth(): void
    {
        $client = new Client();

        // testing a healthy service
        $pdf2ImgClient = new Pdf2Img($client);
        $this->assertNotFalse($pdf2ImgClient->isHealthy(), "The service is not healthy.");

        // testing a non-existing service
        $pdf2ImgClient = new Pdf2Img($client, 'https://example.com');
        $this->assertFalse($pdf2ImgClient->isHealthy(), "The service is healthy.");

        // testing a non-existing url
        $pdf2ImgClient = new Pdf2Img($client, 'https://example-NQrkB6F6MwuXesMrBhqx.com');
        $this->assertFalse($pdf2ImgClient->isHealthy(), "The service is healthy.");
    }

    /**
     * @throws NetworkException
     * @throws InvalidResponseException
     * @throws FileOpenException
     */
    public function testConvert(): void
    {
        $client = new Pdf2Img();
        $stream = $client->convert(StreamUtils::createStreamFromFile(self::TEST_PDF_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $imageContent = (string)$stream;
        $this->assertStringContainsString('WEBP', $imageContent, "The image is not a WEBP");
    }

    /**
     * @throws NetworkException
     * @throws InvalidResponseException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function testConvertWithOptions(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');

        $client = new Pdf2Img();
        $stream = $client->convert(
            StreamUtils::createStreamFromFile(self::TEST_PDF_PATH),
            new Pdf2ImgConvertOptions(
                format: Pdf2ImgOutputFormat::jpeg,
                page: 1,
                density: 72,
                height: 300,
                width: 300,
                background: 'white',
                quality: 80,
            )
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        StreamUtils::saveStreamToFile($stream, $tempFile);
        $this->assertFileExists($tempFile, "The result file does not exist");
        $this->assertStringContainsString(
            'JFIF',
            file_get_contents($tempFile),
            "The image is not valid"
        );

        unlink($tempFile);
    }
}