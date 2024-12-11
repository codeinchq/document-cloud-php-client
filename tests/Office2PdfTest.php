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
use CodeInc\DocumentCloud\Exception\UnsupportedFileTypeException;
use CodeInc\DocumentCloud\Office2Pdf\Office2Pdf;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final class Office2PdfTest extends TestCase
{
    private const string TEST_DOC_PATH = __DIR__.'/assets/file.docx';
    private const string TEST_TEMP_PATH = '/tmp/file.pdf';

    public function testHealth(): void
    {
        $client = new Client();

        // testing a healthy service
        $office2PdfClient = new Office2Pdf($client);
        $this->assertNotFalse($office2PdfClient->isHealthy(), "The service is not healthy.");

        // testing a non-existing service
        $office2PdfClient = new Office2Pdf($client, 'https://example.com');
        $this->assertFalse($office2PdfClient->isHealthy(), "The service is healthy.");

        // testing a non-existing url
        $office2PdfClient = new Office2Pdf($client, 'https://example-NQrkB6F6MwuXesMrBhqx.com');
        $this->assertFalse($office2PdfClient->isHealthy(), "The service is healthy.");
    }

    /**
     * Tests the method convert() with a DOCX file.
     *
     * @throws UnsupportedFileTypeException
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function testConvert(): void
    {
        $this->assertIsWritable(
            dirname(self::TEST_TEMP_PATH),
            "The directory ".dirname(self::TEST_TEMP_PATH)." is not writable."
        );

        $client = new Office2Pdf();
        $stream = $client->convert($client->streamFactory->createStreamFromFile(self::TEST_DOC_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The method convert() should return a stream.");

        $f = fopen(self::TEST_TEMP_PATH, 'w+');
        self::assertNotFalse($f, "The test file could not be opened");

        $r = stream_copy_to_stream($stream->detach(), $f);
        self::assertNotFalse($r, "The stream could not be copied to the test file");
        fclose($f);

        $this->assertFileExists(self::TEST_TEMP_PATH, "The converted file does not exist.");
        $this->assertStringContainsString(
            '%PDF-1.',
            file_get_contents(self::TEST_TEMP_PATH),
            "The file self::TEST_TEMP_PATH is not a PDF file."
        );

        unlink(self::TEST_TEMP_PATH);
    }

    /**
     * Tests a failure when trying to convert an unsupported file type.
     *
     * @throws UnsupportedFileTypeException
     * @throws NetworkException
     * @throws InvalidResponseException
     */
    public function testConvertUnsupportedFileType(): void
    {
        $client = new Office2Pdf();

        $this->expectException(UnsupportedFileTypeException::class);
        $client->convert("", "file.pdf");
    }

    /**
     * Tests the method supports().
     *
     * @return void
     */
    public function testSupport(): void
    {
        $client = new Office2Pdf();

        foreach (Office2Pdf::SUPPORTED_EXTENSIONS as $extension) {
            $this->assertTrue(
                $client->supports("file.$extension"),
                "The method supports() should return true for a file with the extension $extension."
            );
        }

        $this->assertTrue(
            $client->supports("filename_without_extension"),
            "The method supports() should return true for a file without extension."
        );

        $this->assertFalse(
            $client->supports('file.pdf'),
            "The method supports() should return false for a PDF file."
        );
    }
}