<div class="menu" id="drop-nav-menu">
    <ul>
        <li><a href="../form_main.php">График Поставок</a>
            <ul>
                <li><a href="../form_main.php?sgp=5">Оплачено</a></li>
                <li><a href="../form_main.php?sgp=4">Не Оплачено</a></li>
                <li><a href="../form_main.php?sgp=2">Входящие</a></li>
            </ul>
        </li>
        <li><a href="../form_doclist.php">Договоры</a>
            <ul>
                <li><a href="../form_main.php?sgp=1">Исходящие</a></li>
                <li><a href="../form_main.php?sgp=1&pd&ost">Исходящие $</a></li>
                <li><a href="../form_main.php?sgp=7">Входящие</a></li>
                <li><a href="../form_main.php?sgp=7&d&ost">Входящие $</a></li>
            </ul>
        </li>
        <li><a href="../form_orglist.php">Организации</a>
            <ul>
                <li><a href="../form_orglist.php?dolg">Должники</a></li>
                <li><a href="../form_orglist.php?pays">Рейтинг <?php echo date("Y"); ?></a></li>
            </ul>
        </li>
        <li><a href="../form_nomen.php">Номенклатура</a></li>
        <li><a href="../form_pays.php">Платежи</a>
            <ul>
                <li><a href="../form_pays.php?VN">Исходящие</a></li>
            </ul>
        </li>
        <li><a href="../form_main.php?sgp=3&<?php echo "y=".date("Y"); ?>">Склад</a>
            <ul>
                <li><a href="../form_main.php?sgp=6">Отгрузка без оплаты</a></li>
            </ul>
        </li>
    </ul>
</div>