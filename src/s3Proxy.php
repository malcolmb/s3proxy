<?php
/**
 * Created by IntelliJ IDEA.
 * User: mboyanton
 * Date: 5/24/15
 * Time: 9:20 AM
 */


namespace S3Proxy;
require '../vendor/autoload.php';
use Aws\Common\Aws;

class S3Proxy
{

    public $bucket = '';
    public $prefix = '';
    public $download = '';
    public $filename = '';
    protected $aws = '';

    public function __construct()
    {

        //Load your credentials any way you want. I'm using a file outside of my webroot.
        $this->aws = Aws::factory('../../aws-credentials.php');

        //You can set this in the top of the class or use a simple return array() in a config file.
        $config = include '../tmp/s3-config.php';
        $this->bucket = $config['Bucket'];
        $this->prefix = $config['Prefix'];

        //Converting command line arguments to $_GET requests to make things easier.
        if (self::isCommandLineInterface()) {
            global $argv;
            foreach ($argv as $arg) {
                $e = explode("=", $arg);
                if (count($e) == 2) {
                    $_GET[$e[0]] = $e[1];
                } else {
                    $_GET[] = $e[0];
                }
            }
        }

        //If there is file= in the request, download the file, else list contents of bucket.
        if (isset($_GET['file'])) {
            $this->filename = $_GET['file'];
            $this->doDownload();
        } else {
            $this->getList();
        }

    }

    static function isCommandLineInterface()
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * Check if the file exists in S3.
     * @return mixed
     */
    function checkExists()
    {
        $file = $this->prefix . $this->filename;
        $s3 = $this->aws->get('s3');
        return $s3->doesObjectExist($this->bucket, $file);
    }

    /**
     * List the contents of the bucket/prefix and format the links for download.
     */
    function getList()
    {
        $s3 = $this->aws->get('s3');

        $bucket = $this->bucket;
        $prefix = $this->prefix;

        $iterator = $s3->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $prefix
        ));

        echo "<h3>List of available files for download:</h3>";
        foreach ($iterator as $object) {
            if ($object['Key'] != $this->prefix) {
                $dl = substr($object['Key'], strlen($this->prefix));
                echo "<a href='download.php?file=$dl'>$dl</a><br>";
            }
        }
    }

    function doDownload()
    {
        if (self::checkExists($this->filename)) {
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
            echo "File " . $this->filename . " not found";
        }

    }
}