<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_roo = "localhost";
$database_roo = "trin";
$username_roo = "root";
$password_roo = "";

$mysqli = new mysqli($hostname_roo, $username_roo, $password_roo, $database_roo);
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>