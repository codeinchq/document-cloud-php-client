<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Office2Pdf;

enum Office2PdfSupportedExtensions
{
    case txt;
    case rtf;
    case fodt;
    case doc;
    case docx;
    case odt;
    case xls;
    case xlsx;
    case ods;
    case ppt;
    case pptx;
    case odp;

    /**
     * Verifies if the extension is supported.
     *
     * @param string $extension The extension to verify.
     * @return bool
     */
    public static function hasExtension(string $extension): bool
    {
        foreach (self::cases() as $case) {
            if ($extension === $case->name) {
                return true;
            }
        }
    }
}
