<?php

namespace S3Proxy;
require '../vendor/autoload.php';
use Aws\Common\Aws;

class S3Proxy
{

    public $config = '';

    public function __construct($config){
        $this->config = $config;
    }


    /**
     * Check if the file exists in S3.
     * @return mixed
     */
    function checkExists($filename)
    {
        $file = $this->prefix . $filename;
        $s3 = Aws::factory($this->config)->get('s3');
        return $s3->doesObjectExist($this->bucket, $file);
    }

    /**
     * List the contents of the bucket/prefix and format the links for download.
     */
    function getList()
    {
        $s3 = Aws::factory($this->config)->get('s3');

        $bucket = $this->bucket;
        $prefix = $this->prefix;

        $iterator = $s3->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $prefix
        ));

        $results = array();
        foreach ($iterator as $object) {
            if ($object['Key'] != $this->prefix) {
                $file = substr($object['Key'], strlen($this->prefix));
                $results[] = $file;
            }
        }
        return $results;
    }

    function doDownload($filename)
    {
        if (self::checkExists($filename)) {
            $s3 = Aws::factory($this->config)->get('s3');

            $bucket = $this->bucket;
            $prefix = $this->prefix;
            $file = $prefix . $filename;
            
            $link = $s3->getObjectUrl($bucket, $file, '+5 minutes');

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($link);

        } else {
            header("HTTP/1.0 404 Not Found");
            echo "File " . $filename . " not found";
        }

    }
}