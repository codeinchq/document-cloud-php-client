<?php

declare(strict_types=1);

namespace CodeInc\DocumentCloud\Watermarker;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
enum WatermarkPosition: string
{
    case center = 'center';

    case top = 'top';
    case topLeft = 'top-left';
    case topRight = 'top-right';

    case left = 'left';

    case right = 'right';

    case bottom = 'bottom';
    case bottomLeft = 'bottom-left';
    case bottomRight = 'bottom-right';
}