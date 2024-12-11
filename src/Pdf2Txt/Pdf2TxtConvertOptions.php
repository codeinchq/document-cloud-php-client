<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Pdf2Txt;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
final readonly class Pdf2TxtConvertOptions
{
    /**
     * ConvertOptions constructor.
     *
     * @param int $firstPage              The first page to convert
     * @param int|null $lastPage          The last page to convert
     * @param string|null $password       The password to open the PDF file (if any)
     * @param bool $normalizeWhitespace   Normalize the whitespace
     * @param Pdf2TxtOutputFormat $format The output format
     */
    public function __construct(
        public int $firstPage = 1,
        public int|null $lastPage = null,
        public string|null $password = null,
        public bool $normalizeWhitespace = true,
        public Pdf2TxtOutputFormat $format = Pdf2TxtOutputFormat::text,
    ) {
    }
}