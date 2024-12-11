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
class MissingApiKeyException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'The API cannot be empty. Please provide a valid API key or define the DOCUMENT_CLOUD_API_KEY environment variable.'
        );
    }
}