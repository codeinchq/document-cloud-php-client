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
use CodeInc\DocumentCloud\Watermarker\ConvertOptions;
use CodeInc\DocumentCloud\Watermarker\WatermarkerOutputFormat;
use CodeInc\DocumentCloud\Watermarker\WatermarkPosition;
use CodeInc\DocumentCloud\Watermarker\Watermarker;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final class WatermarkerTest extends TestCase
{
    private const string TEST_IMG_PATH = __DIR__.'/assets/doc.png';
    private const string TEST_WATERMARK_PATH = __DIR__.'/assets/watermark.png';
    private const string TEST_RESULT_PATH = '/tmp/watermarked.jpg';

    /**
     * @return void
     */
    public function testHealth(): void
    {
        $documentCloudClient = new Client();

        // testing a healthy service
        $watermarkerClient = new Watermarker($documentCloudClient);
        $this->assertNotFalse($watermarkerClient->isHealthy(), "The service is not healthy.");

        // testing a non-existing service
        $watermarkerClient = new Watermarker($documentCloudClient, 'https://example.com');
        $this->assertFalse($watermarkerClient->isHealthy(), "The service is healthy.");

        // testing a non-existing url
        $watermarkerClient = new Watermarker($documentCloudClient, 'https://example-NQrkB6F6MwuXesMrBhqx.com');
        $this->assertFalse($watermarkerClient->isHealthy(), "The service is healthy.");
    }

    /**
     * @throws FileOpenException
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function testWatermarkWithoutOptions(): void
    {
        $client = new Watermarker();
        $stream = $client->apply(
            $client->createStreamFromFile(self::TEST_IMG_PATH),
            $client->createStreamFromFile(self::TEST_WATERMARK_PATH)
        );
        $this->assertInstanceOf(StreamInterface::class, $stream, 'The returned value is not a stream');
        $imageContent = (string)$stream;
        $this->assertStringContainsString('PNG', $imageContent, 'The image is not a PNG');
    }

    /**
     * @throws FileOpenException
     * @throws FileWriteException
     * @throws InvalidResponseException
     * @throws NetworkException
     */
    public function testWatermarkWithOptions(): void
    {
        $this->assertIsWritable(dirname(self::TEST_RESULT_PATH), 'The result file is not writable');

        $client = new Watermarker();

        $stream = $client->apply(
            $client->createStreamFromFile(self::TEST_IMG_PATH),
            $client->createStreamFromFile(self::TEST_WATERMARK_PATH),
            new ConvertOptions(
                size: 50,
                position: WatermarkPosition::topLeft,
                format: WatermarkerOutputFormat::jpg,
                quality: 100,
                blur: 3,
                opacity: 30
            )
        );

        $this->assertInstanceOf(StreamInterface::class, $stream, 'The returned value is not a stream');

        $client->saveStreamToFile($stream, self::TEST_RESULT_PATH);
        $this->assertFileExists(self::TEST_RESULT_PATH, 'The result file does not exist');
        $this->assertStringContainsString('JFIF', file_get_contents(self::TEST_RESULT_PATH), 'The image is not a JPEG');

        unlink(self::TEST_RESULT_PATH);
    }
}