<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

namespace CodeInc\DocumentCloud\Util;

/**
 * @author  Joan Fabrégat <joan@codeinc.co>
 * @license https://opensource.org/licenses/MIT MIT
 */
class UrlUtils
{
    /**
     * Returns the URL of an endpoint.
     *
     * @param string $baseUrl
     * @param string $endpoint
     * @return string
     */
    public static function getEndpointUrl(string $baseUrl, string $endpoint): string
    {
        if (str_ends_with($baseUrl, '/')) {
            $baseUrl = substr($baseUrl, 0, -1);
        }
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }
        return "$baseUrl/$endpoint";
    }
}