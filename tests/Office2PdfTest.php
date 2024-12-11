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
use CodeInc\DocumentCloud\Exception\UnsupportedFileTypeException;
use CodeInc\DocumentCloud\Office2Pdf\Office2Pdf;
use CodeInc\DocumentCloud\Office2Pdf\Office2PdfSupportedExtensions;
use CodeInc\DocumentCloud\Util\StreamUtils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final class Office2PdfTest extends TestCase
{
    private const string TEST_DOC_PATH = __DIR__.'/fixtures/file.docx';

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
     * @throws InvalidResponseException
     * @throws NetworkException
     * @throws UnsupportedFileTypeException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function testConvert(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');

        $this->assertIsWritable(
            dirname($tempFile),
            "The directory ".dirname($tempFile)." is not writable."
        );

        $client = new Office2Pdf();
        $stream = $client->convert(StreamUtils::createStreamFromFile(self::TEST_DOC_PATH));
        $this->assertInstanceOf(StreamInterface::class, $stream, "The method convert() should return a stream.");

        StreamUtils::saveStreamToFile($stream, $tempFile);
        $this->assertFileExists($tempFile, "The converted file does not exist.");
        $this->assertStringContainsString(
            '%PDF-1.',
            file_get_contents($tempFile),
            "The file self::TEST_TEMP_PATH is not a PDF file."
        );

        unlink($tempFile);
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

        foreach (Office2PdfSupportedExtensions::cases() as $extension) {
            $this->assertTrue(
                $client->supports("file.$extension->name"),
                "The method supports() should return true for a file with the extension $extension->name."
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