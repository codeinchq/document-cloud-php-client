<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Pdf2Img;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final readonly class Pdf2ImgConvertOptions
{
    /**
     * ConvertOptions constructor.
     *
     * @param Pdf2ImgOutputFormat $format Supported formats: 'webp', 'png', 'jpeg'
     * @param int $page                   The page number to convert
     * @param int $density                The density of the image in DPI
     * @param int $height                 The height of the image in pixels
     * @param int $width                  The width of the image in pixels
     * @param string $background          The background color of the image
     * @param int $quality                The quality of the image (only for 'jpeg' format)
     */
    public function __construct(
        public Pdf2ImgOutputFormat $format = Pdf2ImgOutputFormat::webp,
        public int $page = 1,
        public int $density = 300,
        public int $height = 1000,
        public int $width = 1000,
        public string $background = 'white',
        public int $quality = 80,
    ) {
    }
}