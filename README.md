# PHP Client for Code Inc.'s Document Cloud

[![Code Inc.](https://img.shields.io/badge/Code%20Inc.-Docunemt%20Cloud-blue)](https://www.codeinc.co)
[![PHPUnit](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml/badge.svg)](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/codeinchq/document-cloud-php-client?label=Version)

> [!CAUTION]
> It is a work in progress and is not yet ready for production use.

## Installation

The library is available on [Packagist](https://packagist.org/packages/codeinc/services-cloud-client). The recommended way to install it is via Composer:

```bash
composer require codeinc/services-cloud-client
```

## Available APIs

### Office2Pdf API

This API allows you to convert office documents to PDF.

```php
use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\ConvertOptions;
use CodeInc\Office2PdfClient\Format;

$srcDocPath = '/path/to/local/file.docx';
$destPdfPath = '/path/to/local/file.pdf';
$convertOption = new ConvertOptions(
    firstPage: 2,
    lastPage: 3,
    format: Format::json
);

$client = new Office2PdfClient('http://localhost:3000/');

try {
    $client = new Office2PdfClient($apiBaseUri);

    // convert 
    $pdfStream = $client->convert(
        $client->createStreamFromFile($srcDocPath), 
        $convertOption
    );
    
   // save the PDF
   $client->saveStreamToFile($pdfStream, $destPdfPath); 
}
catch (Exception $e) {
    // handle exception
}
```

#### Validating the support of a file format:
```php

use CodeInc\Office2PdfClient\Office2PdfClient;
use CodeInc\Office2PdfClient\Exception;

$filename = 'a-file.docx';

$client = new Office2PdfClient('http://localhost:3000/');

$client->isSupported("a-file.docx"); // returns true
$client->isSupported("a-file"); // returns true 
$client->isSupported("a-file", false); // returns false (the second argument is the strict mode)
$client->isSupported("a-file.pdf"); // returns false
``` 

### Pdf2Img API

This API allows you to convert PDF documents to images. For more information see [this documentation](https://github.com/codeinchq/pdf2img-php-client?tab=readme-ov-file#usage).

The Pdf2Img client can be accessed using:

```php
use CodeInc\ServicesCloud\Client;

// Create a new client
$servicesCloudClient = new Client('my api key');

// Convert a stream using the Pdf2Img API
$response = $servicesCloudClient->pdf2Img()->convert(/* a PDF stream */);
```

### Pdf2Txt API

This API allows you to convert PDF documents to text. For more information see [this documentation](https://github.com/codeinchq/pdf2txt-php-client?tab=readme-ov-file#usage).

The Pdf2Txt client can be accessed using:

```php
use CodeInc\ServicesCloud\Client;

// Create a new client
$servicesCloudClient = new Client('my api key');

// Extract text using the Pdf2Txt API
$response = $servicesCloudClient->pdf2Txt()->extract(/* a PDF stream */);
```

### Watermarker API

This API allows you to add a watermark to a PDF document. For more information see [this documentation](https://github.com/codeinchq/watermarker-php-client?tab=readme-ov-file#usage).

The Watermarker client can be accessed using:

```php
use CodeInc\ServicesCloud\Client;

// Create a new client
$servicesCloudClient = new Client('my api key');

// Apply a watermark using the Watermarker API
$response = $servicesCloudClient->watermarker()->apply(
    /* an image stream*/, 
    /* a PDF stream */
);
```

### Gotenberg API (legacy)

> [!WARNING]  
> By default API keys are not authorized to access the Gotenberg API. If you need access to the Gotenberg API, please contact Code Inc. to request authorization.

The legacy Gotenberg v8 API can be accessed using the ServicesCloud client as the Gotenberg HTTP client.

Here is an example implementation:

```php
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use CodeInc\ServicesCloud\Client;

$response = Gotenberg::send(
    Gotenberg::libreOffice('https://gotenberg-v8-eu-byzteify.ew.gateway.dev')->convert(/* an Office stream */),
    new Client('my api key')
);
```

## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).