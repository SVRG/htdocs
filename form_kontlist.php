<?php
include_once "security.php";

include_once("class_kont.php");
include_once("class_doc.php");

$d = new Doc();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru" xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Список Контактов</title>
</head>
<body>
<?php include("header.php"); ?>
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <div id="pageName">
        <?php

        $c = new Kontakt();
        $query = "";
        if (isset($_GET['kod_elem'])) {
            $kod_elem = $_GET['kod_elem'];
            $query = /** @lang MySQL */
                "SELECT
                                    kontakty.kod_kontakta,
                                    kontakty.kod_org,
                                    kontakty.dolg,
                                    kontakty.famil,
                                    kontakty.`name`,
                                    kontakty.otch,
                                    view_rplan.nazv_krat,
                                    view_rplan.kod_elem
                                FROM
                                    kontakty
                                INNER JOIN view_rplan ON kontakty.kod_org = view_rplan.kod_org
                                WHERE kod_elem = $kod_elem
                                GROUP BY kontakty.kod_kontakta
                                ORDER BY
                                    view_rplan.nazv_krat ASC,
                                    kontakty.famil ASC";
        }

        echo $c->formAllKontats($query);
        ?>
    </div>
</div>
</body>
</html>
