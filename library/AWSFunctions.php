<?php

use Aws\S3\S3Client;

function uploadToS3($tempSourcePath, $fileName, $userId, $S3Bucket)
{
    $s3 = new Aws\S3\S3Client([
        'region' => 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key' => $_SERVER['AWSIAMUSERACCESSKEY'],
            'secret' => $_SERVER['AWSIAMUSERSECRETKEY'],
        ]
    ]);

    $test = $s3->putObject([
        'Bucket' => $S3Bucket,
        'Key' => $userId . DS . $fileName,
        'SourceFile' => $tempSourcePath
    ]);

    var_dump($test);

    return $test;
}