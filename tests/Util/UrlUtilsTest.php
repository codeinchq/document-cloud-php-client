<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Tests\Util;

use CodeInc\DocumentCloud\Util\UrlUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
class UrlUtilsTest extends TestCase
{
    public function testGetEndpointUrl()
    {
        self::assertEquals(
            'https://example.com/endpoint',
            UrlUtils::getEndpointUrl('https://example.com', '/endpoint')
        );
        self::assertEquals(
            'https://example.com/endpoint',
            UrlUtils::getEndpointUrl('https://example.com/', '/endpoint')
        );
        self::assertEquals(
            'https://example.com/endpoint',
            UrlUtils::getEndpointUrl('https://example.com', 'endpoint')
        );
        self::assertEquals(
            'https://example.com/endpoint',
            UrlUtils::getEndpointUrl('https://example.com/', 'endpoint')
        );
    }
}