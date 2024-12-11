# PHP Client for Code Inc.'s Document Cloud

[![Code Inc.](https://img.shields.io/badge/Code%20Inc.-Document%20Cloud-blue)](https://www.codeinc.co)
[![PHPUnit](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml/badge.svg)](https://github.com/codeinchq/document-cloud-php-client/actions/workflows/phpunit.yml)
[![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/codeinchq/document-cloud-php-client?label=Version)](https://github.com/codeinchq/document-cloud-php-client/releases/latest)

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
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Office2Pdf\Office2Pdf;
use CodeInc\DocumentCloud\Office2Pdf\ConvertOptions;
use CodeInc\DocumentCloud\Office2Pdf\Format;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\UnsupportedFileTypeException;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;

$srcDocPath = '/path/to/local/file.docx';
$destPdfPath = '/path/to/local/file.pdf';
$convertOption = new ConvertOptions(
    firstPage: 2,
    lastPage: 3,
    format: Format::json
);

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$office2Pdf = new Office2Pdf($client);

try {
    // convert 
    $pdfStream = $office2Pdf->convert(
        StreamUtils::createStreamFromFile('/path/to/local/file.docx'), 
        $convertOption
    );
    
   // save the PDF
   StreamUtils::saveStreamToFile($pdfStream, '/path/to/local/file.pdf'); 
}
catch (UnsupportedFileTypeException|NetworkException|InvalidResponseException|FileOpenException|FileWriteException $e) {
    // handle exception
}
```

#### Validating the support of a file format:
```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Office2Pdf\Office2Pdf;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$office2Pdf = new Office2Pdf($client);

$office2Pdf->supports('a-file.docx'); // returns true
$office2Pdf->supports('a-file'); // returns true 
$office2Pdf->supports('a-file', false); // returns false (the second argument is the strict mode)
$office2Pdf->supports('a-file.pdf'); // returns false
``` 

### Pdf2Img API

This API allows you to convert PDF documents to images.

#### Base example:

```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2Img;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Util\StreamUtils;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$pdf2Img = new Pdf2Img($client);

try {
    // Open the PDF file
    $pdfStream = StreamUtils::createStreamFromFile('/path/to/local/file.pdf');
    
    // convert 
    $imageStream = $pdf2Img->convert($pdfStream, $convertOption);
    
    // display the image 
    header('Content-Type: image/webp');
    echo $imageStream->getContents();
}
catch (NetworkException|InvalidResponseException|FileOpenException $e) {
    // handle exception
}
```

#### With options:

```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2Img;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2ImgConvertOptions;
use CodeInc\DocumentCloud\Pdf2Img\Pdf2ImgOutputFormat;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$pdf2Img = new Pdf2Img($client);
    
try {
    // Open the PDF file
    $pdfStream = StreamUtils::createStreamFromFile('/path/to/local/file.pdf');
    
    // Convert the PDF to an image
    $convertOption = new Pdf2ImgConvertOptions(
        format: Pdf2ImgOutputFormat::jpeg,
        page: 3,
        density: 300,
        height: 800,
        width: 800,
        background: 'red',
        quality: 90,
    ); 
    $imageStream = $pdf2Img->convert($pdfStream, $convertOption);
    
    // saves the image to a file 
    StreamUtils::saveStreamToFile($imageStream, '/path/to/destination/file.jpg');
}
catch (NetworkException|InvalidResponseException|FileOpenException|FileWriteException $e) {
    // handle exception
}
```

### Pdf2Txt API

This API allows you to convert PDF documents to text. 

#### Extracting text from a local file:

```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2Txt;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$pdf2Txt = new Pdf2Txt($client);

try {
    // Open the PDF file
    $pdfStream = StreamUtils::createStreamFromFile('/path/to/local/file.pdf');
    
    // Extract the textual content
    $textStream = $pdf2Txt->extract($pdfStream);
    
    // Display the textual content
    echo $textStream->getContents();
}
catch (NetworkException|InvalidResponseException|FileOpenException|FileWriteException $e) {
    // handle exception
}
```

#### With additional options:

```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2Txt;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$pdf2Txt = new Pdf2Txt($client);

try {
    // Open the PDF file
    $pdfStream = StreamUtils::createStreamFromFile('/path/to/local/file.pdf');
    
    // Extract the textual content
    $convertOption = new ConvertOptions(
        firstPage: 2,
        lastPage: 3,
        format: Format::json
    );
    $jsonStream = $pdf2Txt->extract(
        $pdfStream,
        $convertOption
    );
    
   // Display the extracted text
   $decodedJson = json_decode($jsonStream->getContents(), true);
   var_dump($decodedJson); 
}
catch (NetworkException|InvalidResponseException|FileOpenException $e) {
    // handle exception
}
```

#### Saving the extracted text to a file:

```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Pdf2Txt\Pdf2Txt;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$pdf2Txt = new Pdf2Txt($client);

try {
    // Open the PDF file
    $pdfStream = StreamUtils::createStreamFromFile('/path/to/local/file.pdf');

    // Extract the textual content
    $textStream = $pdf2Txt->extract($pdfStream);
    
    // Save the textual content to a file
    StreamUtils::saveStreamToFile($textStream, '/path/to/local/file.txt');
}
catch (NetworkException|InvalidResponseException|FileOpenException|FileWriteException $e) {
    // handle exception
}
```

### Watermarker API

This API allows you to add a watermark to a PDF document. 

#### A simple scenario to apply a watermark to an image and display the result:
```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Watermarker\Watermarker;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$watermaker = new Watermarker($client);

try {
    // Open the image and the watermark
    $anImageStream = StreamUtils::createStreamFromFile('/path/to/local/image.png');
    $theWatermarkStream = StreamUtils::createStreamFromFile('/path/to/local/watermark.png');

    // Apply the watermark
    $watermarkedImageStream = $watermaker->apply($anImageStream, $theWatermarkStream);
    
    // Display the watermarked image
    header('Content-Type: image/png');
    echo $watermarkedImageStream->getContents();
}
catch (NetworkException|InvalidResponseException|FileOpenException $e) {
    // handle exception
}
```

#### A mire complex scenario to apply a watermark to an image with options and save the result to a file:
```php
use CodeInc\DocumentCloud\Client;
use CodeInc\DocumentCloud\Watermarker\Watermarker;
use CodeInc\DocumentCloud\Watermarker\WatermarkerConvertOptions;
use CodeInc\DocumentCloud\Watermarker\WatermarkPosition;
use CodeInc\DocumentCloud\Watermarker\WatermarkerOutputFormat;
use CodeInc\DocumentCloud\Util\StreamUtils;
use CodeInc\DocumentCloud\Exception\NetworkException;
use CodeInc\DocumentCloud\Exception\InvalidResponseException;
use CodeInc\DocumentCloud\Exception\FileOpenException;
use CodeInc\DocumentCloud\Exception\FileWriteException;

$client = new Client('your-api-key'); // If the key is not specified, the library will try to get it from the `DOCUMENT_CLOUD_API_KEY` environment variable.
$watermaker = new Watermarker($client);

try {
    // Open the image and the watermark
    $anImageStream = StreamUtils::createStreamFromFile('/path/to/local/image.png');
    $theWatermarkStream = StreamUtils::createStreamFromFile('/path/to/local/watermark.png');
    
    // Apply the watermark
    $convertOption = new WatermarkerConvertOptions(
        size: 50,
        position: WatermarkPosition::topRight,
        format: WatermarkerOutputFormat::jpg,
        quality: 80,
        blur: 3,
        opacity: 75
    );
    $watermarkedImageStream = $client->apply($anImageStream, $theWatermarkStream, $convertOption);
    
    // save the watermarked image
    StreamUtils::saveStreamToFile($watermarkedImageStream, '/path/to/local/file.jpg');
}
catch (NetworkException|InvalidResponseException|FileOpenException|FileWriteException $e) {
    // handle exception
}
```


## License

The library is published under the MIT license (see [`LICENSE`](LICENSE) file).