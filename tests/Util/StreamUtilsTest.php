<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Tests\Util;

use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;
use CodeInc\DocumentCloud\Util\StreamUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
class StreamUtilsTest extends TestCase
{
    public const string TEST_FILE_PATH = __DIR__.'/../fixtures/file.txt';

    /**
     * @throws FileOpenException
     */
    public function testCreateStreamFromFile(): void
    {
        $stream = StreamUtils::createStreamFromFile(self::TEST_FILE_PATH);
        self::assertStringContainsString('Lorem ipsum dolor sit amet', $stream->getContents());
    }

    /**
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function testSaveStreamToFile(): void
    {
        $stream = StreamUtils::createStreamFromFile(self::TEST_FILE_PATH);
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        StreamUtils::saveStreamToFile($stream, $tempFile);
        self::assertFileExists($tempFile);
        self::assertFileEquals(self::TEST_FILE_PATH, $tempFile);
        unlink($tempFile);
    }
}