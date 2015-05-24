<?php
/**
 * Created by IntelliJ IDEA.
 * User: mboyanton
 * Date: 5/24/15
 * Time: 9:20 AM
 */

namespace s3Proxy;


class s3Proxy {

    public $bucket = 'services.spredfast.com';
    public $prefix = 'test/';
    public $download = '';
    public $filename = '';
    protected $aws = '';

    public function __construct(){
        $this->aws = Aws::factory('../aws-credentials.php');

        if (self::isCommandLineInterface()) {
            global $argv;
            foreach ($argv as $arg) {
                $e = explode("=", $arg);
                if (count($e) == 2)
                    $_GET[$e[0]] = $e[1];
                else
                    $_GET[] = $e[0];
            }
        }

        $this->filename = $_GET['file'];

    }

    function isCommandLineInterface()
    {
        return (php_sapi_name() === 'cli');
    }

    function getRequest($argv)
    {
        if (self::isCommandLineInterface()) {
            $args = $argv;
            $params = explode('=', $args[1]);
            return $params[1];
        } elseif(isset($_GET)){
            return $_GET['file'];
        }

    }

    function checkExists(){
        $file = $this->prefix . $this->filename;
        $s3 = $this->aws->get('s3');
        return $s3->doesObjectExist($this->bucket, $file);

    }

    function getList(){
        $s3 = $this->aws->get('s3');

        $bucket = $this->bucket;
        $prefix = $this->prefix;

        $iterator = $s3->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $prefix
        ));

        foreach ($iterator as $object) {
            $link = $s3->getObjectUrl($bucket, $object['Key']);
        }
    }

    function doDownload()
    {
        if(self::checkExists($this->filename)) {
            $s3 = $this->aws->get('s3');

            $bucket = $this->bucket;
            $prefix = $this->prefix;
            $file = $prefix . $this->filename;

            $object = $s3->getObject(array('Bucket' => $bucket, 'Key' => $file));
            $link = $s3->getObjectUrl($bucket, $file, '+5 minutes');

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $this->filename);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $object['ContentLength']);
            readfile($link);

        } else {
            header("HTTP/1.0 404 Not Found");
        }

    }
}