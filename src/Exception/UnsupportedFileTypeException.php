<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Exception;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
class UnsupportedFileTypeException extends FileException
{
    public function __construct(string $filename)
    {
        parent::__construct("The file '$filename' is not supported");
    }
}