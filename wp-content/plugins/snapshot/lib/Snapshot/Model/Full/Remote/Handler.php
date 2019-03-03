<?php // phpcs:ignore
/*
Create client handler for connecting to the Hub.
Included in separate file to avoid parse errors in pre-5.3 PHP.
*/
$ca = trailingslashit( ABSPATH . WPINC ) . 'certificates/ca-bundle.crt';
$s3_handler = new Aws\S3\S3Client(array(
    'version' => '2006-03-01',
    'region' => 'us-east-1',
    'credentials' => array(
        'key' => $nfo['AccessKeyId'],
        'secret' => $nfo['SecretAccessKey'],
        'token' => $nfo['SessionToken'],
    ),
    'http' => array(
        'verify' => $ca,
    ),
));