<?php
/*
Create client handler for connecting to the Hub.
Included in separate file to avoid parse errors in pre-5.3 PHP.
*/
try {
    $s3 = new Aws\S3\S3MultiRegionClient(
        array(
            'version' => '2006-03-01',
            'credentials' => array(
                'key' => $this->destination_info['awskey'],
                'secret' => $this->destination_info['secretkey'],
            ),
        )
    );
} catch (Exception $e) {
    $status = false;
}
try {
    $resp = $s3->getBucketLocation(
        array(
            'Bucket' => $this->destination_info['bucket'],
        )
    );
    $status = true;
} catch (Exception $e) {
    $status = false;
}