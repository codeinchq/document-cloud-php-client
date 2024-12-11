<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Pdf2Img;

enum Pdf2ImgOutputFormat
{
    case webp;
    case png;
    case jpeg;
}
