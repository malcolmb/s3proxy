<?php

ini_set('display_errors', 1);
require '../vendor/autoload.php';
require_once 'auth.php';
use S3Proxy\S3Proxy;

$credentials = '../../aws-credentials.php';
$config = include '../tmp/s3-config.php';

$s3Proxy = new S3Proxy($credentials);

$s3Proxy->bucket = $config['Bucket'];
$s3Proxy->prefix = $config['Prefix'];
$objects = $s3Proxy->getList();
?>
<h3>Files for download</h3>
<ul>
<?php
foreach($objects as $object){
    echo "<li><a href='download.php/?file=$object'>$object</a></li>";
}
?>