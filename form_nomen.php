<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Номенклатура</title>
</head>
    <body>
        <?php include("header.php"); ?>
        <!-- end masthead -->
        <div class="style1" id="pagecell1">
            <!--pagecell1-->
            <?php

            include_once("class_elem.php");

            $UserG = array('admin', 'oper');

            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                echo Func::ActButton('form_add_elem.php', 'Добавить Элемент');

            $e = new Elem();
            echo $e->ShowNomen();

            if (in_array($_SESSION['MM_UserGroup'], $UserG))
                echo Func::ActButton('form_add_elem.php', 'Добавить Элемент');

            ?>
        </div>
    </body>
</html>
