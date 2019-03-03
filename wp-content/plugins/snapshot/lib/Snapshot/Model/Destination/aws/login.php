<?php
/*
Create client handler for connecting to the S3 destination.
Included in separate file to avoid parse errors in pre-5.3 PHP.
*/
try {
    $this->aws_connection = new Aws\S3\S3Client(
        array(
            'version' => '2006-03-01',
            'region' => $connection_region,
            'credentials' => array(
                'key' => $this->destination_info['awskey'],
                'secret' => $this->destination_info['secretkey'],
            ),
            'http' => array(
                'verify' => $use_ssl,
            ),
        )
    );
    $status = true;

} catch ( Exception $e ) {
    $this->error_array['errorStatus']  = true;
    $this->error_array['errorArray'][] = "Error: Could not connect to AWS :" . $e->getMessage();

    $status = false;
}