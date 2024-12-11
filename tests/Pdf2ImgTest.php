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
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Pdf2Img\ConvertOptions;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2Img;
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
    private const string TEST_PDF_PATH = __DIR__.'/assets/file.pdf';
    private const string TEST_PDF_RESULT_IMG = '/tmp/file.jpg';

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
     */
    public function testConvert(): void
    {
        $client = new Pdf2Img();
        $stream = $client->convert($client->streamFactory->createStreamFromFile(self::TEST_PDF_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $imageContent = (string)$stream;
        $this->assertStringContainsString('WEBP', $imageContent, "The image is not a WEBP");
    }

    /**
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function testConvertWithOptions(): void
    {
        $this->assertIsWritable(dirname(self::TEST_PDF_RESULT_IMG), "The result file is not writable");

        $client = new Pdf2Img();
        $stream = $client->convert(
            $client->streamFactory->createStreamFromFile(self::TEST_PDF_PATH),
            new ConvertOptions(
                format: 'jpeg',
                page: 1,
                density: 72,
                height: 300,
                width: 300,
                background: 'white',
                quality: 80,
            )
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, "The stream is not valid");

        $f = fopen(self::TEST_PDF_RESULT_IMG, 'w+');
        self::assertNotFalse($f, "The test file could not be opened");

        $r = stream_copy_to_stream($stream->detach(), $f);
        self::assertNotFalse($r, "The stream could not be copied to the test file");

        $this->assertFileExists(self::TEST_PDF_RESULT_IMG, "The result file does not exist");
        $this->assertStringContainsString(
            'JFIF',
            file_get_contents(self::TEST_PDF_RESULT_IMG),
            "The image is not valid"
        );

        unlink(self::TEST_PDF_RESULT_IMG);
    }
}