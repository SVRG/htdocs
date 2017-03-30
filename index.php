<?php require_once('Connections/roo.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
    session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
    $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['Name'])) {
    $loginUsername = $_POST['Name'];
    $password = $_POST['Pass'];
    $MM_fldUserAuthorization = "";
    $MM_redirectLoginSuccess = "main.php";
    $MM_redirectLoginFailed = "index.php";
    $MM_redirecttoReferrer = false;

    $LoginRS__query = sprintf("SELECT Name, Pass, FName, rt FROM users WHERE Name='%s' AND Pass='%s'",
        get_magic_quotes_gpc() ? $loginUsername : addslashes($loginUsername), get_magic_quotes_gpc() ? $password : addslashes($password));

    $res = $mysqli->query($LoginRS__query);

    if ($res->num_rows > 0) {

        $row = $res->fetch_assoc();

        $loginStrGroup = $row['rt'];

        //echo $loginStrGroup;
        //declare two session variables and assign them
        $_SESSION['MM_Username'] = $loginUsername;
        $_SESSION['MM_UserGroup'] = $loginStrGroup;

        // Запись сесии

        $SessionSQL = sprintf("INSERT INTO Sessions VALUES('','%s','%s','%s')", $loginUsername, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);

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
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Login</title>
    <link href="/img/emx_nav_left.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        <!--
        .style1 {
            font-family: "Arial", Arial, serif;
        }

        body {
            background-image: url(img/bg_grad.jpg);
            background-color: #336699;
        }

        .style2 {
            font-family: "Courier New", Courier, mono
        }

        -->
    </style>
    <script type="text/javascript">
        <!--
        function MM_reloadPage(init) {  //reloads the window if Nav4 resized
            if (init == true) with (navigator) {
                if ((appName == "Netscape") && (parseInt(appVersion) == 4)) {
                    document.MM_pgW = innerWidth;
                    document.MM_pgH = innerHeight;
                    onresize = MM_reloadPage;
                }
            }
            else if (innerWidth != document.MM_pgW || innerHeight != document.MM_pgH) location.reload();
        }
        MM_reloadPage(true);
        //-->
    </script>
</head>
<?php
require_once('class_func.php');
?>

<body>
<form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>">
    <table width="253" border="0">
        <tr>
            <th width="92" scope="row"><span class="style2">Name</span></th>
            <td width="162"><label>
                    <input name="Name" type="text" class="style1" id="Name"/>
                </label></td>
        </tr>
        <tr>
            <th scope="row"><span class="style2">Pass</span></th>
            <td><label>
                    <input name="Pass" type="password" class="style1" id="Pass"/>
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
