<div class="menu" id="drop-nav-menu">
    <ul>
        <li><a href="../form_main.php">График Поставок</a>
            <ul>
                <li><a href="../form_main.php?sgp=5">Оплачено</a></li>
                <li><a href="../form_main.php?sgp=4">Не Оплачено</a></li>
                <li><a href="../form_main.php?sgp=2">Входящие</a></li>
            </ul>
        </li>
        <li><a href="../form_doclist.php?<?php echo "y=".date("Y"); ?>">Договоры</a>
            <ul>
                <li><a href="../form_doclist.php?add">Добавить</a></li>
                <li><a href="../form_doclist.php">Все</a></li>
                <li><a href="../form_main.php?sgp=1">Исходящие</a></li>
                <li><a href="../form_main.php?sgp=1&pd&ost">Исходящие $</a></li>
                <li><a href="../form_main.php?sgp=9">Исходящие ?</a></li>
                <li><a href="../form_main.php?sgp=1&npd">Исходящие !</a></li>
                <li><a href="../form_main.php?sgp=7">Входящие</a></li>
                <li><a href="../form_main.php?sgp=7&pd&ost">Входящие $</a></li>
            </ul>
        </li>
        <li><a href="../form_orglist.php">Организации</a>
            <ul>
                <li><a href="../form_orglist.php?add">Добавить</a></li>
                <li><a href="../form_orglist.php?dolg">Должники</a></li>
                <li><a href="../form_orglist.php?pays">Рейтинг <?php echo date("Y"); ?></a></li>
            </ul>
        </li>
        <li><a href="../form_nomen.php">Номенклатура</a>
            <ul>
                <li><a href="../form_nomen.php?add">Добавить</a></li>
                <li><a href="../form_set.php">Сборки</a></li>
                <li><a href="../form_nomen.php?rating">Рейтинг <?php echo date("Y"); ?></a></li>
            </ul>
        </li>
        <li><a href="../form_pays.php">Платежи</a>
            <ul>
                <li><a href="../form_pays.php?VN">Исходящие</a></li>
            </ul>
        </li>
        <li><a href="../form_main.php?sgp=3&<?php echo "y=".date("Y").'&m='.date("m"); ?>">Склад</a>
            <ul>
                <li><a href="../form_main.php?sgp=6">Отгрузка без оплаты</a></li>
            </ul>
        </li>
    </ul>
</div>