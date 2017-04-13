<?php
include_once('class_part.php');
include_once('class_kont.php');
include_once('class_org.php');
include_once('class_docum.php');
include_once('class_db.php');

// Класс Договор
class Doc
{
    public $Data;   // строка запроса - row['']

    // Поля
    public $kod_dogovora = '';  // код_договора
    public $nomer = '';         // номер договора
    public $kod_org = '';       // код_организации
    public $nazv_krat = '';     // Из расшиернного запроса

//--------------------------------------------------------------
    public function __construct($kod_dogovora=-1)
    {
        //$this->getData($kod_dogovora);
    }

//--------------------------------------------------------------
//

    /**
     * Показать Партии по Элементу - только партии, для просмотра договора надо в него перейти
     * @param $kod_elem
     * @return string
     */
    static public function formDocByElem($kod_elem)
    {
        $db = new Db();

        $rows = $db->rows(" SELECT
                                      *
                                    FROM view_rplan
                                    WHERE kod_elem=$kod_elem
                                    ORDER BY kod_dogovora DESC"); // Код договора по убыванию

        return Doc::formRPlan_by_Doc($rows);
    }
//-----------------------------------------------------------------

    /**
     * Договоры - основной формат вывода
     * Группировка строк rplan по договорам
     * На вход должен подаватья rplan отсотированный по коду договора!
     * @param $rplan_rows
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
                    <th>Договор</th>
                    <th>Организация</th>
                    <th>Наименование</th>
                    <th>Кол-во</th>
                    <th>Дата поставки</th>
                    <th>Цена с НДС</th>
                    <th>Сумма</th>
                    <th>Оплачено</th>
                  </tr>";

        for ($i = 0; $i < $cnt; $i++) { //
            // todo - Проверить пропуски
            $buffer = self::getDocBuffer($rplan_rows,$i);

                // Записываем буфер
                if(count($buffer)>0)
                {
                    $zakryt = (int)$buffer[0]['zakryt'];
                    $kod_ispolnit = (int)$buffer[0]['kod_ispolnit'];

                    $rplan_row = Doc::getRPlan_Row($buffer);

                    // Внешние договоры
                    if ($kod_ispolnit != 683) {
                        if ($zakryt == 1) // Внешний закрытый
                            $dogovor_vnesh_zakryt .= $rplan_row;
                    // Внешний действующий
                        else
                            $dogovor_vnesh .= $rplan_row;
                    }
                    // Действующий договор
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
// вывод партий Договора

    /**
     * @param $rplan_rows
     * @param $i - внешний счетчик
     * @return array
     */
    private function getDocBuffer($rplan_rows, &$i)
    {
        $buffer = array();

        $cnt = count($rplan_rows);
        if($cnt==0)
            return $buffer;

        $kod_dogovora = $rplan_rows[$i]['kod_dogovora'];

        for(;$i < $cnt; $i++)
        {
            $row = $rplan_rows[$i];
            if($row['kod_dogovora']==$kod_dogovora)
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
     * @param $rplan_rows array Строки rplan
     * @return string
     */
    public static function getRPlan_Row($rplan_rows)
    {
        $cnt = count($rplan_rows);

        if($cnt==0)
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

        // Процент оплаты по договору
        $oplacheno = "";
        $dogovor_summa = self::getSummaDogovora($kod_dogovora); // todo - медленные запросы, надо подумать как их ускорить.
        $summa_plat = self::getSummaPlat($kod_dogovora);        // todo - медленные запросы, надо подумать как их ускорить.
        if ((double)$dogovor_summa > 0 and (double)$summa_plat > 0)
            $oplacheno = (int)((double)$summa_plat / (double)$dogovor_summa * 100) . "%"; // Процент оплаты


        for ($i = 0; $i < $cnt; $i++) {
            $row = $rplan_rows[$i];

            // Данны по Партии
            // Партия
            $kod_part = (int)$row['kod_part']; // Код партии
            $kod_elem = (int)$row['kod_elem']; // Код элемента
            $obozn = $row['obozn']; // Обозначение
            $mod = $row['modif']; // Модификация
            $numb = (int)$row['numb']; // Количество
            $numb_ostat = (int)$row['numb_ostat'];
            $data = Func::Date_from_MySQL($row['data_postav']); // Дата поставки
            $price = round((double)$row['price'], 2); // Цена
            $val = ""; // Валюта
            $price_nds = round($price * (1 + (double)$row['nds']), 2); // Цена с НДС
            $part_summa = round((double)$row['part_summa'], 2); // Сумма партии
            $nds = ""; // НДС

            // НДС
            if ((int)((double)$row['nds'] * 100) != 18)
                $nds = "<br>НДС ".(int)((double)$row['nds'] * 100)."%";

            $ind_data = ""; // "bgcolor='#cc0000'"; // Индикатор окраски даты

            // Цвет строки. Если договор закрыт - зеленый. Нет - без цвета
            $ind_row = ""; // Индкатор строки
            if ((int)$row['zakryt'] == 1)
                $ind_row = " bgcolor='#85e085'";

            // Вывод остатка. Если он не нулевой и не равен количеству поставки то выводим
            $ostatok_str = "";
            if($numb_ostat>0 and $numb_ostat!=$numb){
                    $ostatok_str = ' (' . $numb_ostat . ')';
            }

            // Если договор внешний то надо Код организации указать как Код исполнителя
            if ($kod_ispolnit != 683) {
                $kod_org = $kod_ispolnit;
                $nazv_krat = $ispolnit_nazv_krat;
            }

            // Модификация
            if ($mod != "")
                $mod = " ($mod)";

            // Формируем строку
            if ($i == 0 and $cnt > 1) { // Когда требуется объединение строк
                $res .= "<tr $ind_row>
                                <td $rowspan width='100'><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$nomer<br>$annulir</a></td>
                                <td $rowspan><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>";
            } elseif ($cnt == 1) { // Когда объединение строк не требуется
                $res .= "<tr $ind_row>
                                    <td><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$nomer<br>$annulir</a></td>
                                    <td width='150'><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>";
            } else {
                $res .= "<tr $ind_row>";
            }

            $res .= "<td  width='365'><a href='form_part.php?kod_part=" . $kod_part . "&kod_dogovora=" . $kod_dogovora . "'><img src='/img/edit.gif' height='14' border='0' /></a>
                                       <a href='form_elem.php?kod_elem=" . $kod_elem . "'>" . $obozn . $mod . "</a></td>
                      <td width='40'>" .$numb . $ostatok_str . "</td>
                      <td width='80' " . $ind_data . ">" . $data . "</td>
                      <td width='120' align='right'>" . Func::Rub($price_nds) . "</td>
                      <td width='120' align='right'>" . Func::Rub($part_summa) . $val . $nds . "</td>
                      <td width='90'>" . $oplacheno . "</td>
                  </tr>";
        }

        return $res;
    }
//--------------------------------------------------------------
//

    /**
     * Сумма договора
     * @param $kod_dogovora
     * @return float|int
     */
    public static function getSummaDogovora($kod_dogovora)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_dogovor_summa WHERE kod_dogovora=$kod_dogovora");

        if ($db->cnt > 0)
            return (double)$rows[0]['dogovor_summa'];
        else
            return 0;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Сумма платежей по договору
     * @param $kod_dogovora
     * @return float|int
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
     * @param $kod_kontakta
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
                                    INNER JOIN kontakty_dogovora ON view_rplan.kod_dogovora = kontakty_dogovora.kod_dogovora
                                    WHERE
                                        kontakty_dogovora.kod_kontakta = $kod_kontakta
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
                            <th scope="row"></th>
                            <td bgcolor="#F18585">Закрыт</td>
                        </tr>';
            else {
                if ($Close == 1)
                    $clr = '<tr>
                            <th scope="row"></th>
                            <td>' . Func::ActButton('', 'Закрыть', 'DocClose') . '</td>
                            </tr>';
            }

            $ISP = '';

            if ($this->kod_org == 683) {
                $ISP = '<tr>
                            <th scope="row">Исполнитель</th>
                            <td><a href="form_org.php?kod_org=' . $row['kod_ispolnit'] . '">' . $row['ispolnit_nazv_krat'] . '</a></td>
                            </tr>';
            }

            echo // Проверить правильность. Округление! Валюта - пока только руб.
                '<table width="600" border="0">
                        <th width="202" scope="row">Номер</th>
                        <td width="374"><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '" ><h1>' . $row['nomer'] . '</h1></a></td>
                      </tr>
                      <tr>
                        <th scope="row">Дата Составления </th>
                        <td>' . Func::Date_from_MySQL($row['data_sost']) . '</td>
                      </tr>
                      <tr>
                        <th scope="row">Заказчик</th>
                        <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                      </tr>
                      <tr>
                      </tr>
                        ' . $ISP . '
                      <tr>
                        <th scope="row">Сумма Договора</th>
                        <td>' . Func::Rub($row['dogovor_summa']) . ' р.</td>
                      </tr>
                      <tr>
                        <th scope="row">Сумма Платежей</th>
                        <td>' . Func::Rub($row['summa_plat']) . ' р.</td>
                      </tr>
                      <tr>
                        <th scope="row">Остаток</th>
                        <td>' . Func::Rub($row['dogovor_ostat']) . ' р.</td>
                      </tr>
                        ' . $clr . '
                </table>';

        }
    }
//--------------------------------------------------------------
//

    /**
     * Форма редактирования Договора
     * todo - упростить ввод внешних/внутренних договоров - например <input name="vnesh" type="radio" />
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit=0)
    {
        $nomer = "";
        $data_sost = Func::NowE();
        $kod_org = $this->kod_org;
        $kod_ispolnit = 683;
        $FormName = "formAdd";

        if($Edit==1)
        {
            $nomer = $this->Data['nomer'];
            $data_sost = Func::Date_from_MySQL($this->Data['data_sost']);
            $kod_org = $this->Data['kod_org'];
            $kod_ispolnit = $this->Data['kod_ispolnit'];
            $FormName = "formEdit";
        }

        $res = /** @lang HTML */
            '<form id="form1" name="form1" method="post" action="">
                    <table width="600" border="0">
                      <tr>
                            <td>
                                Договор
                            </td>
                      </tr>
                      <tr>
                        <th width="202" scope="row">Номер</th>
                        <td width="374"><span id="SNumR">
                                  <input type="text" name="nomer" id="nomer" value="' . $nomer . '"/>
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldMinCharsMsg">Minimum
                                  number of characters not met.</span></span>
                         </td>
                      </tr>
                      <tr>
                        <th scope="row">Дата Составления </th>
                        <td><span id="SDateR">
                                  <input type="text" name="data_sost" id="data_sost" value="' . $data_sost . '" />
                             <span class="textfieldRequiredMsg">A value is required.</span>
                             <span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span>
                             </span>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">Заказчик</th>
                        <td>' . Org::formSelList($kod_org, '', 'kod_org') . '</td>
                      </tr>
                      <tr>
                        <th scope="row">Поставщик</th>
                        <td>' . Org::formSelList($kod_ispolnit, '', 'kod_ispolnit') . '</td>
                      </tr>
                    </table>
        
                    <input id="'.$FormName.'" type="hidden" value="'.$FormName.'" name="'.$FormName.'"/>
                    <input type="submit" value="Сохранить" />
                </form>';

        return $res;
    }
//--------------------------------------------------------------------
//

    /**
     * @param int $kod_dogovora
     * @return array
     */
    public function getData($kod_dogovora=-1)
    {
        if($kod_dogovora>0)
            $this->kod_dogovora = $kod_dogovora;

        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_dogovor_data WHERE kod_dogovora= $this->kod_dogovora");

        unset($this->Data);

        if ($db->cnt > 0)
            $this->Data = $rows[0];

        $this->kod_org = $this->Data['kod_org'];
        $this->nomer = $this->Data['nomer'];
        $this->nazv_krat = $this->Data['nazv_krat'];

        return $this->Data;

    }
//--------------------------------------------------------------------
//

    /**
     * @param int $sgp
     */
    public function formParts($sgp = 0)
    {
        $p = new Part();
        $p->kod_dogovora = $this->kod_dogovora;
        echo $p->formParts($sgp);
    }
//--------------------------------------------------------------
//

    /**
     * Список договоров
     * @param int $VN : 1 - внешний; 0 - внутренний
     * @return string
     */
    public function formRPlan($VN = 0)
    {
        if ($VN == 0) //Если договор поставки
            $sql = /** @lang SQL */
                "SELECT 
                    * 
                    FROM 
                      view_rplan 
                    WHERE 
                      kod_ispolnit=683 AND zakryt<>1 
                    ORDER BY 
                      obozn ASC,
                      numb DESC";

        else // Если договор внешний
            $sql = /** @lang SQL */
                "SELECT 
                    * 
                    FROM 
                      view_rplan 
                    WHERE 
                      kod_org=683 AND zakryt<>1 
                    ORDER BY 
                      obozn ASC, 
                      numb DESC";

        $db = new Db();
        $rows = $db->rows($sql); // Массив данных

        return $this->formRPlan_by_Elem($rows);
    }
//--------------------------------------------------------------
//

    /**
     * График поставок в тек. и след. месяцах по Изделиям. (План Реализации)
     * @param $rplan_rows
     * @return string
     */
    static public function formRPlan_by_Elem($rplan_rows)
    {
        $cnt = count($rplan_rows); // Количество записей

        if ($cnt == 0) return "Список договоров пуст";// "RPlan with current SQL = $sql is empty"; // Если данных нет то выходим

        // Формируем заголовок таблицы
        $header = "<tr bgcolor=\"#CCCCCC\">
                    <td width=\"200\">Наименование</td>
                    <td>Кол-во</td>
                    <td>Оплата</td>
                    <td width=\"180\">Номер Договора</td>
                    <td>Организация</td>
                    <td width=\"100\">Дата</td>
                    <td width=\"100\">Сумма с НДС</td>
                </tr>";

        // Создаем таблицу
        $res = '<table width="100%">' . $header;

        // Переменные
        $cm = (int)date('m');// Текущий месяц
        $cy = (int)date('Y'); // Текущий год
        $zebra = "#FFFFFF"; // Цвет зебры
        $itog_summ = 0; // Итоговая Сумма по всем партиям
        $fut = ''; // Будущий план
        $kod_elem_pred = -1; // Код предыдущего элемента
        $kod_elem_pred_fut = -1; // Код предыдущего элемента

        // Вывод плана
        for ($i = 0; $i < $cnt; $i++) {

            $row = $rplan_rows[$i];

            // Партия
            $numb_ostat = $row['numb_ostat'];

            if ($numb_ostat == 0)
                continue; // Если нет остатка то переходим к след. шагу

            // Договор
            $kod_dogovora = (int)$row['kod_dogovora']; // Код договора
            $nomer = $row['nomer']; // номер договора
            $kod_org = (int)$row['kod_org']; // Код организации (Заказчик)
            $nazv_krat = $row['nazv_krat']; // Название Заказчика

            // Если заказчик НВС - то выводим исполнителя
            if($kod_org==683) {
                $kod_org = $row['kod_ispolnit']; // Код исполнителя
                $nazv_krat = $row['ispolnit_nazv_krat'];
            }

            $kod_elem = (int)$row['kod_elem']; // Код элемента
            $obozn = $row['obozn']; // Обозначение
            $modif = $row['modif']; // Модификация
            $numb = $row['numb'];
            $val = (int)$row['val']; // Валюта
            $price_nds = round($row['price'] * (1 + (double)$row['nds']), 2); // Цена с НДС
            $part_summa = $row['part_summa']; // Сумма остатка партии
            $part_summa_ostat = $price_nds*$numb_ostat; // Сумма остатка партии
            $data_postav = $row['data_postav'];
            $nds = round((double)$row['nds'], 2);

            $part_summa_ostat_str = "";
            if($part_summa_ostat!=$part_summa)
            {
                $part_summa_ostat = func::Rub($part_summa_ostat);
                $part_summa_ostat_str = "<br><abbr title=\"Сумма неотгруженного остатка\">($part_summa_ostat)</abbr>";
            }

            $numb_ostat_str = "";
            if($numb_ostat>0 and $numb_ostat!=$numb)
                $numb_ostat_str = " <abbr title=\"Осталось отгрузить $numb_ostat\">($numb_ostat)</abbr>";

            // Строки
            $Dt = Func::Date_from_MySQL($data_postav); // Дата поставки

            $Mod = '';            // Модификация
            if ($modif != '')
                $Mod = " ($modif)";

            // НДС
            $NDS = '';
            if ($nds != 0.18)
                $NDS = '<br>НДС ' . $nds * 100 . '%';

            // Валюта
            $Val = '';
            if ($val != 1)
                $Val = ' ' . $val;

            $proc = self::getProcPay($kod_dogovora); // todo - Сравнить производительность - Ввести в запрос rplan или отдельно много запросов
            if($proc==0)
                $proc = "";
            else
                $proc.="%";

            $itog_summ += $part_summa;// Итоговая Сумма по всем партиям

            // Сумма партии
            $part_summa_str = Func::Rub($part_summa);

            if ($zebra == "#FFFFFF")
                $zebra = "#E6E6E6";
            else
                $zebra = "#FFFFFF";

            // Формируем строку плана
            $row_str = "<tr bgcolor='$zebra'>
                                <td><a href='form_elem.php?kod_elem=" . $kod_elem . "'>" . $obozn . $Mod . "</a></td>
                                <td align='right'>" . $numb . $numb_ostat_str. "</td>
                                <td align='right'>" . $proc . "</td>
                                <td align='right'><a href='form_dogovor.php?kod_dogovora=" . $kod_dogovora . "'>" . $nomer . "</a></td>
                                <td><a href='form_org.php?kod_org=" . $kod_org . "'>" . $nazv_krat . "</td>
                                <td align='right'>" . Func::Date_from_MySQL($data_postav) . "</td>
                                <td align='right'>" . $part_summa_str . $Val . $NDS .$part_summa_ostat_str. "</td>
                         </tr>";

            $rowm = 0;
            $rowy = 0;
            // Разбираем дату на месяц и год
            if ($Dt != '-') {
                $m = explode('.', $Dt);

                if(count($m)>2)
                {
                    $rowm = (int)$m[1];
                    $rowy = (int)$m[2];
                }


                // Если месяц и год обрабатываемой сторки меньше текущего месяца и года то...
                // Записываем в график поставок на текущий месяц
                // в противном случае на будующие месяцы
                if (($rowy == $cy and $rowm <= $cm) or ($rowy < $cy)) {

                    // Если предыдущий элемент другой то создаем заголовок
                    if ($kod_elem != $kod_elem_pred)
                        $res .= "<tr><th colspan='7' align='left' bgcolor='#faebd7'>" . $obozn . "</th></tr>";
                    // Записываем строку в план
                    $res .= $row_str;

                    $kod_elem_pred = $kod_elem;

                    continue; // строка уже записана, переходим на след шаг
                }
            }

            // Если предыдущий элемент другой то формируем заголовок
            if ($kod_elem != $kod_elem_pred_fut)
                $fut .= "<tr><th colspan='7' align='left' bgcolor='#faebd7'>" . $obozn . "</th></tr>";

            // Если строка не записана в текущий план, то записываем в план на будущее
            $fut .= $row_str;

            $kod_elem_pred_fut = $kod_elem;
        }

        // Формируем заголовок для плана на след. месяцы
        $res .= "<tr bgcolor=\"#21ba42\">
                    <td>Поставка в следующих месяцах</td>
                    <td width=\"100\"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>"
            . $header
            . $fut .
            '</table>';

        // Выводим сумму по всем партиям
        $res .= "<br>Итого: " . Func::Rub($itog_summ) . "<br>";

        return $res;
    }
//-------------------------------------------------------------------

    /**
     * Процент оплаты = Сумма платежей / Сумма договора
     * @param $kod_dogovora - код договора
     * @return float|int
     */
    public static function getProcPay($kod_dogovora)
    {
        // Сумма платежей
        $summa_plat = self::getSummaPlat($kod_dogovora);

        if ($summa_plat==0)
            return 0;

        // Сумма договора
        $dogovor_summa = self::getSummaDogovora($kod_dogovora);

        if ($dogovor_summa==0)
            return 0;

        $p = 0.;
        if ($dogovor_summa > 0)
            $p = $summa_plat / $dogovor_summa;

        return Func::Proc($p);
    }
//-------------------------------------------------------------------
//

    /**
     * Форма - Платежи по договору
     */
    public function formPP()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM plat WHERE kod_dogovora=$this->kod_dogovora");

        $cnt = $db->cnt;

        if($cnt==0)
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

            $res.= '<tr>
                        <td>' . Func::Rub($row['summa']) . '</td>
                        <td>' . $row['nomer'] . '</td>
                        <td>' . Func::Date_from_MySQL($row['data']) . '</td>
                        <td>' . $row['prim'] . '</td>
                    </tr>';
        }
        $res.= '</table>';

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

        $sql = "SELECT 
                * 
                FROM 
                    view_rplan 
                WHERE 
                    kod_org=$this->kod_org
                    OR kod_ispolnit=$this->kod_org
                ORDER BY 
                kod_dogovora DESC,
                view_rplan.name ASC";

        $rows = $db->rows($sql);


        if(count($rows)>0)
            return $this->formRPlan_by_Doc($rows);

        return "Договоры без партий:<br>".$this->formDocList(/** @lang SQL */
            "SELECT * FROM view_scheta_dogovory_all WHERE kod_org=$this->kod_org");
    }

//-----------------------------------------------------------------------
//

    /**
     * Показать счета по Договору - Проверить Удаление
     */
    public function formScheta()
    {

        $db = new Db();

        $rows = $db->rows("SELECT * FROM scheta WHERE kod_dogovora=$this->kod_dogovora");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return;

        echo '<br>Счета<br>';
        echo '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        echo '<tr bgcolor="#CCCCCC" >
                <td width="60">Номер</td>
                <td width="100">Сумма</td>
                <td width="80">Дата</td>
                <td>Примечание</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            echo '<tr>
                        <td>' . $row['nomer']             . '</td>
                        <td>' . Func::Rub($row['summa']) . '</td>
                        <td>' . Func::Date_from_MySQL($row['data']) . '</td>
                        <td>' . $row['prim'] . '<br>' . Func::ActForm('', '<input type="hidden" name="InvID" id="InvID" value="' . $row['kod_scheta'] . '" />', 'Удалить Счет', 'DelInv') . '</td>
                    </tr>';
        }

        echo '</table>';
    }
//-----------------------------------------------------------------------
//

    /**
     * Примечание договора
     * @param int $AddForm
     * @return string
     */
    public function formPrim($AddForm=0)
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM dogovor_prim WHERE kod_dogovora=$this->kod_dogovora ORDER BY dogovor_prim.time_stamp DESC");

        $cnt = $db->cnt;
        $res = "";
        if($AddForm==1)
        {
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
            $res.= Func::Cansel();
        }

        if ($cnt == 0)
            return $res;

        // Формируем таблицу
        $res.= 'Примечание
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC" >
                    <td width="80">Дата</td>
                    <td>Текст</td>';

        // Заполняем данными
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $user = "";
            if($row['user']!="")
                $user = "<br>".$row['user'];

            $res.=  '<tr>
                        <td>' . Func::Date_from_MySQL($row['time_stamp']) . $user . '</td>
                        <td>' . $row['text'] . '</td>
                     </tr>';
        }
        $res.= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------
// Удаление счета и данных связных таблиц. Переделать с каскадным удалением!

    /**
     * Контакты по договору. Проверить
     * @param int $AddPh - вывод формы добавления телефона
     */
    public function formDocKontakts($AddPh = 0)
    {
        $c = new Kontakt();
        $c->kod_dogovora = $this->kod_dogovora;

        // Если организация NVS
        if ($this->kod_org != 683)
            $c->kod_org = $this->kod_org;
        else
            $c->kod_org = $this->Data['kod_ispolnit'];

        // Показать контакты
        echo $c->formKontakts($AddPh, "Doc");
    }
//----------------------------------------------------------------------------------------------------------------------
// Вывод списка договоров по заданному запросу
// Сортировка по Элементам

    /**
     * Документы по Договору. Проверить
     * @param $DelButton - кнопка удаления
     * @return string
     */
    public function formDocum($DelButton)
    {
        $d = new Docum();
        return $d->formDocum('Doc', $this->kod_dogovora, $DelButton);
    }
//-----------------------------------------------------------------------
// История по Складу

    /**
     * Список Всех договоров и вложенных счетов
     * @param string $sql - запрос
     * @return string
     */
    public function formDocList($sql="")
    {
        $db = new Db();
        if($sql=="")
            $sql = "SELECT * FROM view_scheta_dogovory_all";

        $rows = $db->rows($sql);

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        //$res = 'Количество Записей: ' . $cnt . '<br>';

        $res= '<table border=0 cellspacing=0 cellpadding=0 width="50%">';
        $res.= '<tr bgcolor="#CCCCCC">
                    <td>Номер Договора</td>
                    <td>Организация</td>
                </tr>';

        for ($i = 0; $i < $cnt; $i++) {

            $row = $rows[$i];

            if ($row['kod_org'] == 683)
                $res.= '<tr bgcolor="#8fe8a1">
                            <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer'] . '</a></td>
                            <td width="80%"><a href="form_org.php?kod_org=' . $row['kod_ispolnit'] . '">' . $row['ispolnit_nazv_krat'] . '</a></td>
                        </tr>';
            else
                $res.= '<tr>
                            <td><a href="form_dogovor.php?kod_dogovora=' . $row['kod_dogovora'] . '">' . $row['nomer'] . '</a></td>
                            <td width="80%"><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                        </tr>';
        }
        $res.= '</table>';

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
            $pm='-';
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
                if(count($m)>2){
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
                          <td ' . $col . '>' . $prs . '%</td>
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

            if (($ostat) > 0)
            {
                $res .= "<option value=$kod_plat>ПП №  $nomer  от  $data - $ostat  р.</option>";
                $sell_list_empty = false;
            }
        }

        if($sell_list_empty) // Если список пустой - возвращаем пустую строку.
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

    public function formSGPHistory()
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_sklad");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return '';

        $res = '<table width="100%" border="0">
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
                    <td>' . Func::Proc($procent) . '</td>
                </tr>';

        }

        $res .= '</table>';

        return $res;
    }
//-----------------------------------------------------------------------
//

    /**
     * Добавить Договор
     * @param $nomer
     * @param $data_sost
     * @param $kod_org - Код организации Заказчика
     * @param int $kod_ispolnit
     * @internal param int $VN - Внешний, Заказчик - НВС, Исполнитель - Код организации
     * @internal param $Num - номер
     * @internal param $DataCR - Дата создания договора
     * @internal param $Priem - Приемка, не актуально
     */
    public function Add($nomer, $data_sost, $kod_org, $kod_ispolnit = 683)
    {
        $db = new Db();

        $data_sost = func::Date_to_MySQL($data_sost);

            $sql = "INSERT INTO dogovory (nomer,data_sost,kod_org,kod_ispolnit) VALUES('$nomer','$data_sost',$kod_org,$kod_ispolnit)";

        $db->query($sql);
    }
//--------------------------------------------------------------

    /**
     * Обработчик событий
     *
     */
    public function Events()
    {
        $event = false;

        if (isset($_POST['formAdd'])) {
            if(isset($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['kod_ispolnit']))
            {
                $this->Add($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['kod_ispolnit']);
                $event = true;
            }
            elseif(isset($_POST['famil'], $_POST['name']))
            {
                $this->AddCont($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
                $event = true;
            }
        }

        if (isset($_POST['formAddPP']))
            if (isset($_POST['nomer'], $_POST['summa'], $_POST['data'])) {
                $this->AddPay($_POST['nomer'], $_POST['summa'], $_POST['data'], $_POST['prim']);
                $event = true;
            }

        if (isset($_POST['AddInv']))
            if (isset($_POST['InvNum']) and isset($_POST['InvSumm']) and isset($_POST['InvDate'])) {
                $this->AddInvoice($_POST['InvNum'], $_POST['InvSumm'], $_POST['InvDate'], $_POST['InvPrim']);
                $event = true;
            }

        if (isset($_POST['AddPrim']))
            if (isset($_POST['Prim'])) {
                $this->AddPrim($_POST['Prim'],$_SESSION['MM_Username']);
                $event = true;
            }

        if (isset($_POST['formEdit']))
            if (isset($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['kod_ispolnit'])) {
                $this->Edit($_POST['nomer'], $_POST['data_sost'], $_POST['kod_org'], $_POST['kod_ispolnit']);
                $event = true;
            }

        if (isset($_POST['Flag']))
        {
            if ($_POST['Flag'] == 'DelInv') {
                $this->DelInvoice($_POST['InvID']);
                $event = true;
            }
            elseif($_POST['Flag'] == 'DocOpen') {
                    $this->Close(0);
                    $event = true;
                }
            elseif($_POST['Flag'] == 'DocCloseConf') {
                $this->Close();

                $event = true;
            }
        }
            if($event)
                header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//----------------------------------------------------------------------------------------------------------------------
// Строка плана rplan

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

        $db->query("INSERT INTO plat (kod_dogovora,nomer,summa,data,prim) VALUES($kod_dogovora,$nomer,$summa,'$data','$prim')");
    }
//----------------------------------------------------------------------------------------------------------------------
// Читает подряд все строки rplan с одним кодом договора. Используется при выводе договоров
// На вход подается rplan отсортированный по коду договора

    /**
     * Добавление счета в договор
     * @param $Numb
     * @param $Summ
     * @param $Date
     * @param string $Prim
     * @internal param string $PayDate
     */
    public function AddInvoice($Numb, $Summ, $Date, $Prim = '-')
    {
        $kod_dogovora = $this->kod_dogovora;

        if (!isset($Prim)) $Prim = '-';

        $db = new Db();
        $Date = func::Date_to_MySQL($Date);

        $db->query("INSERT INTO scheta (kod_dogovora,nomer,summa,data,prim) VALUES($kod_dogovora,'$Numb',$Summ,'$Date','$Prim')");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление контакта в договор
     * @param $Dolg
     * @param $SName
     * @param $Name
     * @param $PName
     */
    public function AddCont($Dolg, $SName, $Name, $PName)
    {
        $c = new Kontakt();
        $c->kod_dogovora = $this->kod_dogovora;
        $c->kod_org = $this->kod_org;
        $c->AddKontakt($Dolg, $SName, $Name, $PName);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $Prim
     * @param string $user
     */
    public function AddPrim($Prim, $user="")
    {
        if (strlen($Prim) < 4) {
            echo "Err: Слишком короткое примечание. Должно быть не менее 4-х символов.";
            return;
        }

        $P = nl2br($Prim); // Вставлем <br> вместо перевода строки

        $db = new Db();
        $db->query("INSERT INTO dogovor_prim (kod_dogovora,text,user) VALUES($this->kod_dogovora,'$P','$user')");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Сохранить Изменения Договора
     * @param $Numb - номер
     * @param $Date - дата
     * @param $kod_org - код заказчика
     * @param $IspID - код исполнителя
     */
    public function Edit($Numb, $Date, $kod_org, $IspID)
    {
        $DateR = func::Date_to_MySQL($Date);
        $db = new Db();
        $db->query("UPDATE dogovory SET nomer = '$Numb', data_sost='$DateR', kod_org=$kod_org, kod_ispolnit=$IspID WHERE kod_dogovora=$this->kod_dogovora");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $kod_scheta
     */
    public function DelInvoice($kod_scheta)
    {
        $db = new Db();

        if (isset($kod_scheta)) {
            $db->query("DELETE FROM scheta WHERE kod_scheta=$kod_scheta");

        } else
            echo "Ошибка: Не задан ID Счета";
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Закрытие договора или отмена закрытия
     * @param int $zakryt - 1-Закрыть, 0 - отмена закрытия
     */
    public function Close($zakryt=1)
    {
        $db = new Db();

        $now = date('y.m.d');

        $db->query("UPDATE dogovory SET zakryt = $zakryt, data_zakrytiya='$now' WHERE kod_dogovora=$this->kod_dogovora");
    }

//----------------------------------------------------------------------------------------------------------------------
    /**
     * Форма вставки счета
     * @return string
     */
    public function formAddInvoice()
    {
       $res = /** @lang HTML */
                            '<form id="form1" name="form1" method="post" action="">
                              <table width="434" border="0">
                                <tr>
                                  <td width="126">Номер Счета</td>
                                  <td width="292"><span id="SNumR">
                                  <input type="text" name="InvNum" id="InvNum" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldMinCharsMsg">Minimum
                                  number of characters not met.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Дата</td>
                                  <td><span id="SDateR">
                                  <input type="text" name="InvDate" id="InvDate" value="' . date('d.m.Y') . '" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                                </tr>
                                <tr>
                                  <td>Сумма</td>
                                  <td><span id="SSummR">
                                  <input type="text" name="InvSumm" id="InvSumm" />
                                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid
                                  format.</span></span></td>
                                </tr>
                                <tr>
                                  <td>Примечание</td>
                                  <td><span id="STextNR">
                                    <input type="text" name="InvPrim" id="InvPrim" />
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
       $res.= func::Cansel();
       return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function formAddPP()
    {
        $date = date('d.m.Y');
        $res = /** @lang HTML */
            "               <form id=\"form1\" name=\"form1\" method=\"post\" action=\"\">
                                  <table width=\"434\" border=\"0\">
                                    <tr>
                                      <td width=\"126\">Номер ПП</td>
                                      <td width=\"292\"><span id=\"SNumR\">
                                      <input type=\"text\" name=\"nomer\" id=\"nomer\" />
                                      <span class=\"textfieldRequiredMsg\">A value is required.</span><span class=\"textfieldMinCharsMsg\">Minimum
                                      number of characters not met.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Дата</td>
                                      <td><span id=\"SDateR\">
                                      <input type=\"text\" name=\"data\" id=\"data\" value=\"$date\"/>
                                      <span class=\"textfieldRequiredMsg\">A value is required.</span><span class=\"textfieldInvalidFormatMsg\">Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Сумма</td>
                                      <td><span id=\"SSummR\">
                                      <input type=\"text\" name=\"summa\" id=\"summa\" />
                                      <span class=\"textfieldRequiredMsg\">A value is required.</span><span class=\"textfieldInvalidFormatMsg\">Invalid
                                      format.</span></span></td>
                                    </tr>
                                    <tr>
                                      <td>Примечание</td>
                                      <td><span id=\"STextNR\">
                                        <input type=\"text\" name=\"prim\" id=\"prim\" />
                                      </span></td>
                                    </tr>
                                  </table>
                                  <p>
                                    <input type=\"submit\" name=\"button\" id=\"button\" value=\"Добавить\" />
                                    <input type=\"hidden\" name=\"formAddPP\" value=\"formAddPP\" />
                                  </p>
                                </form>";
        $res.= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');
        return $res;
    }
}// END CLASS