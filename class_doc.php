<?php
include_once('class_part.php');
include_once('class_kont.php');
include_once('class_org.php');
include_once('class_docum.php');
include_once('class_db.php');
include_once('class_mail.php');

// Класс Договор
class Doc
{
    public $Data;   // строка запроса - row['']

    // Поля
    public $kod_dogovora = '';  // код_договора
    public $kod_org = '';       // код_организации (Заказчик)
    public $nazv_krat = '';     // краткое название организации из расшиернного запроса

//----------------------------------------------------------------------------------------------------------------------
    /**
     * Doc constructor.
     * @param int $kod_dogovora
     */
    public function __construct($kod_dogovora = -1)
    {

    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Показать Партии по Элементу - только партии, для просмотра договора надо в него перейти
     * @param  int $kod_elem
     * @return string
     */
    static public function formDocByElem($kod_elem)
    {
        $db = new Db();
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        // Открытые
        $rows = $db->rows("SELECT
                                      *
                                    FROM view_rplan
                                    WHERE kod_elem=$kod_elem AND zakryt=0 AND kod_ispolnit=$kod_org_main
                                    ORDER BY kod_dogovora DESC"); // Код договора по убыванию
        $res = Doc::formRPlan_by_Elem($rows);
        // Закрытые
        $res .= func::ActButton2("", "Закрытые", "close", "close", 1);
        if (isset($_POST['close']) or isset($_POST['in_close'])) {
            $rows = $db->rows("SELECT
                                      *
                                    FROM view_rplan
                                    WHERE kod_elem=$kod_elem AND zakryt=1 AND kod_ispolnit=$kod_org_main
                                    ORDER BY kod_dogovora DESC"); // Код договора по убыванию
            $res .= "<b>Закрытые</b><br>" . Doc::formRPlan_by_Elem($rows);
        }

        // Внешние открытые
        $res .= func::ActButton2("", "Внешние открытые", "in_open", "in_open", 1);
        if (isset($_POST['in_open']) or isset($_POST['in_close'])) {
            $rows = $db->rows("SELECT
                                    `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
                                    `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
                                    `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
                                    `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
                                    `trin`.`parts`.`modif`                                                                               AS `modif`,
                                    `trin`.`parts`.`numb`                                                                                AS `numb`,
                                    `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
                                    round(`trin`.`parts`.`nds`, 2)                                                                       AS `nds`,
                                    round(ifnull(((`trin`.`parts`.`numb` * `trin`.`parts`.`price`) * (1 + `trin`.`parts`.`nds`)), 0),
                                          2)                                                                                             AS `part_summa`,
                                    `trin`.`parts`.`val`                                                                                 AS `val`,
                                    `trin`.`parts`.`price`                                                                               AS `price`,
                                    `trin`.`elem`.`kod_elem`                                                                             AS `kod_elem`,
                                    `trin`.`elem`.`obozn`                                                                                AS `obozn`,
                                    `trin`.`elem`.`shifr`                                                                                AS `shifr`,
                                    `trin`.`parts`.`kod_part`                                                                            AS `kod_part`,
                                    ifnull(`trin`.`dogovory`.`zakryt`, 0)                                                                AS `zakryt`,
                                    `trin`.`dogovory`.`kod_ispolnit`                                                                     AS `kod_ispolnit`,
                                    `trin`.`elem`.`name`                                                                                 AS `name`,
                                    `ispolnit`.`nazv_krat`                                                                               AS `ispolnit_nazv_krat`,
                                    ifnull(`view_sklad_summ_postup`.`summ_postup`,
                                           0)                                                                                            AS `numb_otgruz`,
                                    (`trin`.`parts`.`numb` - ifnull(`view_sklad_summ_postup`.`summ_postup`, 0))                          AS `numb_ostat`
                                  FROM (((((`trin`.`dogovory`
                                    JOIN `trin`.`parts` ON ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) JOIN `trin`.`org`
                                      ON ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) JOIN `trin`.`elem`
                                      ON ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) JOIN `trin`.`org` `ispolnit`
                                      ON ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`))) LEFT JOIN `trin`.`view_sklad_summ_postup`
                                      ON ((`trin`.`parts`.`kod_part` = `view_sklad_summ_postup`.`kod_part`)))
                                  WHERE (`trin`.`parts`.`del` = 0) AND zakryt=0 AND parts.kod_elem=$kod_elem AND dogovory.kod_org=$kod_org_main
                                  ORDER BY kod_dogovora DESC;"); // Код договора по убыванию
            $res .= "<b>Входящие</b><br>" . Doc::formRPlan_by_Elem($rows);
        }

        // Внешние закрытые
        $res .= func::ActButton2("", "Внешние закрытые", "in_close", "in_close", 1);
        if (isset($_POST['in_close'])) {
            $rows = $db->rows("SELECT
                                    `trin`.`dogovory`.`kod_dogovora`                                                                     AS `kod_dogovora`,
                                    `trin`.`dogovory`.`nomer`                                                                            AS `nomer`,
                                    `trin`.`org`.`kod_org`                                                                               AS `kod_org`,
                                    `trin`.`org`.`nazv_krat`                                                                             AS `nazv_krat`,
                                    `trin`.`parts`.`modif`                                                                               AS `modif`,
                                    `trin`.`parts`.`numb`                                                                                AS `numb`,
                                    `trin`.`parts`.`data_postav`                                                                         AS `data_postav`,
                                    round(`trin`.`parts`.`nds`, 2)                                                                       AS `nds`,
                                    round(ifnull(((`trin`.`parts`.`numb` * `trin`.`parts`.`price`) * (1 + `trin`.`parts`.`nds`)), 0),
                                          2)                                                                                             AS `part_summa`,
                                    `trin`.`parts`.`val`                                                                                 AS `val`,
                                    `trin`.`parts`.`price`                                                                               AS `price`,
                                    `trin`.`elem`.`kod_elem`                                                                             AS `kod_elem`,
                                    `trin`.`elem`.`obozn`                                                                                AS `obozn`,
                                    `trin`.`elem`.`shifr`                                                                                AS `shifr`,
                                    `trin`.`parts`.`kod_part`                                                                            AS `kod_part`,
                                    ifnull(`trin`.`dogovory`.`zakryt`, 0)                                                                AS `zakryt`,
                                    `trin`.`dogovory`.`kod_ispolnit`                                                                     AS `kod_ispolnit`,
                                    `trin`.`elem`.`name`                                                                                 AS `name`,
                                    `ispolnit`.`nazv_krat`                                                                               AS `ispolnit_nazv_krat`,
                                    ifnull(`view_sklad_summ_postup`.`summ_postup`,
                                           0)                                                                                            AS `numb_otgruz`,
                                    (`trin`.`parts`.`numb` - ifnull(`view_sklad_summ_postup`.`summ_postup`, 0))                          AS `numb_ostat`
                                  FROM (((((`trin`.`dogovory`
                                    JOIN `trin`.`parts` ON ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) JOIN `trin`.`org`
                                      ON ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) JOIN `trin`.`elem`
                                      ON ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) JOIN `trin`.`org` `ispolnit`
                                      ON ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`))) LEFT JOIN `trin`.`view_sklad_summ_postup`
                                      ON ((`trin`.`parts`.`kod_part` = `view_sklad_summ_postup`.`kod_part`)))
                                  WHERE (`trin`.`parts`.`del` = 0) AND zakryt=1 AND parts.kod_elem=$kod_elem AND dogovory.kod_org=$kod_org_main
                                  ORDER BY kod_dogovora DESC;");
            $res .= "<b>Входящие Закрытые</b><br>" . Doc::formRPlan_by_Elem($rows);
        }
        return $res;
    }
//-----------------------------------------------------------------

    /**
     * Договоры - основной формат вывода
     * Группировка строк rplan по договорам
     * На вход должен подаватья rplan отсотированный по коду договора!
     * @param array $rplan_rows
     * @return string
     */
    static public function formRPlan_by_Doc($rplan_rows)
    {
        $cnt = count($rplan_rows);

        if ($cnt == 0)
            return "Список договоров пуст";

        $dogovor_deyst = ""; // Таблица действующих договоров
        $dogovor_zakryt = ""; // Таблица закрытых договоров
        $dogovor_vnesh = ""; // Таблица внешних действующих договоров
        $dogovor_vnesh_zakryt = ""; // Таблица закрытых внешних договоров

        $res = "<table border='1' cellspacing='0' width='100%'>"; // Результирующий набор строк с объединением

        $header = "<tr bgcolor='#5f9ea0'>
                    <th width='10%'>Договор</th>
                    <th width='15%'>Организация</th>
                    <th>Наименование</th>
                    <th>Кол-во</th>
                    <th>Дата поставки</th>
                    <th>Цена с НДС</th>
                    <th>Сумма</th>
                    <th>Оплачено</th>
                  </tr>";

        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        for ($i = 0; $i < $cnt; $i++) { //
            // todo - Проверить пропуски
            $buffer = self::getDocBuffer($rplan_rows, $i);

            // Записываем буфер
            if (count($buffer) > 0) {
                $zakryt = (int)$buffer[0]['zakryt'];
                $kod_ispolnit = (int)$buffer[0]['kod_ispolnit'];

                $rplan_row = Doc::getRPlan_Row($buffer);

                // Внешние договоры
                if ($kod_ispolnit != $kod_org_main) {
                    if ($zakryt == 1) // Внешний закрытый
                        $dogovor_vnesh_zakryt .= $rplan_row;
                    // Внешний действующий
                    else
                        $dogovor_vnesh .= $rplan_row;
                } // Действующий договор
                elseif ($zakryt == 0)
                    $dogovor_deyst .= $rplan_row;
                // Закрытый договор
                else
                    $dogovor_zakryt .= $rplan_row;
            }

            unset($buffer); // Очищаем буфер

        }

        if ($dogovor_deyst != "") {
            $res .= "<tr bgcolor='#5f9ea0'><th colspan='8'>Действующие</th></tr>";
            $res .= $header;
            $res .= $dogovor_deyst;
        }

        if ($dogovor_zakryt != "") {
            $res .= "<tr bgcolor='#5f9ea0'><th colspan='8'>Закрытые</th></tr>";
            $res .= $header;
            $res .= $dogovor_zakryt;
        }

        if ($dogovor_vnesh != "") {
            $res .= "<tr bgcolor='#5f9ea0' ><th colspan='8'>Внешние</th></tr>";
            $res .= $header;
            $res .= $dogovor_vnesh;
        }

        if ($dogovor_vnesh_zakryt != "") {
            $res .= "<tr bgcolor='#5f9ea0'><th colspan='8'>Внешние закрытые</th></tr>";
            $res .= $header;
            $res .= $dogovor_vnesh_zakryt;
        }

        $res .= "</table>";

        return $res;
    }
//--------------------------------------------------------------
//

    /**
     * Собирает буфер по одному коду договора для формирования строки
     * @param array $rplan_rows
     * @param $i - внешний счетчик
     * @return array
     */
    private static function getDocBuffer($rplan_rows, &$i)
    {
        $buffer = array();

        $cnt = count($rplan_rows);
        if ($cnt == 0)
            return $buffer;

        $kod_dogovora = $rplan_rows[$i]['kod_dogovora'];

        for (; $i < $cnt; $i++) {
            $row = $rplan_rows[$i];
            if ($row['kod_dogovora'] == $kod_dogovora)
                array_push($buffer, $row);
            else {
                $i--; // Возвращаемся на шаг назад, т.к. это уже новая строчка
                break;
            }
        }

        return $buffer;
    }
//--------------------------------------------------------------
//

    /**
     * Формирует объединенную строку Договор + Партии из строк/строки rplan отобранных по одному коду договора
     * На вход подается массив с одним кодом договора
     * @param array $rplan_rows - Строки rplan
     * @return string
     */
    public static function getRPlan_Row($rplan_rows)
    {
        $cnt = count($rplan_rows);

        if ($cnt == 0)
            return "";

        $res = ""; // Результирующий набор строк с объединением

        $rowspan = " rowspan='$cnt'"; // Количество объединяемых строк

        // Данные глобальные
        // Договор
        $kod_dogovora = (int)$rplan_rows[0]['kod_dogovora']; // Код договора
        $nomer = $rplan_rows[0]['nomer']; // номер договора
        $annulir = ""; // Аннулирован
        $kod_org = (int)$rplan_rows[0]['kod_org']; // Код организации (Заказчик)
        $nazv_krat = $rplan_rows[0]['nazv_krat']; // Название Заказчика
        $kod_ispolnit = $rplan_rows[0]['kod_ispolnit']; // Код исполнителя
        $ispolnit_nazv_krat = $rplan_rows[0]['ispolnit_nazv_krat']; // Название исполнителя
        $zakryt = $rplan_rows[0]['zakryt'];

        // Процент оплаты по договору
        $oplacheno = self::getProcPay($kod_dogovora); // todo - медленные запросы, надо подумать как их ускорить.
        if ($oplacheno > 0)
            $oplacheno .= "%";
        else
            $oplacheno = "";

        $summa_plat = self::getSummaPlat($kod_dogovora);        // todo - медленные запросы, надо подумать как их ускорить.

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rplan_rows[$i];

            // Данны по Партии
            // Партия
            $kod_part = (int)$row['kod_part']; // Код партии
            $kod_elem = (int)$row['kod_elem']; // Код элемента
            $shifr = $row['shifr']; // Обозначение
            $mod = $row['modif']; // Модификация
            $numb = (int)$row['numb']; // Количество
            $numb_ostat = (int)$row['numb_ostat']; // Осталось отгрузить
            $data = Func::Date_from_MySQL($row['data_postav']); // Дата поставки
            $price = Part::getPrice($row); // Цена
            $val = ""; // Валюта
            $price_nds = round($price * (1 + (double)$row['nds']), 2); // Цена с НДС
            $part_summa = Part::getPartSumma($row); // Сумма партии
            $nds = ""; // НДС

            // НДС
            if ((int)((double)$row['nds'] * 100) != 18)
                $nds = "<br>НДС " . (int)((double)$row['nds'] * 100) . "%";

            $ind_row = ""; // Индикатор строки Если договор закрыт - зеленый. Нет - без цвета
            if ((int)$zakryt == 1) {
                if ($summa_plat > 0)
                    $ind_row = " bgcolor='#85e085'";
                else
                    $ind_row = " bgcolor='#ffaaa0'";
            }

            $conf = new config();
            $kod_org_main = $conf->kod_org_main;

            $ostatok_str = ""; // Остаток отгрузки
            // Для исходящих договоров
            if ($numb_ostat > 0 and (int)$row['kod_ispolnit'] == $kod_org_main) {
                if ($numb_ostat > 0 and $numb_ostat != $numb) // Вывод остатка. Если он не нулевой и не равен количеству поставки то выводим
                    $ostatok_str = " <abbr title=\"Осталось отгрузить $numb_ostat\">($numb_ostat)</abbr>";
            }

            // Если договор входящий - выводим сколько осталось получить
            if ($row['kod_ispolnit'] != $kod_org_main) {
                $numb_ostat = $numb - Part::getNumbPoluch($kod_part);
                if ($numb_ostat > 0 and $numb_ostat != $numb) // Вывод остатка. Если он не нулевой и не равен количеству поставки то выводим
                    $ostatok_str = " <abbr title=\"Осталось получить $numb_ostat\">($numb_ostat)</abbr>";
            }

            $ind_data = ""; // Индикатор окраски даты - если менее 14 дней - то желтый
            if ($summa_plat > 0 and $numb_ostat > 0) {
                $days_rem = func::DaysRem($row['data_postav']);
                if ($days_rem <= 14)
                    $ind_data = /** @lang HTML */
                        " bgcolor='#F18585'";
                elseif ($days_rem <= 30)
                    $ind_data = /** @lang HTML */
                        " bgcolor='#f4df42'";
            }

            $ind_part = ''; // Окрашиваем ячейку Кол-во в зеленый если все отгружено
            if ($numb_ostat == 0) {
                $ind_part = /** @lang HTML */
                    " bgcolor='#85e085'";
            }

            // Если договор внешний то надо Код организации указать как Код исполнителя
            if ($kod_ispolnit != $kod_org_main) {
                $kod_org = $kod_ispolnit;
                $nazv_krat = $ispolnit_nazv_krat;
            }

            // Модификация
            if ($mod != "")
                $mod = " ($mod)";

            // Нет контаката по договору
            $no_contact = self::formNoContact($kod_dogovora);

            // Формируем строку
            if ($i == 0 and $cnt > 1) { // Когда требуется объединение строк
                $res .= /** @lang HTML */
                    "<tr $ind_row>
                                <td $rowspan width='100'><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$no_contact$nomer<br>$annulir</a></td>
                                <td $rowspan><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>";
            } elseif ($cnt == 1) // Когда объединение строк не требуется
            {
                $res .= /** @lang HTML */
                    "<tr $ind_row>
                                    <td><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$no_contact$nomer<br>$annulir</a></td>
                                    <td width='150'><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>";
            } else
                $res .= /** @lang HTML */
                    "<tr $ind_row>";

            $res .= /** @lang HTML */
                "<td  width='365'><a href='form_part.php?kod_part=$kod_part&kod_dogovora=$kod_dogovora'><img src='/img/edit.gif' height='14' border='0' /></a>
                                       <a href='form_elem.php?kod_elem=$kod_elem'>$shifr $mod</a></td>
                      <td width='40' align='right' $ind_part>$numb $ostatok_str </td>
                      <td width='80' align='center' $ind_data >$data</td>
                      <td width='120' align='right'>" . Func::Rub($price_nds) . "</td>
                      <td width='120' align='right'>" . Func::Rub($part_summa) . $val . $nds . "</td>
                      <td width='90'>$oplacheno</td>
                  </tr>";
        }

        return $res;
    }
//--------------------------------------------------------------
//

    /**
     * Сумма договора
     * @param int $kod_dogovora
     * @return float
     */
    public static function getSummaDogovora($kod_dogovora)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                                    kod_dogovora,
                                    numb,
                                    price,
                                    price_or,
                                    nds
                                  FROM view_rplan
                                  WHERE kod_dogovora=$kod_dogovora
                                  ");
        if ($db->cnt > 0) {
            $summa_dogovora = 0.;

            for ($i = 0; $i < $db->cnt; $i++) {
                $row = $rows[$i];
                $summa_dogovora += Part::getPartSumma($row);
            }

            return $summa_dogovora;
        }
        return 0.;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Сумма платежей по договору
     * @param int $kod_dogovora
     * @return float
     */
    public static function getSummaPlat($kod_dogovora)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_dogovor_summa_plat WHERE kod_dogovora=$kod_dogovora");

        if ($db->cnt > 0)
            return (double)$rows[0]['summa_plat'];
        else
            return 0.;
    }
//--------------------------------------------------------------
//

    /**
     * Договоры по Контакту
     * @param int $kod_kontakta
     * @return string
     */
    public static function formDocsByKontakt($kod_kontakta)
    {

        $db = new Db();

        $rows = $db->rows(/** @lang SQL */
            "SELECT
                                        *
                                    FROM
                                        view_rplan
                                    INNER JOIN view_kontakty_dogovora ON view_rplan.kod_dogovora = view_kontakty_dogovora.kod_dogovora
                                    WHERE
                                        view_kontakty_dogovora.kod_kontakta = $kod_kontakta
                                    ORDER BY
                                        view_rplan.kod_dogovora DESC,
                                        view_rplan.name ASC");

        return Doc::formRPlan_by_Doc($rows);
    }
//--------------------------------------------------------------
//

    /**
     * Вывод формы договора
     * @param int $Edit - редактирование
     * @param int $Close - форма закрытия
     */
    public function formDogovor($Edit = 0, $Close = 0)
    {

        if ($Edit == 1) {
            echo $this->formAddEdit(1);
        } else {
            $this->getData();
            $row = $this->Data;
            $clr = '';

            // Если договор закрыт то красим красным
            if ($row['zakryt'] == 1)
                $clr = '<tr>
                            <th ></th>
                            <td bgcolor="#F18585">Закрыт</td>
                            <td>' . Func::ActButton2('', "Восстановить", "DocOpen", 'kod_dogovora_open', $row['kod_dogovora']) . '</td>
                        </tr>';
            else {
                $close = true;
                if (isset($_POST['Flag']))
                    if ($_POST['Flag'] == 'DocClose') {
                        $clr = "   <tr>
                                        <td bgcolor=\"#F18585\">Закрыть?</td>
                                        <td bgcolor='red'>" . Func::ActButton('', 'Подтвердить Закрытие', 'DocCloseConf') . Func::Cansel(0) . "</td>
                                    </tr>";
                        $close = false;
                    }

                if ($Close == 1 and $close)
                    $clr = '<tr>
                            <th ></th>
                            <td>' . Func::ActButton('', 'Закрыть', 'DocClose') . '</td>
                            </tr>';
            }

            $ISP = '';
            $conf = new config();
            $kod_org_main = $conf->kod_org_main;
            if ($this->kod_org == $kod_org_main) {
                $ISP = '<tr>
                            <th >Исполнитель</th>
                            <td><a href="form_org.php?kod_org=' . $row['kod_ispolnit'] . '">' . $row['ispolnit_nazv_krat'] . '</a></td>
                            </tr>';
            }

            $copy_btn = Func::ActButtonConfirm('Копировать', 'copyDogovor', 'Подтвердить копирование Договора');

            $summa_dogovora = self::getSummaDogovora($row['kod_dogovora']);
            $summa_plat = self::getSummaPlat($row['kod_dogovora']);
            $ostatok = $summa_dogovora - $summa_plat;

            $user = $this->getUser();
            $row_user = "";
            if($user!=="")
                $row_user = "<tr>
                                <th >Введен</th>
                                <td>$user</td>
                            </tr>";

            echo // todo - Проверить правильность. Округление! Валюта - пока только руб.
                '<table width="600" border="0">
                        <th width="202" >Номер</th>
                        <td width="374"><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '" ><h1>' . $row['nomer'] . '</a><a target="_blank" href="form_invoice.php?kod_dogovora='.$this->kod_dogovora.'"><img src="img/printer.png"></a>' . $copy_btn . '</h1></td>
                      </tr>
                      <tr>
                        <th >Дата Составления </th>
                        <td>' . Func::Date_from_MySQL($row['data_sost']) . '</td>
                      </tr>
                      <tr>
                        <th >Заказчик</th>
                        <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                      </tr>
                      <tr>
                      </tr>
                        ' . $ISP . '
                      <tr>
                        <th >Сумма Договора</th>
                        <td>' . func::Rub($summa_dogovora) . ' р.</td>
                      </tr>
                      <tr>
                        <th >Сумма Платежей</th>
                        <td>' . Func::Rub($summa_plat) . ' р.</td>
                      </tr>
                      <tr>
                        <th >Остаток</th>
                        <td>' . Func::Rub($ostatok) . ' р.</td>
                      </tr>'. $row_user . $clr . '
                </table>';

        }
    }
//--------------------------------------------------------------
//

    /**
     * Форма редактирования Договора
     * @param int $Edit - 1 форма редактирования
     * @return string
     */
    public function formAddEdit($Edit = 0)
    {
        $nomer = $this->getNextSchetNomer();
        $data_sost = Func::NowE();
        $kod_org = $this->kod_org;
        $FormName = "formAdd";

        $posav_checked = "checked";
        $zakup_checked = "";

        if ($Edit == 1) {
            $nomer = $this->Data['nomer'];
            $data_sost = Func::Date_from_MySQL($this->Data['data_sost']);
            $kod_org = $this->Data['kod_org'];
            $conf = new config();
            $kod_org_main = $conf->kod_org_main;
            if ($kod_org == $kod_org_main) {
                $zakup_checked = "checked";
                $posav_checked = "";
                $kod_org = $this->Data['kod_ispolnit'];
            }

            $FormName = "formEdit";
        }

        $res = /** @lang HTML */
            '<form id="form1" name="form1" method="post" action="">
                    <table width="100%" border="0">
                      <tr>
                        <th width="202" >Номер</th>
                        <td width="100%"><span id="SNumR">
                                  <input  name="nomer" id="nomer" value="' . $nomer . '"/>
                                  <span class="textfieldRequiredMsg">Нужно ввести значение.</span><span class="textfieldMinCharsMsg">Minimum
                                  number of characters not met.</span></span>
                         </td>
                      </tr>
                      <tr>
                        <th >Дата</th>
                        <td><span id="SDateR">
                                  <input  name="data_sost" id="data_sost" value="' . $data_sost . '" />
                             <span class="textfieldRequiredMsg">Нужно ввести значение.</span>
                             <span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span>
                             </span>
                        </td>
                      </tr>
                      <tr>
                        <th >Контрагент</th>
                        <td>' . Org::formSelList2($kod_org) . '</td>
                      </tr>
                      <tr>
                        <th >Тип Догвора</th>
                            <td>     
                                    <p><input name="doc_type" id="doc_type" type="radio" value="postav" ' . $posav_checked . '>Поставка</p>
                                    <p><input name="doc_type" id="doc_type" type="radio" value="zakup" ' . $zakup_checked . '>Закупка</p>
                            </td>
                      </tr>
                    </table>
                    <input id="' . $FormName . '" type="hidden" value="' . $FormName . '" name="' . $FormName . '"/>
                    <input type="submit" value="Сохранить" />
                </form>';
        return $res;
    }
//--------------------------------------------------------------------
//

    /**
     * Запрос данных по договору
     * @param int $kod_dogovora
     * @return array
     */
    public function getData($kod_dogovora = -1)
    {
        if ($kod_dogovora > 0)
            $this->kod_dogovora = $kod_dogovora;

        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_dogovor_data WHERE kod_dogovora= $this->kod_dogovora");

        unset($this->Data);

        if ($db->cnt == 0)
            exit("Неправильный код договора");

        $this->Data = $rows[0];
        $this->kod_org = $this->Data['kod_org'];
        $this->nomer = $this->Data['nomer'];
        $this->nazv_krat = $this->Data['nazv_krat'];

        return $this->Data;

    }
//--------------------------------------------------------------------
//

    /**
     * Форма - Партии договора
     * @param int $sgp
     * @return string
     */
    public function formParts($sgp = 0)
    {
        $p = new Part();
        $p->kod_dogovora = $this->kod_dogovora;
        return $p->formParts($sgp);
    }
//--------------------------------------------------------------
//

    /**
     * План реализации
     * @param int $VN : 1 - внешний; 0 - внутренний
     * @return string
     */
    public function formRPlan($VN = 0)
    {
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        if ($VN == 0) //Если договор поставки
            $sql = /** @lang SQL */
                "SELECT 
                    * 
                    FROM 
                      view_rplan 
                    WHERE 
                      kod_ispolnit=$kod_org_main AND zakryt<>1 AND numb_ostat>0
                    ORDER BY 
                      shifr ASC,
                      numb DESC";

        else // Если внешних договор
            $sql = /** @lang SQL */
                "SELECT 
                    * 
                    FROM 
                      view_rplan 
                    WHERE 
                      kod_org=$kod_org_main AND zakryt<>1 AND numb_ostat>0
                    ORDER BY 
                      shifr ASC, 
                      numb DESC";

        $db = new Db();
        $rows = $db->rows($sql); // Массив данных

        return $this->formRPlan_by_Elem($rows);
    }
//--------------------------------------------------------------
//

    /**
     * Список оплаченных договоров
     * @return string
     */
    public function formRPlanOplach()
    {
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        $sql = /** @lang SQL */
            "SELECT
                      view_rplan.kod_dogovora,
                      view_rplan.nomer,
                      view_rplan.kod_org,
                      view_rplan.nazv_krat,
                      view_rplan.modif,
                      view_rplan.numb,
                      view_rplan.data_postav,
                      view_rplan.nds,
                      view_rplan.part_summa,
                      view_rplan.val,
                      view_rplan.price,
                      view_rplan.price_or,
                      view_rplan.kod_elem,
                      view_rplan.obozn,
                      view_rplan.shifr,
                      view_rplan.kod_part,
                      view_rplan.zakryt,
                      view_rplan.kod_ispolnit,
                      view_rplan.name,
                      view_rplan.ispolnit_nazv_krat,
                      view_rplan.numb_otgruz,
                      view_rplan.numb_ostat
                    FROM
                      view_rplan
                      LEFT JOIN view_dogovor_summa_plat ON view_dogovor_summa_plat.kod_dogovora=view_rplan.kod_dogovora
                    WHERE
                      view_rplan.kod_org<>$kod_org_main AND zakryt<>1 AND numb_ostat>0 AND view_dogovor_summa_plat.summa_plat>0
                    ORDER BY
                      shifr ASC,
                      numb DESC";

        $db = new Db();
        $rows = $db->rows($sql); // Массив данных

        return $this->formRPlan_by_Elem($rows);
    }
//--------------------------------------------------------------
//

    /**
     * Список не оплаченных договоров - по которым вообще нет никакой оплаты, частично оплаченные не выводятся
     * @return string
     */
    public function formRPlanNeOplach()
    {
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        $sql = /** @lang SQL */
            "SELECT
                      view_rplan.kod_dogovora,
                      view_rplan.nomer,
                      view_rplan.kod_org,
                      view_rplan.nazv_krat,
                      view_rplan.modif,
                      view_rplan.numb,
                      view_rplan.data_postav,
                      view_rplan.nds,
                      view_rplan.part_summa,
                      view_rplan.val,
                      view_rplan.price,
                      view_rplan.price_or,
                      view_rplan.kod_elem,
                      view_rplan.obozn,
                      view_rplan.shifr,
                      view_rplan.kod_part,
                      view_rplan.zakryt,
                      view_rplan.kod_ispolnit,
                      view_rplan.name,
                      view_rplan.ispolnit_nazv_krat,
                      view_rplan.numb_otgruz,
                      view_rplan.numb_ostat
                    FROM
                      view_rplan
                      LEFT JOIN view_dogovor_summa_plat ON view_dogovor_summa_plat.kod_dogovora=view_rplan.kod_dogovora
                    WHERE
                      view_rplan.kod_org<>$kod_org_main AND zakryt<>1 AND numb_ostat>0 AND ISNULL(view_dogovor_summa_plat.summa_plat)
                    ORDER BY
                      shifr ASC,
                      numb DESC";

        $db = new Db();
        $rows = $db->rows($sql); // Массив данных

        return $this->formRPlan_by_Elem($rows);
    }
//--------------------------------------------------------------
//

    /**
     * График поставок по изделиям (План Реализации)
     * @param array $rplan_rows - массив rplan отсортированный по элементам
     * @return string
     */
    static public function formRPlan_by_Elem($rplan_rows)
    {
        $cnt = count($rplan_rows); // Количество записей

        if ($cnt == 0) return "Список договоров пуст<br>"; // Если данных нет то выходим

        // Формируем заголовок таблицы
        $header = /** @lang HTML */
            "<tr bgcolor=\"#CCCCCC\">
                    <td width=\"200\">Наименование</td>
                    <td>Кол-во</td>
                    <td>Оплата</td>
                    <td width=\"150\">Номер Договора</td>
                    <td>Организация</td>
                    <td width=\"100\">Дата</td>
                    <td width=\"100\">Цена с НДС</td>
                    <td width=\"100\">Сумма с НДС</td>
                </tr>";

        // Создаем таблицу
        $res = '<table width="100%">' . $header;

        // Переменные
        $zebra = "#FFFFFF"; // Цвет зебры
        $itog_summ = 0; // Итоговая Сумма по всем партиям
        $itog_numb_ostat = 0; // Итог по остаткам
        $kod_elem_pred = -1; // Код предыдущего элемента
        $summ_numb_ostat = 0; // Сумма остатка отгрузки по элементу
        $summ_cnt = 0; // Счетчик - сколько раз считали сумму. Используется в условии
        $summ_numb_payed = 0; // Сумма оплаченных товаров
        $summ_total = 0; // Сумма количества по всем партиям

        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        // Формирование плана
        for ($i = 0; $i < $cnt; $i++) {
            $input = false; // Входящий

            $row = $rplan_rows[$i];

            // Договор
            $kod_dogovora = (int)$row['kod_dogovora']; // Код договора
            $nomer = $row['nomer']; // номер договора
            $kod_org = (int)$row['kod_org']; // Код организации (Заказчик)
            $nazv_krat = $row['nazv_krat']; // Название Заказчика
            $kod_part = $row['kod_part'];
            $zakryt = (int)$row['zakryt'];

            // Если заказчик НВС - то выводим исполнителя
            if ($kod_org == $kod_org_main) {
                $kod_org = $row['kod_ispolnit']; // Код исполнителя
                $nazv_krat = $row['ispolnit_nazv_krat'];
                $input = true;
            }

            $kod_elem = (int)$row['kod_elem']; // Код элемента
            $shifr = $row['shifr']; // Обозначение
            $modif = $row['modif']; // Модификация
            $numb = $row['numb'];   // Количество в партии
            $val = (int)$row['val']; // Валюта
            $nds = round((double)$row['nds'], 2); // НДС
            $data_postav = $row['data_postav']; // Дата поставки
            $price = Part::getPrice($row);
            $price_nds = round($price * (1 + $nds), 2); // Цена с НДС
            $part_summa = Part::getPartSumma($row); // Сумма остатка партии

            $modif_str = '';            // Модификация
            if ($modif != '')
                $modif_str = " ($modif)";

            // НДС
            $nds_str = '';
            if ($nds != 0.18)
                $nds_str = /** @lang HTML */
                    '<br>НДС ' . $nds * 100 . '%';

            // Валюта
            $val_str = ' ' . func::val_sign($val);

            // Процент оплаты
            $proc = self::getProcPay($kod_dogovora); // todo - Сравнить производительность - Ввести в запрос rplan или отдельно много запросов
            $proc_str = "";
            if ($proc > 0)
                $proc_str = "$proc%";

            if ($zebra == "#FFFFFF")
                $zebra = "#E6E6E6";
            else
                $zebra = "#FFFFFF";

            $ind_data = ""; // Индикатор даты

            // Остаток отгрузки/поcтупления
            $numb_ostat = $row['numb_ostat']; // Осталось отгрузить/получить (При получении используется другой запрос со структурой rplan)

            if ($proc > 0 and $numb_ostat > 0) {
                $days_rem = func::DaysRem($data_postav);
                if ($days_rem <= 14)
                    $ind_data = " bgcolor='#F18585'"; // Внимание - менее 2х недель
                elseif ($days_rem <= 30)
                    $ind_data = " bgcolor='#f4df42'"; // Менее 30-ти дней до отгрузки
            } elseif ($numb_ostat == 0)
                $zebra = "#85e085"; // Красим в зеленый - отгружено

            $otgruz_poluch = "отгрузить";
            if ($input)
                $otgruz_poluch = "получить";

            $numb_ostat_str = ""; // Количество которое осталось отгрузить/получить
            if ($numb_ostat != $numb and $numb_ostat > 0)
                $numb_ostat_str = " <abbr title=\"Осталось $otgruz_poluch $numb_ostat\">($numb_ostat)</abbr>";
            $itog_numb_ostat += $numb_ostat;
            $itog_summ += $part_summa;// Итоговая Сумма по всем партиям

            $part_summa_str = Func::Rub($part_summa);// Сумма партии

            // Если предыдущий элемент другой то создаем заголовок + Итоги
            if ($kod_elem != $kod_elem_pred) {
                if ($summ_total == $summ_numb_ostat)
                    $summ_total = "";
                if ($summ_numb_ostat == $summ_numb_payed)
                    $summ_numb_ostat = "";
                if ($summ_numb_payed == 0)
                    $summ_numb_payed = "";

                if ($summ_cnt > 1)
                    $res .= "<tr>
                                <td align='right'><b>Итого:</b></td>
                                <td align='right'>$summ_total <b><abbr title=\"Осталось $otgruz_poluch $summ_numb_ostat\">$summ_numb_ostat</abbr> <abbr title=\"Оплачено $summ_numb_payed\"><font color='#006400'>$summ_numb_payed</font></abbr></b></td><th colspan='6'></th></tr>";
                $res .= "<tr><th colspan='8' align='left' bgcolor='#faebd7'><a href='form_elem.php?kod_elem=$kod_elem'>$shifr</a></th></tr>";
                $summ_numb_ostat = 0;
                $summ_numb_payed = 0;
                $summ_total = 0;
                $summ_cnt = 0;
            }
            $kod_elem_pred = $kod_elem;

            if ($zakryt == 0 and $numb_ostat > 0) {
                $summ_numb_ostat += $numb_ostat;

                if ($proc > 0)
                    $summ_numb_payed += $numb_ostat;
            }
            $summ_total += $numb;

            $summ_cnt++;

            $form_part_link = "form_part.php?kod_part=$kod_part&kod_dogovora=$kod_dogovora";
            $form_dogovor_link = "form_dogovor.php?kod_dogovora=$kod_dogovora";

            // Красим строку
            if ($zakryt == 1) // Если договор закрыт
            {
                if ($numb_ostat == 0)
                    $zebra = "#85e085"; // Если все отгружено то красим в зеленый
                else
                    $zebra = "#F18585"; // Если не все отгружено то в красный
            }

            // Формируем строку плана
            $row_str = /** @lang HTML */
                "<tr bgcolor='$zebra'>
                                <td><a href='$form_part_link'>" . $shifr . $modif_str . "</a></td>
                                <td align='right'><a href='$form_part_link'>" . $numb . $numb_ostat_str . "</a></td>
                                <td align='right'><a href='$form_part_link'>" . $proc_str . "</a></td>
                                <td align='right'><a href='$form_dogovor_link'>" . $nomer . "</a></td>
                                <td><a href='form_org.php?kod_org=" . $kod_org . "'>" . $nazv_krat . "</td>
                                <td align='right' $ind_data>" . Func::Date_from_MySQL($data_postav) . "</td>
                                <td align='right'>" . func::Rub($price_nds) . "</td>
                                <td align='right'>" . $part_summa_str . $val_str . $nds_str . "</td>
                         </tr>";

            $res .= $row_str;

            if ($summ_cnt > 1 and $i == $cnt - 1) // Вывод итогов если последняя запись
            {
                if ($summ_total == $summ_numb_ostat)
                    $summ_total = "";
                if ($summ_numb_ostat == $summ_numb_payed)
                    $summ_numb_ostat = "";
                if ($summ_numb_payed == 0)
                    $summ_numb_payed = "";

                $res .= "<tr><td align='right'><b>Итого:</b></td>
                             <td align='right'>$summ_total <b><abbr title=\"Осталось $otgruz_poluch $summ_numb_ostat\">$summ_numb_ostat</abbr> <abbr title=\"Оплачено $summ_numb_payed\"><font color='#006400'>$summ_numb_payed</font></abbr></b></td><th colspan='6'></th></tr>";
            }
        }

        $res .= '</table>';

        // Выводим сумму по всем партиям
        $res .= "<br>Итого: " . Func::Rub($itog_summ) . "<br>";

        // Выводим количесто по всем партиям
        $res .= "<br>Итого отгрузить: $itog_numb_ostat<br>";

        return $res;
    }
//-------------------------------------------------------------------

    /**
     * Процент оплаты = Сумма платежей / Сумма договора
     * @param int $kod_dogovora - код договора
     * @return string
     */
    public static function getProcPay($kod_dogovora)
    {
        // Сумма платежей
        $summa_plat = self::getSummaPlat($kod_dogovora);

        if ($summa_plat == 0)
            return "0";

        // Сумма договора
        $dogovor_summa = self::getSummaDogovora($kod_dogovora);

        if ($dogovor_summa == 0)
            return "0";

        $p = 0.;
        if ($dogovor_summa > 0)
            $p = $summa_plat / $dogovor_summa;

        return Func::Proc($p);
    }
//-------------------------------------------------------------------
//

    /**
     * Форма - Платежи по договору
     * @param int $Del - 1 Кнопка удаления
     * @return string
     */
    public function formPlat($Del = 0)
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM plat WHERE kod_dogovora=$this->kod_dogovora AND plat.del=0");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = '<br>Платежные поручения<br>
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC"><td width="100">Сумма</td>
                        <td width="80">Номер ПП</td>
                        <td width="80">Дата</td>
                        <td>Примечание</td>
                    </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_plat = $row['kod_plat'];

            $del = "";
            if ($Del == 1)
                $del = func::ActButton2("", "Удалить", "DelPlat", "kod_plat_del", $kod_plat);

            $res .= '<tr>
                        <td>' . Func::Rub($row['summa']) . '</td>
                        <td>' . $row['nomer'] . $del . '</td>
                        <td>' . Func::Date_from_MySQL($row['data']) . '</td>
                        <td>' . $row['prim'] . '</td>
                    </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------
//

    /**
     * Договоры по Организации - Внешние и Поставка
     * @return string
     */
    public function formDocsByOrg()
    {
        $db = new Db();

        $res = func::ActButton2("", "Все Договоры", "dogovory_all", "dogovory_all", 1);
        if (!isset($_POST['dogovory_all'])) {
            $sql = "SELECT 
                * 
                FROM 
                    view_rplan 
                WHERE 
                    (kod_org=$this->kod_org OR kod_ispolnit=$this->kod_org) AND zakryt=0
                ORDER BY 
                kod_dogovora DESC,
                view_rplan.name ASC";

            $rows = $db->rows($sql);
            if (count($rows) > 0)
                return ($res . $this->formRPlan_by_Doc($rows));
        }

        $sql = "SELECT 
                * 
                FROM 
                    view_rplan 
                WHERE 
                    kod_org=$this->kod_org OR kod_ispolnit=$this->kod_org
                ORDER BY 
                kod_dogovora DESC,
                view_rplan.name ASC";

        $rows = $db->rows($sql);
        if (count($rows) > 0)
            return ($this->formRPlan_by_Doc($rows));

        return "Договоры без партий:<br>" . $this->formDocList(/** @lang SQL */
                "SELECT * FROM view_scheta_dogovory_all WHERE kod_org=$this->kod_org");

    }
//-----------------------------------------------------------------------
//

    /**
     * Договоры - Внешние или Поставка
     * @param int $VN
     * @return string
     */
    public function formDocsOpen($VN = 0)
    {
        $db = new Db();
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        $and = "kod_ispolnit=$kod_org_main";
        if ($VN == 1)
            $and = "kod_org=$kod_org_main";

        $sql = "SELECT 
                * 
                FROM 
                    view_rplan 
                WHERE 
                    view_rplan.zakryt = 0 AND $and
                ORDER BY 
                kod_dogovora DESC,
                view_rplan.name ASC";

        $rows = $db->rows($sql);


        if (count($rows) > 0)
            return $this->formRPlan_by_Doc($rows);

        return "Список пуст.";
    }

//-----------------------------------------------------------------------
//

    /**
     * Показать счета по Договору
     * @return string
     */
    public function formInvoice()
    {

        $db = new Db();

        $rows = $db->rows("SELECT * FROM scheta WHERE kod_dogovora=$this->kod_dogovora AND scheta.del=0");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = /** @lang HTML */
            '<br>Счета<br>
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                <tr bgcolor="#CCCCCC" >
                    <td width="60">Номер</td>
                    <td width="100">Сумма</td>
                    <td width="80">Дата</td>
                    <td>Примечание</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_dogovora = $row['kod_dogovora'];
            $kod_scheta = $row['kod_scheta'];
            $res .= "<tr>
                        <td><a target='_blank' href='form_invoice.php?kod_dogovora=$kod_dogovora&kod_scheta=$kod_scheta'>". $row['nomer'] . "</a><br>" . Func::ActButton2('', 'Удалить', 'DelInv', "kod_scheta_del", $row['kod_scheta']) . '</td>
                        <td>' . Func::Rub($row['summa']) . '</td>
                        <td>' . Func::Date_from_MySQL($row['data']) . '</td>
                        <td>' . $row['prim'] . '</td>
                    </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------
//
    public function formAttrebutesSelList($kod_attr_sel = 0)
    {
        $db = new Db();

        $rows = $db->rows("SELECT
                                    attributes.kod_attr,
                                    attributes.value,
                                    kod_type_attr
                                  FROM attributes
                                  WHERE attributes.del=0
                                  ORDER BY kod_type_attr ASC"
        );
        $cnt = $db->cnt;

        if ($db->cnt == 0)
            return "";

        $res = "<select id='kod_attr' name='kod_attr' placeholder=\"Выбрать элемент...\">";
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $kod_attr = $row['kod_attr'];

            $kod_type_attr = $this->getAttrType($row);

            $selected = "";
            $value = $row['value'];
            if ($kod_attr == $kod_attr_sel)
                $selected = " selected='selected'";

            $res .= "<option value='$kod_attr' $selected>$kod_type_attr - $value</option>\r\n";
        }
        $res .= '</select>
        <script type="text/javascript">
                        var kod_attr, $kod_attr;
    
                        $kod_attr = $("#kod_attr").selectize({
                            onChange: function(value) {
                                if (!value.length) return;
                            }
                        });
                        kod_attr = $kod_attr[0].selectize;
                </script>';

        return $res;
    }
//-----------------------------------------------------------------------
//
    /**
     * Вотзвращает значение типа Атрибута
     * @param $row
     * @return string
     */
    public static function getAttrType($row)
    {
        if ($row['kod_type_attr'] == 1)
            return ("ИГК");
        elseif ($row['kod_type_attr'] == 2)
            return ("Заказ");
        return ("Приемка");
    }

//-----------------------------------------------------------------------
//

    /**
     * Показать Атрибуты по Договору
     * @return string
     */
    public function formAttributes()
    {

        $db = new Db();

        $rows = $db->rows("SELECT 
                                    dogovor_attribute.kod_dogovor_attr,
                                    dogovor_attribute.kod_dogovora,
                                    dogovor_attribute.kod_attr,
                                    attributes.value,
                                    attributes.kod_type_attr
                                  FROM dogovor_attribute
                                  INNER JOIN attributes ON attributes.kod_attr=dogovor_attribute.kod_attr
                                  WHERE kod_dogovora=$this->kod_dogovora AND dogovor_attribute.del=0 AND attributes.del=0
                                  ORDER BY kod_type_attr ASC"
        );
        $cnt = $db->cnt;
        if (isset($_POST['formAddAttr'])) {
            $res = /** @lang HTML */
                '<form name="form1" method="post" action="">
                                      <table width="416" border="0">
                                        <tr>
                                          <td width="185">Аттрибут
                                            '.self::formAttrebuteTypeSelList().'
                                          </td>
                                          <td width="215">
                                            <input type="text" name="value" id="value">
                                          </td>
                                        </tr>
                                        <tr>
                                          <td>Выбрать:</td>
                                          <td>' . $this->formAttrebutesSelList() . '</td>
                                        </tr>
                                        <tr>
                                          <td><input type="submit" name="button" id="button" value="Добавить" /></td>
                                        <td></td>
                                        </tr>
                                      </table>
                                    <input type="hidden" name="AddAttr" value="1" /></form>';
            $res .= Func::Cansel();
        } else
            $res = func::ActButton2("", "Добавить Аттрибут", "1", "formAddAttr", "1");

        if ($cnt == 0)
            return $res;

        $res .= /** @lang HTML */
            '<br>Атрибуты Договора<br>
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                <tr bgcolor="#CCCCCC" >
                    <td width="60">Тип</td>
                    <td>Значение</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $kod_type_attr = $this->getAttrType($row);

            $res .= '<tr>
                        <td>' . $kod_type_attr . '<br>' . Func::ActButton2('', 'Удалить', 'DelAttr', "kod_attr_del", $row['kod_dogovor_attr']) . '</td>
                        <td>' . $row['value'] . '</td>
                    </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------
//

    /**
     * Форма - Примечание договора
     * @param int $AddForm
     * @param int $Del
     * @return string
     */
    public function formPrim($AddForm = 0, $Del = 0)
    {
        $db = new Db();
        // todo - проверить будет ли работать запрос в отсутствии поля kod_part
        $rows = $db->rows("SELECT * 
                                  FROM dogovor_prim 
                                  WHERE kod_dogovora=$this->kod_dogovora AND dogovor_prim.del=0 AND isnull(kod_part)
                                  ORDER BY dogovor_prim.time_stamp DESC
                                  ");

        $cnt = $db->cnt;
        $res = "";
        if ($AddForm == 1) {
            $res = '<form id="form1" name="form1" method="post" action="">
                                      <table width="416" border="0">
                                        <tr>
                                          <td width="185">Примечание</td>
                                          <td width="215"><span id="sprytextfield1">
                                            <textarea name="Prim" id="Prim" cols="70" rows="3"></textarea>
                                          <span class="textfieldRequiredMsg">Необходимо ввести значение.</span></span></td>
                                        </tr>
                                        <tr>
                                          <td><input type="submit" name="button" id="button" value="Добавить" /></td>
                                        <td>&nbsp;</td>
                                        </tr>
                                      </table>
                                    <input type="hidden" name="AddPrim" value="1" />
                    </form>';
            $res .= Func::Cansel();
        }

        if ($cnt == 0)
            return $res;

        // Формируем таблицу
        $res .= 'Примечание
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC" >
                    <td width="80">Дата</td>
                    <td>Текст</td>';

        // Заполняем данными
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $user = "";
            if ($row['user'] != "")
                $user = "<br>" . $row['user'];

            $kod_prim = $row['kod_prim'];

            $btn_del = "";
            if ($Del == 1) {
                $btn_del = func::ActButton2("", "Удалить", "DelPrim", "kod_prim_del", $kod_prim);
            }

            $res .= /** @lang HTML */
                '<tr>
                        <td>' . Func::Date_from_MySQL($row['time_stamp']) . $user . $btn_del . '</td>
                        <td>' . $row['text'] . '</td>
                     </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------

    /**
     * Контакты по договору. Проверить
     * @param int $AddPh - вывод формы добавления телефона 0-не выводить, 1-выводить
     */
    public function formDocKontakts($AddPh = 0)
    {
        $c = new Kontakt();
        $c->kod_dogovora = $this->kod_dogovora;
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        // Если организация NVS
        if ($this->kod_org != $kod_org_main)
            $c->kod_org = $this->kod_org;
        else
            $c->kod_org = $this->Data['kod_ispolnit'];

        // Показать контакты
        echo $c->formKontakts($AddPh, "Doc");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Документы по Договору. Проверить
     * @return string
     */
    public function formDocum()
    {
        return Docum::formDocum('Doc', $this->kod_dogovora);
    }
//-----------------------------------------------------------------------

    /**
     * Список Всех договоров и вложенных счетов
     * @param string $sql - запрос
     * @return string
     */
    public function formDocList($sql = "")
    {
        $db = new Db();
        if ($sql == "")
            $sql = "SELECT * FROM view_scheta_dogovory_all";

        $rows = $db->rows($sql);

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        //$res = 'Количество Записей: ' . $cnt . '<br>';
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        $res = '<table border=0 cellspacing=0 cellpadding=0 width="50%">';
        $res .= '<tr bgcolor="#CCCCCC">
                    <td>Номер Договора</td>
                    <td>Организация</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {

            $row = $rows[$i];

            if ($row['kod_org'] == $kod_org_main)
                $res .= '<tr bgcolor="#8fe8a1">
                            <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer'] . '</a></td>
                            <td width="80%"><a href="form_org.php?kod_org=' . $row['kod_ispolnit'] . '">' . $row['ispolnit_nazv_krat'] . '</a></td>
                        </tr>';
            else
                $res .= '<tr>
                            <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer'] . '</a></td>
                            <td width="80%"><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                        </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------

    /**
     * Список всех платежей.
     * @return string - таблица всех введенных платежей
     */
    public function formAllPays()
    {

        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_plat ORDER BY view_plat.data DESC ");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return '';


        $summ = 0; // Сумма по месяцу

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        $res .= '<tr bgcolor="#CCCCCC" >
                    <td width="60">Номер ПП</td>
                    <td width="100">Сумма</td>
                    <td width="80">Дата</td>
                    <td width="100">Распределено</td>
                    <td width="130">Договор</td>
                    <td  width="220">Организация</td>
                    <td>Примечание</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {

            // Используется отсортированный массив по Дате
            $pm = '-';
            if ($i > 1) {

                $row = $rows[$i - 1];

                $d = Func::Date_from_MySQL($row['data']);

                if ($d <> '-') {
                    $m = explode('.', $d);
                    $pm = $m[1]; // Предыдущий месяц
                }
            }

            $row = $rows[$i];

            $d = Func::Date_from_MySQL($row['data']); // Дата

            $cm = '-';
            $cy = '-';
            if ($d != '-') {
                $m = explode('.', $d);
                if (count($m) > 2) {
                    $cm = $m[1];
                    $cy = $m[2];
                }
            }


            if ($i > 1) {
                // Если текущее значение месяца не равно значению на пред. шаге
                // то выводим заголовок и начинаем таблицу заново
                if ($cm != $pm) {

                    $res .= '<br><h1>Сумма: ' . Func::Rub($summ) . '</h1><br>';
                    $res .= '</table><br><h1>' . $cm . '.' . $cy . '</h1>
                                  <table border=1 cellspacing=0 cellpadding=0 width="100%">
                                  <tr bgcolor="#CCCCCC" ><td width="60">Номер ПП</td>
                                    <td width="100">Сумма</td>
                                    <td width="80">Дата</td>
                                    <td width="100">Распределено</td>
                                    <td width="130">Договор</td>
                                    <td width="220">Организация</td>
                                    <td>Примечание</td>
                                  </tr>';
                    $summ = 0.;

                }

            }


            if ($i == 1)
                $res .= '<br><h1>' . $cm . '.' . $cy . '</h1>';

            // Процент распределения платежа
            $prs = 0;
            if ($row['summa'] != 0)
                $prs = Func::Proc($row['summa_raspred'] / $row['summa']);

            // Если процент не равен 100 то красим ячейку
            if ($prs != 100)
                $col = 'bgcolor="#FFFF99"';
            else
                $col = '';

            $res .= '<tr><td>' . $row['nomer'] . '</td>
                          <td  align="right">' . Func::Rub($row['summa']) . '</td>
                          <td  align="center">' . $d . '</td>
                          <td ' . $col . '><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $prs . '%</a></td>
                          <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer_dogovora'] . '</a></td>
                          <td>' . $row['nazv_krat'] . '</td>
                          <td>' . $row['prim'] . '</td>
                        </tr>';

            $summ += $row['summa'];
        }

        $res .= '</table>';

        return $res;
    }
    //-----------------------------------------------------------------------

    /**
     * Список платежей в выбранном месяце.
     * @param int $Month - месяц текущего года
     * @return string - таблица платежей
     */
    public function formCurrentMonthPays($Month = 0)
    {

        $start_data = date('Y-m-01');

        if ($Month > 0)
            $start_data = date("Y-$Month-01");

        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_plat WHERE data >= '$start_data' ORDER BY view_plat.data DESC");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return '';


        $summ = 0; // Сумма по месяцу

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        $res .= '<tr bgcolor="#CCCCCC" >
                    <td width="60">Номер ПП</td>
                    <td width="100">Сумма</td>
                    <td width="80">Дата</td>
                    <td width="100">Распределено</td>
                    <td width="130">Договор</td>
                    <td  width="220">Организация</td>
                    <td>Примечание</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {

            $row = $rows[$i];

            $d = Func::Date_from_MySQL($row['data']); // Дата

            // Процент распределения платежа
            $prs = 0;
            if ($row['summa'] != 0)
                $prs = Func::Proc($row['summa_raspred'] / $row['summa']);

            // Если процент не равен 100 то красим ячейку
            if ($prs != 100)
                $col = 'bgcolor="#FFFF99"';
            else
                $col = '';

            $res .= '<tr><td>' . $row['nomer'] . '</td>
                          <td  align="right">' . Func::Rub($row['summa']) . '</td>
                          <td  align="center">' . $d . '</td>
                          <td ' . $col . '><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $prs . '%</a></td>
                          <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer_dogovora'] . '</a></td>
                          <td>' . $row['nazv_krat'] . '</td>
                          <td>' . $row['prim'] . '</td>
                        </tr>';

            $summ += $row['summa'];
        }

        $res .= '</table>';

        if ($Month > 0)
            $month = date("$Month.Y");
        else
            $month = date("m.Y");

        $res = "<h1>$month<br>Сумма: " . func::Rub($summ) . "</h1>" . $res;

        return $res;
    }
//-----------------------------------------------------------------------
//

    /**
     * Выпадающий список платежей. Пока только рубли
     * @param string $Action
     * @param string $Body
     * @return string
     */
    public function formPaySelList($Action = '', $Body = '')
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_plat WHERE kod_dogovora =$this->kod_dogovora ORDER BY view_plat.data DESC");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = "<form action=' $Action  ' method='post'>
                <table>
                    <tr>
                        <td>Платежи</td>
                        <td><select name='kod_plat' id='kod_plat'>";

        $sell_list_empty = true;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Данные
            $summa = (double)$row['summa']; // Сумма платежа
            $summa_raspred = (double)$row['summa_raspred']; // Сумма которая уже распределена
            $kod_plat = (int)$row['kod_plat']; // Код платежа
            $nomer = $row['nomer']; // Номер платежа
            $data = Func::Date_from_MySQL($row['data']); // Дата платежа
            $ostat = $summa - $summa_raspred; // Остаток платежа который можно распределить

            if (($ostat) > 0) {
                $ostat_str = func::Rub($ostat) . " р"; // todo - сделать универсально для всех валют
                $res .= "<option value=$kod_plat>ПП №$nomer от $data - $ostat_str </option>";
                $sell_list_empty = false;
            }
        }

        if ($sell_list_empty) // Если список пустой - возвращаем пустую строку.
            return "";

        $res .= '</select></td>
                </tr>
                </table>' . $Body . '
                <br><input type="submit" name="Submit" value="Добавить" />
                </form>';
        return $res;
    }
//--------------------------------------------------------------
//

    /**
     * Форма - История по складу
     * @param int $dolg - Если 1 то возвращает только неоплаченные накладные
     * @return string
     */
    public function formSGPHistory($dolg = 0)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_sklad");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return '';

        $res = /** @lang HTML */
            '<table width="100%" border="0">
                    <tr bgcolor="#CCCCCC">
                    <td width="40%">Наименование</td>
                    <td width="10%">Номер Договора</td>
                    <td width="20%">Организация</td>
                    <td width="5%">Кол-во</td>
                    <td width="10%">Накладная</td>
                    <td width="10%">Дата</td>
                    <td width="10%">Оператор</td>
                    <td width="10%">Процент</td>
                   </tr>';

        for ($i = 0; $i < $cnt; $i++) {

            $row = $rows[$i];

            $procent = 0.;

            if ((double)$row['dogovor_summa'] > 0 && (double)$row['summa_plat'] > 0)
                $procent = $row['summa_plat'] / (double)$row['dogovor_summa'];

            if ($dolg == 1 and $procent >= 0.98)
                continue;

            if ($procent >= 0.98)
                $res .= '<tr>';
            else
                $res .= '<tr bgcolor="#d8d210">';

            $res .= '
                    <td><a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '">' . $row['name'] . '</a></td>
                    <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer'] . '</a></td>
                    <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</td>
                    <td >' . (int)$row['numb'] . '</td>
                    <td>' . $row['naklad'] . '</td>
                    <td>' . Func::Date_from_MySQL($row['data']) . '</td>
                    <td>' . $row['oper'] . '</td>
                    <td align="right">' . Func::Proc($procent) . '%</td>
                </tr>';

        }

        $res .= '</table>';

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Создает копию договора
     * @return int
     */
    public function Copy()
    {
        $db = new Db();
        $kod_dogovora = $this->kod_dogovora;
        $kod_user = func::kod_user();
        $db->query(/** @lang SQL */
            "INSERT INTO dogovory (nomer, data_sost, kod_org, kod_ispolnit, kod_gruzopoluchat, kod_user) 
                      SELECT CONCAT(dogovory.nomer,' copy'),NOW(),kod_org,kod_ispolnit,kod_gruzopoluchat,$kod_user 
                      FROM dogovory
                      WHERE kod_dogovora=$kod_dogovora");
        $kod_dogovora_new = $db->last_id;

        $db->query(/** @lang SQL */
            "INSERT INTO parts (kod_elem, modif, numb, data_postav, price, price_or, kod_dogovora, val, nds, kod_user) 
                SELECT kod_elem, modif, numb, NOW(), price, price_or, $kod_dogovora_new, val, nds, $kod_user 
                FROM parts
                WHERE kod_dogovora=$kod_dogovora");

        return $kod_dogovora_new;
    }
//----------------------------------------------------------------------------------------------------------------------
//

    /**
     * Добавить Договор
     * @param $nomer
     * @param $data_sost
     * @param int $kod_org - Код организации Заказчика
     * @param int $kod_ispolnit
     * @internal param int $VN - Внешний, Заказчик - НВС, Исполнитель - Код организации
     * @internal param $Num - номер
     * @internal param $DataCR - Дата создания договора
     * @internal param $Priem - Приемка, не актуально
     * @return int
     */
    public function Add($nomer, $data_sost, $kod_org, $kod_ispolnit)
    {
        $db = new Db();

        $data_sost = func::Date_to_MySQL($data_sost);
        $kod_user = func::kod_user();
        if (!isset($kod_ispolnit)) {
            $conf = new config();
            $kod_ispolnit = $conf->kod_org_main;
        }


        $sql = "INSERT INTO dogovory (nomer,data_sost,kod_org,kod_ispolnit,kod_user) VALUES('$nomer','$data_sost',$kod_org,$kod_ispolnit,$kod_user)";

        $db->query($sql);

        return $db->last_id;
    }
//--------------------------------------------------------------

    /**
     * Обработчик событий
     *
     */
    public function Events()
    {
        $event = false;
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;

        if (isset($_POST['formAdd'])) {
            if (isset($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['doc_type'])) {
                $kod_ispolnit = $kod_org_main; // НВС - Поставщик
                $kod_org = $_POST['kod_org']; // Заказчик

                if ($_POST['doc_type'] == "zakup") {
                    $kod_ispolnit = $_POST['kod_org']; // Поставщик
                    $kod_org = $kod_org_main; // НВС - Заказчик
                }
                $kod_dogovora = $this->Add($_POST['nomer'], $_POST['data_sost'], $kod_org, $kod_ispolnit);
                // переходим в форму договору
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_dogovor.php?kod_dogovora=' . $kod_dogovora);
                return;
            } elseif (isset($_POST['famil'], $_POST['name'])) {
                $this->AddKontakt($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
                $event = true;
            }
        } elseif (isset($_POST['formAddPP'])) {
            if (isset($_POST['nomer'], $_POST['summa'], $_POST['data'])) {
                $this->AddPay($_POST['nomer'], $_POST['summa'], $_POST['data'], $_POST['prim']);
                $event = true;
            }
        } elseif (isset($_POST['AddInv'])) {
            if (isset($_POST['InvNum']) and isset($_POST['InvSumm']) and isset($_POST['InvDate'])) {
                $this->AddInvoice($_POST['InvNum'], $_POST['InvSumm'], $_POST['InvDate'], $_POST['InvPrim']);
                $event = true;
            }
        } elseif (isset($_POST['AddPrim'])) {
            if (isset($_POST['Prim'])) {
                $this->AddPrim($_POST['Prim']);
                $event = true;
            }
        } elseif (isset($_POST['AddAttr'])) {
            if (isset($_POST['value']) or isset($_POST['kod_attr'])) {
                $this->AddAttribute($_POST['kod_attr'], $_POST['value'], $_POST['kod_type_attr']);
            }
        } elseif (isset($_POST['formEdit'])) {
            if (isset($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['doc_type'])) {

                $kod_ispolnit = $kod_org_main; // НВС - Поставщик
                $kod_org = $_POST['kod_org']; // Заказчик

                if ($_POST['doc_type'] == "zakup") {
                    $kod_ispolnit = $_POST['kod_org']; // Поставщик
                    $kod_org = $kod_org_main; // НВС - Заказчик
                }

                $this->Edit($_POST['nomer'], $_POST['data_sost'], $kod_org, $kod_ispolnit);
                $event = true;
            }
        } elseif (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == 'DelInv' and isset($_POST['kod_scheta_del'])) {
                $this->DelInvoice($_POST['kod_scheta_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'DocOpen') {
                $this->Close(0);
                $event = true;
            } elseif ($_POST['Flag'] == 'DocCloseConf') {
                $this->Close();
                $event = true;
            } elseif ($_POST['Flag'] == 'DelPlat' and isset($_POST['kod_plat_del'])) {
                $this->DelPlat($_POST['kod_plat_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'DelPrim') {
                $this->DelPrim($_POST['kod_prim_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'copyDogovor') {
                $kod_dogovora = $this->Copy();
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_dogovor.php?kod_dogovora=' . $kod_dogovora);
                return;
            } elseif ($_POST['Flag'] == 'DelAttr' and isset($_POST['kod_attr_del'])) {
                $this->DelAttr($_POST['kod_attr_del']);
                $event = true;
            }
        }

        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление платежа в договор
     * @param $nomer - номер
     * @param $summa - сумма
     * @param $data - дата
     * @param $prim - примечание
     */
    public function AddPay($nomer, $summa, $data, $prim)
    {
        $db = new Db();
        $kod_dogovora = $this->kod_dogovora;
        $data = func::Date_to_MySQL($data);

        $user = func::user();
        $kod_user = func::kod_user();

        $db->query("INSERT INTO plat (kod_dogovora,nomer,summa,data,prim,user,kod_user) VALUES($kod_dogovora,$nomer,$summa,'$data','$prim','$user',$kod_user)");

        // Информирование по e-mail
        $mail = new Mail();
        $data = $this->getData($kod_dogovora);
        $dog_nomer = $data['nomer'];
        $kod_org = $data['kod_org'];
        $nazv_krat = $data['nazv_krat'];
        $summa_str = func::Rub($summa);
        $host = $_SERVER['HTTP_HOST'];
        $body = "<a href='http://$host/form_dogovor.php?kod_dogovora=$kod_dogovora'>№$dog_nomer</a><br>";
        $body .= "<a href='http://$host/form_org.php?kod_org=$kod_org'>$nazv_krat</a><br>";
        $body .= "Сумма: $summa_str<br>";
        $body .= "Примечание: $prim";
        $mail->send_mail($body, "Оплата: $dog_nomer - $nazv_krat - $summa_str");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление счета в договор
     * @param $nomer
     * @param $summa
     * @param $data
     * @param string $prim
     * @internal param string $PayDate
     */
    public function AddInvoice($nomer, $summa, $data, $prim = '-')
    {
        $kod_dogovora = $this->kod_dogovora;

        if (!isset($prim)) $prim = '-';

        $db = new Db();
        $data = func::Date_to_MySQL($data);
        $kod_user = func::kod_user();

        $db->query("INSERT INTO scheta (kod_dogovora,nomer,summa,data,prim,kod_user) VALUES($kod_dogovora,'$nomer',$summa,'$data','$prim',$kod_user)");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление атрибута в договор
     * @param int $kod_attr
     * @param string $value
     * @param int $kod_type_attr
     */
    public function AddAttribute($kod_attr = 0, $value = "", $kod_type_attr = 1)
    {
        $kod_dogovora = $this->kod_dogovora;

        if ($value == "" and $kod_attr == 0) return;

        $db = new Db();
        $kod_user = func::kod_user();

        if ($value != "") {
            $db->query("INSERT INTO attributes (value, kod_type_attr,kod_user) VALUES($value,$kod_type_attr,$kod_user)");
            $kod_attr = $db->last_id;
            $db->query("INSERT INTO dogovor_attribute (kod_dogovora,kod_attr,kod_user) VALUES($kod_dogovora,$kod_attr,$kod_user)");
        }
        else
            $db->query("INSERT INTO dogovor_attribute (kod_dogovora,kod_attr,kod_user) VALUES($kod_dogovora,$kod_attr,$kod_user)");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление контакта в договор
     * @param $dolg
     * @param $famil
     * @param $name
     * @param $otch
     */
    public function AddKontakt($dolg, $famil, $name, $otch)
    {
        $kontakt = new Kontakt();
        $kontakt->kod_dogovora = $this->kod_dogovora;

        $kod_org = $this->kod_org;
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        if ($kod_org == $kod_org_main) {
            $this->getData();
            $kod_org = $this->Data['kod_ispolnit'];
        }

        $kontakt->kod_org = $kod_org;
        $kontakt->AddKontakt($dolg, $famil, $name, $otch);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавить примечание
     * @param $text
     */
    public function AddPrim($text)
    {
        if (strlen($text) < 4) {
            echo "Err: Слишком короткое примечание. Должно быть не менее 4-х символов.";
            return;
        }

        $P = nl2br($text); // Вставялем <br> вместо перевода строки
        $user = func::user();
        $kod_user = func::kod_user();

        $kod_part = "NULL";
        if (isset($_POST['kod_part']))
            $kod_part = $_POST['kod_part'];

        $db = new Db();
        $db->query("INSERT INTO dogovor_prim (kod_dogovora,text,user,kod_user,kod_part) VALUES($this->kod_dogovora,'$P','$user',$kod_user,$kod_part)");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление платежа
     * @param $kod_plat
     * @internal param int $kod_prim
     * @internal param $kod_scheta
     */
    public function DelPlat($kod_plat)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        if (isset($kod_plat)) {
            $db->query("UPDATE plat SET del=1,kod_user=$kod_user WHERE kod_plat=$kod_plat");

            $db->query("UPDATE raschety_plat SET del=1,kod_user=$kod_user WHERE kod_plat=$kod_plat");

        } else
            echo "Ошибка: Не задан ID платежа";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление примечания
     * @param $kod_dogovor_attr
     */
    public function DelAttr($kod_dogovor_attr)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        if (isset($kod_dogovor_attr)) {
            $db->query("UPDATE dogovor_attribute SET del=1,kod_user=$kod_user WHERE kod_dogovor_attr=$kod_dogovor_attr");
        } else
            echo "Ошибка: Не задан ID";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление примечания
     * @param int $kod_prim
     */
    public function DelPrim($kod_prim)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        if (isset($kod_prim)) {
            $db->query("UPDATE dogovor_prim SET del=1,kod_user=$kod_user WHERE kod_prim=$kod_prim");

        } else
            echo "Ошибка: Не задан ID примечания";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Сохранить Изменения Договора
     * @param $nomer - номер
     * @param $data_sost - дата
     * @param int $kod_org - код заказчика
     * @param $kod_ispolnit - код исполнителя
     */
    public function Edit($nomer, $data_sost, $kod_org, $kod_ispolnit = 0)
    {
        $data_sost = func::Date_to_MySQL($data_sost);
        $db = new Db();
        $kod_user = func::kod_user();
        if ($kod_ispolnit == 0) {
            $conf = new config();
            $kod_ispolnit = $conf->kod_org_main;
        }

        $db->query("UPDATE dogovory SET nomer = '$nomer', data_sost='$data_sost', kod_org=$kod_org, kod_ispolnit=$kod_ispolnit, kod_user=$kod_user WHERE kod_dogovora=$this->kod_dogovora");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление счета
     * @param int $kod_scheta
     */
    public function DelInvoice($kod_scheta)
    {
        $db = new Db();

        if (isset($kod_scheta)) {
            $kod_user = func::kod_user();

            $db->query("UPDATE scheta SET del=1,kod_user=$kod_user WHERE kod_scheta=$kod_scheta");

        } else
            echo "Ошибка: Не задан ID Счета";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Закрытие договора или отмена закрытия
     * @param int $zakryt - 1-Закрыть, 0 - отмена закрытия
     */
    public function Close($zakryt = 1)
    {
        $db = new Db();

        $now = date('y.m.d');
        $kod_user = func::kod_user();

        $db->query("UPDATE dogovory SET zakryt = $zakryt, data_zakrytiya='$now', edit=1, kod_user=$kod_user WHERE kod_dogovora=$this->kod_dogovora");
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Форма вставки подчиненного счета
     * @return string
     */
    public function formAddSchet()
    {
        $nomer = $this->getNextSchetNomer();
        $summa = self::getSummaDogovora($this->kod_dogovora);
        $this->getData();
        $prim = "Оплата по договору/счету №".$this->Data['nomer']." от ".func::Date_from_MySQL($this->Data['data_sost']);
        $res = /** @lang HTML */
            '<form id="form1" name="form1" method="post" action="">
                              <table width="434" border="0">
                                <tr>
                                  <td width="126">Номер Счета</td>
                                  <td width="292"><span id="SNumR">
                                  <input  name="InvNum" id="InvNum" value="'.$nomer.'" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldMinCharsMsg">Minimum
                                  number of characters not met.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Дата</td>
                                  <td><span id="SDateR">
                                  <input  name="InvDate" id="InvDate" value="' . date('d.m.Y') . '" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                                </tr>
                                <tr>
                                  <td>Сумма</td>
                                  <td><span id="SSummR">
                                  <input  name="InvSumm" id="InvSumm" value="'.$summa.'" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid
                                  format.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Примечание</td>
                                  <td><span id="STextNR">
                                    <input  name="InvPrim" id="InvPrim" value="'.$prim.'" />
                                      <span class="textfieldRequiredMsg">Необходимо ввести значение.</span></span></td>
                                  </span></td>
                                </tr>
                              </table>
                              <p>
                                <input type="submit" name="button" id="button" value="Добавить" />
                                <input type="reset" name="button" id="button" value="Сброс" />
                                <input type="hidden" name="AddInv" id="button" value="1" />
                              </p>
                            </form>';
        $res .= func::Cansel();
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Форма - добавление платежного поручения
     * @return string
     */
    public function formAddPP()
    {
        $date = date('d.m.Y');
        $res = /** @lang HTML */
            "               <form id='form1' name='form1' method='post' action=''>
                                  <table width='434' border='0'>
                                    <tr>
                                      <td width='126'>Номер ПП</td>
                                      <td width='292'><span id='SNumR'>
                                      <input name='nomer' id='nomer' />
                                      <span class='textfieldRequiredMsg'>A value is required.</span><span class='textfieldMinCharsMsg'>Minimum
                                      number of characters not met.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Дата</td>
                                      <td><span id='SDateR'>
                                      <input name='data' id='data' value='$date'/>
                                      <span class='textfieldRequiredMsg'>A value is required.</span><span class='textfieldInvalidFormatMsg'>Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Сумма</td>
                                      <td><span id='SSummR'>
                                      <input name='summa' id='summa' />
                                      <span class='textfieldRequiredMsg'>A value is required.</span><span class='textfieldInvalidFormatMsg'>Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Примечание</td>
                                      <td><span id='STextNR'>
                                        <input name='prim' id='prim' />
                                      </span></td>
                                    </tr>
                                  </table>
                                  <p>
                                    <input type='submit' name='button' id='button' value='Добавить' />
                                    <input type='hidden' name='formAddPP' value='formAddPP' />
                                  </p>
                                </form>";
        $res .= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');
        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Ссылка на форму договора вида - "НомерДоговора от дд.мм.гггг НазваниеКомпании"
     * @param int $kod_dogovora
     * @param bool $show_org
     * @return string
     */
    public function getFormLink($kod_dogovora = -1, $show_org = true)
    {
        if ($kod_dogovora == -1)
            $kod_dogovora = $this->kod_dogovora;

        $this->getData($kod_dogovora);
        $Org = new Org();
        $Org->kod_org = $this->Data['kod_org'];
        $name = '№' . $this->Data['nomer'] . ' от ' . func::Date_from_MySQL($this->Data['data_sost']);

        if ($show_org)
            $name .= ' ' . $Org->getFormLink();

        return "<a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$name</a>";
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Если в договоре нет контакта - выдает значек
     * @param $kod_dogovora
     * @return string
     */
    public static function formNoContact($kod_dogovora)
    {
        $db = new Db();
        $db->rows("SELECT * FROM view_kontakty_dogovora WHERE kod_dogovora=$kod_dogovora");

        if ($db->cnt == 0)
            return "<img src='img/sign_pr.gif' width='15'>";

        return "";
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * @param int $kod_elem_sub
     * @return string
     */
    public function formProduction($kod_elem_sub = 1002)
    {
        $conf = new config();
        $kod_org_main = $conf->kod_org_main;
        $sql = /** @lang SQL */
            "SELECT
                      view_rplan.kod_dogovora,
                      view_rplan.nomer,
                      view_rplan.kod_org,
                      view_rplan.nazv_krat,
                      view_rplan.modif,
                      view_rplan.numb,
                      view_rplan.data_postav,
                      view_rplan.nds,
                      view_rplan.part_summa,
                      view_rplan.val,
                      view_rplan.price,
                      view_rplan.price_or,
                      view_rplan.kod_elem,
                      view_rplan.obozn,
                      view_rplan.shifr,
                      view_rplan.kod_part,
                      view_rplan.zakryt,
                      view_rplan.kod_ispolnit,
                      view_rplan.name,
                      view_rplan.ispolnit_nazv_krat,
                      view_rplan.numb_otgruz,
                      view_rplan.numb_ostat
                    FROM
                      view_rplan
                      INNER JOIN specs ON kod_elem_base=kod_elem
                    WHERE
                      view_rplan.kod_org<>$kod_org_main AND zakryt<>1 AND numb_ostat>0 AND kod_elem_sub=$kod_elem_sub AND specs.del=0
                    ORDER BY
                      shifr ASC,
                      numb DESC";

        $db = new Db();
        $rows = $db->rows($sql); // Массив данных

        return $this->formRPlan_by_Elem($rows);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Пользователь, кто ввел/редактировал договор
     */
    public function getUser()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT kod_user FROM dogovory WHERE kod_dogovora=$this->kod_dogovora");
        if ($db->cnt == 0)
            return ("unset");

        $kod_user = $rows[0]['kod_user'];
        $rows = $db->rows(/** @lang MySQL */
            "SELECT famil FROM users WHERE kod_user=$kod_user AND kod_user!=1");

        if ($db->cnt == 0)
            return ("");

        $res = $rows[0]['famil'];
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//

    /**
     * @return string
     */
    public function formAttrebuteTypeSelList()
    {
        $res = "<select name='kod_type_attr'>
                    <option value='1' selected>ИГК</option>
                    <option value='2' selected>Заказ</option>
                    <option value='3' selected>Приемка</option>
                </select>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * @return string
     */
    public function getNextSchetNomer()
    {
        $db = new Db();
        $year = date('Y');
        $rows = $db->rows("SELECT MAX(nomer) AS nomer_max
                                    FROM view_scheta_dogovory_all
                                    WHERE data_sost>'$year-01-01'");
        if($db->cnt==0)
            return "1/".date("y");

        if(explode('-',$rows[0]['nomer_max']))
        {
            $nomer = (int)explode('/',$rows[0]['nomer_max'])[0]+1;
            $nomer.=date("y");
        }

        return $nomer;
    }
}// END CLASS