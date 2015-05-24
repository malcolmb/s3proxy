<?php
/**
 * Created by IntelliJ IDEA.
 * User: mboyanton
 * Date: 5/23/15
 * Time: 11:37 AM
 */

ini_set('display_errors',1);
require '../vendor/autoload.php';
require_once 'auth.php';
use S3Proxy\S3Proxy;

$s3Proxy = new S3Proxy();