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
    <title>Прайс-лист</title>
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
                    ORDER BY elem.name;
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

            $rows2 = $db->rows("SELECT *
                                        FROM view_rplan
                                        WHERE kod_elem=$kod_elem AND kod_ispolnit=683 AND numb_otgruz>0
                                        ORDER BY kod_part DESC;");
            $last_price = "";
            for($j=0;$j<$db->cnt;$j++)
            {
                $row2 = $rows2[$j];
                $last_price .= func::Rub($row2['price_it'])."<br>";

                if($j >= 5)
                    break;
            }

            echo "<tr>
                    <td><a href='form_elem.php?kod_elem=$kod_elem'>$name</a></td>
                    <td><b>$price</b><br>$last_price</td>
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
