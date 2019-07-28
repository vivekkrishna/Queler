<?php


// Include the SDK using the composer autoloader
require 'vendor/autoload.php';

$s3 = new Aws\S3\S3Client([
    'region' => 'us-west-2',
    'version' => 'latest',
    'credentials' => [
        'key' => $_SERVER['AWSIAMUSERACCESSKEY'],
        'secret' => $_SERVER['AWSIAMUSERSECRETKEY'],
    ]
]);

// Send a PutObject request and get the result object.
$key = 'testFile';

$result = $s3->putObject([
    'Bucket' => 'quelerusers',
    'Key' => $key,
    'Body' => 'this is the body!',
    //'SourceFile' => 'c:\samplefile.png' -- use this if you want to upload a file from a local location
]);

// Print the body of the result by indexing into the result object.
var_dump($result);

