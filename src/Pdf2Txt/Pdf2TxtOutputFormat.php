<?php

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Pdf2Txt;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
enum Pdf2TxtOutputFormat
{
    case text;
    case json;
}