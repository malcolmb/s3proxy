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
<style type="text/css">
    table {
        border: none;
        border-collapse: collapse;
        width: 100%;
    }
    table td {
        padding: 10px 0;
    }
</style>
<h3>Files for download</h3>

<table>
    <tr>
        <td>File</td>
        <td>Size</td>
        <td>Modified</td>
    </tr>
<?php
foreach($objects as $object){
    echo "<tr><td><a href='download.php/?file=" .$object['Name'] ."'>" .$object['Name'] ."</a></td>";
    echo "<td><span class='filesize'>" .$object['Size'] ."</span></td>";
    echo "<td><span class='filesize'>" .$object['LastModified'] ."</span></td>";
    echo "</tr>";
}
?>
</table>
