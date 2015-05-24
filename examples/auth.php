<?php
/**
 * Created by IntelliJ IDEA.
 * User: mboyanton
 * Date: 5/23/15
 * Time: 11:37 AM
 */
session_start();

$username = 'dude';
$password = md5('dude');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (md5($_POST['password']) === $password && $_POST['username'] === $username) {
        $_SESSION['Authorized'] = 'true';
    }
}

if(!$_SESSION['Authorized'] == 'true'):
    header('HTTP/1.0 401 Unauthorized');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Login for downloads</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<form action="/auth.php" method="post">
    <label for="username">Username</label>
    <input type="text" name="username" id="username"/>
    <label for="password">Password</label>
    <input type="password" name="password" id="password"/>
    <input type="submit" value="submit"/>
</form>
</body>
</html>
<?php exit; endif; ?>
