<?php
require_once('Connections/roo.php');
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
    session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
    $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['login'],$_POST['password'])) {
    $loginUsername = mysqli_real_escape_string($mysqli,$_POST['login']);
    $password = mysqli_real_escape_string($mysqli,$_POST['password']);
    $MM_fldUserAuthorization = "";
    $MM_redirectLoginSuccess = "form_main.php";
    $MM_redirectLoginFailed = "index.php";
    $MM_redirecttoReferrer = false;

    $saltQuery = "SELECT users.salt FROM users WHERE users.login='$loginUsername'";
    $result = $mysqli->query($saltQuery);
    $row = mysqli_fetch_assoc($result);
    $salt = $row['salt'];

    /*
    if($salt=='') // todo - при первом входе обновить пароли!
    {
        //generate a random salt to use for this account
        $salt = bin2hex(mcrypt_create_iv(16,MCRYPT_DEV_URANDOM));
        $saltedPW =  $password . $salt;
        $hashedPW = hash('sha256', $saltedPW);
        $query =
            "UPDATE users SET users.password='$hashedPW', users.salt = '$salt' WHERE users.login='$loginUsername'";
        $mysqli->query($query);
    }
    */

    $saltedPW =  $password . $salt;
    $hashedPW = hash('sha256', $saltedPW);
    $query = /** @lang SQL */
        "SELECT * FROM users WHERE users.login='$loginUsername' AND users.password='$hashedPW'";
    $res = $mysqli->query($query);

    if ($res->num_rows > 0) {

        $row = $res->fetch_assoc();

        $loginStrGroup = $row['rt'];
        $kod_user = $row['kod_user'];

        //declare two session variables and assign them
        $_SESSION['MM_Username'] = $loginUsername; // имя пользователя
        $_SESSION['MM_UserGroup'] = $loginStrGroup; // группа пользователя todo - доделать с правами
        $_SESSION['MM_kod_user'] = $kod_user; // код пользователя

        // Запись сесии
        $SessionSQL = sprintf("INSERT INTO sessions VALUES('','%s','%s','%s')", $loginUsername, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
        $mysqli->query($SessionSQL);
        $res->close();
        $mysqli->close();

        if (isset($_SESSION['PrevUrl']) && false) {
            $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];
        }
        header("Location: " . $MM_redirectLoginSuccess);
    } else {
        header("Location: " . $MM_redirectLoginFailed);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="menu/menu.css">
</head>
<?php
require_once('class_func.php');
?>

<body>
<form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>">
    <table width="253" border="0">
        <tr>
            <th width="92" scope="row"><span class="style1">Name</span></th>
            <td width="162"><label>
                    <input name="login" class="style1" id="login"/>
                </label></td>
        </tr>
        <tr>
            <th scope="row"><span class="style1">Pass</span></th>
            <td><label>
                    <input name="password" type="password" class="style1" id="password"/>
                </label></td>
        </tr>
        <tr>
            <th scope="row">&nbsp;</th>
            <td><label>
                    <input name="Submit" type="submit" class="style1" value="Вход"/>
                </label></td>
        </tr>

        </a></table>
</form>
</body>
</html>
