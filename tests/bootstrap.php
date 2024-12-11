<?php
/*
 * Copyright (c) 2024 Code Inc. - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Visit <https://www.codeinc.co> for more information
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file
if (file_exists(__DIR__.'/../.env.test')) {
    Dotenv\Dotenv::createImmutable(__DIR__.'/..', '.env.test')->load();
}