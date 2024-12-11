# PHP Client for Code Inc.'s Document Cloud

[![Code Inc.](https://img.shields.io/badge/Code%20Inc.-Docunemt%20Cloud-blue)](https://www.codeinc.co)
[![PHPUnit](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml/badge.svg)](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/codeinchq/document-cloud-php-client?label=Version)

> [!CAUTION]
> It is a work in progress and is not yet ready for production use.

## Installation

The library is available on [Packagist](https://packagist.org/packages/codeinc/document-cloud-client). The recommended way to install it is via Composer:

```bash
composer require codeinc/document-cloud-client
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

This API allows you to convert PDF documents to images.

#### Base example:
```php
use CodeInc\Pdf2ImgClient\Pdf2ImgClient;
use CodeInc\Pdf2ImgClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';

try {
    $client = new Pdf2ImgClient($apiBaseUri);

    // convert 
    $image = $client->convert(
        $client->createStreamFromFile($localPdfPath)
    );
    
    // display the image 
    header('Content-Type: image/webp');
    echo (string)$image;
}
catch (Exception $e) {
    // handle exception
}
```

#### With options:
```php
use CodeInc\Pdf2ImgClient\Pdf2ImgClient;
use CodeInc\Pdf2ImgClient\ConvertOptions;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';
$destinationPath = '/path/to/destination/file.jpg';
$convertOption = new ConvertOptions(
    format: 'jpg',
    page: 3,
    density: 300,
    height: 800,
    width: 800,
    background: 'red',
    quality: 90,
);

try {
    $client = new Pdf2ImgClient($apiBaseUri);

    // convert 
    $image = $client->convertLocalFile(
        $client->createStreamFromFile($localPdfPath),
        $convertOption
     );
    
    // saves the image to a file 
    $client->saveStreamToFile($image, $destinationPath);
}
catch (Exception $e) {
    // handle exception
}
```

### Pdf2Txt API

This API allows you to convert PDF documents to text. 

#### Extracting text from a local file:
```php
use CodeInc\Pdf2TxtClient\Pdf2TxtClient;
use CodeInc\Pdf2TxtClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';

try {
    // convert
    $client = new Pdf2TxtClient($apiBaseUri);
    $stream = $client->extract(
        $client->createStreamFromFile($localPdfPath)
    );
    
    // display the text
    echo (string)$stream;
}
catch (Exception $e) {
    // handle exception
}
```

#### With additional options:
```php
use CodeInc\Pdf2TxtClient\Pdf2TxtClient;
use CodeInc\Pdf2TxtClient\ConvertOptions;
use CodeInc\Pdf2TxtClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';
$convertOption = new ConvertOptions(
    firstPage: 2,
    lastPage: 3,
    format: Format::json
);

try {
    $client = new Pdf2TxtClient($apiBaseUri);

    // convert 
    $jsonResponse = $client->extract(
        $client->createStreamFromFile($localPdfPath),
        $convertOption
    );
    
   // display the text in a JSON format
   $decodedJson = $client->processJsonResponse($jsonResponse);
   var_dump($decodedJson); 
}
catch (Exception $e) {
    // handle exception
}
```

#### Saving the extracted text to a file:
```php
use CodeInc\Pdf2TxtClient\Pdf2TxtClient;
use CodeInc\Pdf2TxtClient\ConvertOptions;
use CodeInc\Pdf2TxtClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$localPdfPath = '/path/to/local/file.pdf';
destinationTextPath = '/path/to/local/file.txt';

try {
    $client = new Pdf2TxtClient($apiBaseUri);

    // convert
    $stream = $client->extract(
        $client->createStreamFromFile($localPdfPath)
    );
    
    // save the text to a file
    $client->saveStreamToFile($stream, $destinationTextPath);
}
catch (Exception $e) {
    // handle exception
}
```

### Watermarker API

This API allows you to add a watermark to a PDF document. 

#### A simple scenario to apply a watermark to an image and display the result:
```php
use CodeInc\WatermarkerClient\WatermarkerClient;
use CodeInc\WatermarkerClient\Exception;

$apiBaseUri = 'http://localhost:3000/';
$anImage = '/path/to/local/image.png';
$theWatermark = '/path/to/local/watermark.png';

try {
    $client = new WatermarkerClient($apiBaseUri);

    // apply the watermark
    $watermarkedImageStream = $client->apply(
        $client->createStreamFromFile($anImage),
        $client->createStreamFromFile($theWatermark),
    );
    
    // display the watermarked image
    header('Content-Type: image/png');
    echo (string)$watermarkedImageStream;
}
catch (Exception $e) {
    // handle exception
}
```

#### A mire complex scenario to apply a watermark to an image with options and save the result to a file:
```php
use CodeInc\WatermarkerClient\WatermarkerClient;
use CodeInc\WatermarkerClient\ConvertOptions;
use CodeInc\WatermarkerClient\Position;
use CodeInc\WatermarkerClient\Format;

$apiBaseUri = 'http://localhost:3000/';
$theImageStream = '/path/to/local/image.png';
$theWatermarkStream = '/path/to/local/watermark.png';
$theDestinationFile = '/path/to/local/destination.png';
$convertOption = new ConvertOptions(
    size: 50,
    position: Position::topRight,
    format: Format::jpg,
    quality: 80,
    blur: 3,
    opacity: 75
);

try {
    $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    $client = new WatermarkerClient($apiBaseUri);

    // apply the watermark
    $watermarkedImageStream = $client->apply(
        $client->createStreamFromFile($theImageStream),
        $client->createStreamFromFile($theWatermarkStream),
        $convertOption
    );
    
    // save the watermarked image
    $client->saveStreamToFile($watermarkedImageStream, $theDestinationFile);
}
catch (Exception $e) {
    // handle exception
}
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