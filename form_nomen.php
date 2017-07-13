<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";
include_once("class_elem.php");
$E = new Elem();
$E->Events();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <title>Номенклатура</title>
    <link rel="stylesheet" type="text/css" href="/widgets/jquery-ui/jquery-ui.css">

    <link rel="stylesheet" type="text/css" href="/widgets/autocomplete/autocomplete.css">

    <script type="text/javascript" src="/widgets/jquery-ui/external/jquery/jquery.js"></script>

    <script type="text/javascript" src="/widgets/jquery-ui/jquery-ui.min.js"></script>

    <script type="text/javascript" src="/widgets/autocomplete/jquery.ui.autocomplete.html.js"></script>

    <script type="text/javascript" src="/widgets/autocomplete/autocomplete.js"></script>
</head>
    <body>
        <?php include("header.php"); ?>
        <!-- end masthead -->
        <div class="style1" id="pagecell1">
            <input type="text" id="topic_title" title="Найти">
            <?php

            $UserG = array('admin', 'oper');

            $add = false;
            if(isset($_POST['Flag']))
                if($_POST['Flag']=='formAdd')
            {
                echo $E->formAddEdit();
                $add = true;
            }

            if(!$add)
                if (in_array($_SESSION['MM_UserGroup'], $UserG)) {
                    echo Func::ActButton('', 'Добавить Элемент', 'formAdd');
                    echo $E->formNomen();
                }
            ?>
        </div>
    </body>
</html>
