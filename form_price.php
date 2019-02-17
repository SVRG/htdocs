<?php
include_once "security.php";
include_once "class_db.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<!-- DW6 -->
<head>
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
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT price,elem.kod_elem,quantity,price,name,price_list.time_stamp
                    FROM price_list
                    JOIN elem ON elem.kod_elem=price_list.kod_elem
                    WHERE price_list.del=0
                    ORDER BY elem.kod_elem ASC
        ");

        echo "<table width='100%' border='1' cellspacing='0'>";


        $price_list = "<tr bgcolor='#a9a9a9'>
                    <td>Наименование</td>
                    <td>Цена</td>
                    <td>Количество</td>
                    <td>Дата</td>";

        $cnt = $db->cnt;
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $price = func::Rub($row['price']);
            $quantity = $row['quantity'];
            $name = $row['name'];
            $date = func::Date_from_MySQL($row['time_stamp']);
            $kod_elem = $row['kod_elem'];

            echo "<tr>
                    <td><a href='form_elem.php?kod_elem=$kod_elem'>$name</a></td>
                    <td>$price</td>
                    <td>$quantity</td>
                    <td>$date</td>
                </td>";
            }

        echo "</table>"
        ?>
    </div>
</div>
</body>
</html>
