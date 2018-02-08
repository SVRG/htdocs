<?php
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

include_once "security.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<!-- DW6 -->
<head>
    <!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>План поставок CSM</title>
</head>
<body>
<?php include("header.php"); ?>
<!-- end masthead -->
<div class="style1" id="pagecell1">
    <!--pagecell1-->
    <div id="pageName">
        <?php
        include_once "class_db.php";
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT *
                    FROM view_rplan
                    WHERE (kod_elem=1002 OR kod_elem=1123 OR kod_elem=1004) AND zakryt=0 AND kod_ispolnit=683 AND numb_ostat>0
                    ORDER BY shifr ASC, kod_dogovora ASC, data_postav ASC
        ");

        echo "<table width='100%' border='1' cellspacing='0'>";

        $current_month = date('n'); // Текущий месяц
        $summ = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0); // массив сумм по 12 месяцам

        $month_row = "<tr bgcolor='#a9a9a9'>
                    <td>Договор</td>
                    <td>Организация</td>
                    <td>Номенклатура</td>";

        for ($j = 0; $j <= 11; $j++) {
            $month_row .= "<td width='50'>" . ($j + 1) . "</td>";
        }
        echo $month_row .= "</rt>";

        $cnt = $db->cnt;
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $nomer = $row['nomer'];
            $nazv_krat = $row['nazv_krat'];
            $kod_dogovora = $row['kod_dogovora'];
            $kod_org = $row['kod_org'];
            $kod_elem = $row['kod_elem'];
            $shifr = $row['shifr'];

            echo "<tr>
                    <td><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$nomer</a></td>
                    <td><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>
                    <td><a href='form_elem.php?kod_elem=$kod_elem'>$shifr</a></td>";
            // Договор
            for ($j = 1; $j <= 12; $j++) {
                echo "<td>";

                $date = strtotime($row['data_postav']);

                if (date('n', $date) == $j and $row['numb_ostat'] > 0) {
                    echo $row['numb_ostat'];
                    $summ[$j - 1] += $row['numb_ostat'];
                }
                echo "</td>";
            }
            echo "</tr>";
        }

        echo $month_row;
        // Итого
        echo "<tr>
                    <td></td>
                    <td></td>
                    <td>Итого</td>";

        for ($j = 0; $j <= 11; $j++) {
            $sm = "";
            if ($summ[$j] > 0)
                $sm = $summ[$j];

            echo "<td>" . $sm . "</td>";
        }
        echo "</tr>";

        // Итого с накоплением
        echo "<tr>
                    <td></td>
                    <td></td>
                    <td>Итого с накоплением</td>";
        $sm = 0;
        for ($j = 0; $j <= 11; $j++) {
            if ($summ[$j] > 0)
                $sm += $summ[$j];
            echo "<td>";
            if ($sm > 0)
                echo $sm;
            echo "</td>";
        }
        echo "</tr>";

        echo "</table>"
        ?>
    </div>
</div>
</body>
</html>
