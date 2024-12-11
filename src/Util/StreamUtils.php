<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Util;

use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;
use Http\Discovery\Psr17FactoryDiscovery;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
class StreamUtils
{
    /**
     * Opens a local file and creates a stream from it.
     *
     * @param string $path                               The path to the file.
     * @param string $openMode                           The mode used to open the file.
     * @param StreamFactoryInterface|null $streamFactory The PSR-17 stream factory.
     * @return StreamInterface
     * @throws FileOpenException
     */
    public static function createStreamFromFile(
        string $path,
        string $openMode = 'r',
        ?StreamFactoryInterface $streamFactory = null
    ): StreamInterface {
        $streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();

        try {
            return $streamFactory->createStreamFromFile($path, $openMode);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            throw new FileOpenException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Saves a stream to a local file.
     *
     * @param StreamInterface $stream
     * @param string $path     The path to the file.
     * @param string $openMode The mode used to open the file.
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public static function saveStreamToFile(StreamInterface $stream, string $path, string $openMode = 'w'): void
    {
        $f = fopen($path, $openMode);
        if ($f === false) {
            throw new FileOpenException("The file '$path' could not be opened");
        }

        if (stream_copy_to_stream($stream->detach(), $f) === false) {
            throw new FileWriteException("The stream could not be copied to the file '$path'");
        }

        fclose($f);
    }
}