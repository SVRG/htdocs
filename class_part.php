<?php
include_once('class_func.php');
include_once('class_elem.php');
include_once('class_config.php');

class Part
{
    public $kod_part = 0;
    public $kod_dogovora;
    //-------------------------------------------------------------------------

    /**
     * Part constructor.
     */
    public function __construct()
    {

    }
    //-------------------------------------------------------------------------

    /**
     * Формируем таблицу партий по договору. На входе rplan
     * @param int $sgp - вывод накладных о поступлении на склад и об отгрузке (1-вывод,0-не выводить)
     * @param string $sql - запрос. По умолчанию - "" = Empty
     * @param int $AddNacl
     * @return string
     */
    public function formParts($sgp = 0, $sql = "", $AddNacl = 0)
    {
        $db = new Db();

        // Если запрос не был передан в параметрах
        if ($sql == "")
            $sql = /** @lang MySQL */
                "SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora ORDER BY data_postav;"; // Сначала старые партии

        $rows = $db->rows($sql);
        $cnt = $db->cnt;

        $btn_auto_ras = '';
        $btn_add_100 = '';

        // Если вызов списка партий - выводим кнопки Добавить партию и Авто-Расчет 100%
        // Если вызов из формы Партия - выводим только Авторасчет
        if ($this->kod_part != 0) {
            $btn_auto_ras = "<div>" . Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora&kod_part=" . $this->kod_part, 'Авто-Расчет', 'AddAVOK') . "</div>";
        } else {
            if (func::user_group() == "admin") // todo - Придумать глобальную политику прав
            {
                // todo - добавить проверку, может уже есть расчеты на 100%
                $btn_add_100 = "<div>" . Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora", 'Авто-Расчет 100%', 'AddRasch100') . "</div>";
            }
        }

        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddPartForm');

        // Шапка
        // Кнопки Добавить Партию / Добавить расчет во все партии / Добавить расчет в партию
        $res = "<div class='btn'>
                  <div><b>Партии</b></div>
                  <div>$btn_add</div>
                  $btn_add_100
                  $btn_auto_ras
                </div>";

        $res .= '<table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC">
                        <td width="365">Наименование</td>
                        <td width="40">Кол-во</td>
                        <td width="80">Дата</td>
                        <td width="80">Склад</td>
                        <td width="120">Цена без НДС</td>
                        <td width="120">Цена c НДС</td>
                        <td width="120">Сумма</td>
                        <td width="90">Оплата</td>
                    </tr>';

        $dogovor_proc_pay = Doc::getProcPay($this->kod_dogovora); // Процент платежей по договору. Строка вида "70%"

        // Редактирование партии
        $edit = false;
        if (!Doc::getPaymentFlag($this->kod_dogovora) or (isset($_GET['edit'])))
            $edit = true;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $this->kod_part = $row['kod_part'];
            $modif = '';
            if ($row['modif'] != '')
                $modif = ' (' . $row['modif'] . ')'; // Модификация

            $numb = round((double)$row['numb'], 2); // Количество товара в партии
            $numb_otgruz = (double)$row['numb_otgruz']; // Количество отгруженного товара по партии
            $sum_part = $row['sum_part'];

            $ost = $row['numb_ostat']; // Осталось отгрузить
            $ostatok = ""; // Строка для вывода остатка по отгрузке
            // Если договор входящий
            $numb_poluch = 0;
            if ($row['kod_ispolnit'] != config::$kod_org_main) {
                $numb_poluch = $this->getNumbPoluch($row['kod_part']);
                $ost = $numb - $numb_poluch;
            }

            // Вывод накладных о поступлении и Отгрузке с СГП---------------------------------------
            $nacl = ''; // Строка вывода накладных
            if ($sgp == 1)
                $nacl = $this->formSGPAll();

            // Форма добавления накладной
            if ($AddNacl > 0) {
                if ((int)$row['kod_org'] != config::$kod_org_main)
                    $nacl .= $this->formAddNacl($ost, 2); // Отгрузка
                else
                    $nacl .= $this->formAddNacl($ost, 1); // Поступление
            } elseif ($ost > 0) // Выводим кнопку Добавить только когда есть отстаток
                $nacl .= Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], "Добавить", 'AddNaklad', "kod_part", $row['kod_part']);

            // Форма редактированя суммы партии
            $sum_part_form = "";
            if ($edit)
                $sum_part_form = Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], "Изменить", 'formSumPart', "kod_part_edit_sum", $row['kod_part']);
            if (isset($_POST['kod_part_edit_sum']))
                if ($_POST['kod_part_edit_sum'] == $this->kod_part)
                    $sum_part_form = $this->formSumPart();

            // Цена без НДС-------------------------------------------------------------------------------
            $price_str = self::formPrice($row);
            $price_it_str = Func::Rub($row['price_it']) . " " . Part::formPriceIndicator($row);

            // Дата поставки------------------------------------------------------------------------------
            $data_postav = Func::Date_from_MySQL($row['data_postav']);
            $data_postav_str = $data_postav;
            if (isset($row['data_nach'])) {
                if ($row['data_nach'] !== '0000-00-00')
                    $data_postav_str = Func::Date_from_MySQL($row['data_nach']) . "<br>" . $data_postav; // Дата начала
            }

            // Окраска отгруженных/полученных партий в зелёный
            $ind = '';// Индикатор окраски даты поставки
            if ($ost == 0) {
                $res .= '<tr bgcolor="#ADFAC2">';
                $data_postav_str = "<b>" . Part::getLastNaklDate($this->kod_part) . "</b>";; // Дата последней отгрузки
            }// Зеленый
            else {
                $res .= '<tr>';
                // Если отстаок не равен количеству партии то выводим
                if ($ost != $numb) {
                    if ($row['kod_ispolnit'] == config::$kod_org_main)
                        $ostatok = " (<abbr title=\"Осталось отгрузить $ost\">$ost</abbr>)<br><abbr title='Отгружено $numb_otgruz'><img alt='Out' src=\"/img/out.gif\" height=\"14\" />$numb_otgruz</abbr>";
                    else
                        $ostatok = " (<abbr title=\"Осталось получить $ost\">$ost</abbr>)<br><abbr title='Получено $numb_poluch'><img alt='In' src=\"/img/in.gif\" height=\"14\" />$numb_poluch</abbr>";
                } else
                    $ostatok = "";

                // Дней до отгрузки
                try {
                    $drem = Func::DaysRem($row['data_postav']);
                } catch (Exception $e) {
                    $drem = 0;
                }

                // Если осталось меньше 30 и больше 14 дней то красим в оранжевый
                if ($dogovor_proc_pay > 0) {
                    if ($drem <= 14)
                        $ind = "bgcolor='#F18585'";// Красный
                    elseif ($drem <= 30)
                        $ind = 'bgcolor="#FFD222"';// Оранжевый
                }
            }

            //--------------------------
            // НДС
            $NDS = '';
            // Если отличается от текущей ставки то выводить
            if ((int)$row['nds'] != (int)config::$nds_main)
                $NDS = 'НДС ' . (int)$row['nds'] . '%';

            //--------------------------
            // Валюта
            $Val = func::val_sign($row['val']);

            // Процент оплаты
            $PRC = 0; // Строка вывода процента
            if ($dogovor_proc_pay > 0) {
                $prc = $this->getProcPayByPart($row);
                if ($prc != $dogovor_proc_pay)
                    $PRC = $prc . "($dogovor_proc_pay)";
                else
                    $PRC = $prc;
            }

            $pn = '<br> p/n ' . $row['kod_part']; // Идентификатор партии
            $prc_profit = self::formPartProfitProc($row['kod_part']); // Процент прибыли

            //Примечание партии
            $prim = $this->formPrim();

            // Кнопка редактирования партии
            $btn_edit = ""; // Редактирование тольо если не было платежей или если пользователь задаст $_GET['edit']
            if ($edit)
                $btn_edit = Func::ActButton("form_part.php?kod_part=" . $row['kod_part'] . '&kod_dogovora=' . $this->kod_dogovora, 'Изменить', 'EditPartForm');

            $btn_del = "";

            // todo - Придумать глобальные права
            if (isset($_GET['del']) and func::user_group() == "admin")
                $btn_del = "<div>" . Func::ActButton2('', "Удалить", 'DelPart', 'kod_part_del', $this->kod_part) . "</div>";

            $btn_copy_to_doc = $this->formCopyToDoc();
            $form_copy_to_doc = $this->formCopyToDoc(false);
            $btn_pl = "<a target='_blank' href='form_invoice.php?pl&kod_part=$this->kod_part&kod_dogovora=" . $row['kod_dogovora'] . "'>PL</a>";
            $btn_set = "<div>" . Func::ActButton("form_set.php?kod_part=" . $this->kod_part . "&add", 'Комплектация', 'PartSet', "target='_blank'") . $prc_profit . "</div>";
            $btn_add_po = "<div>" . $this->formAddPO() . "</div>";
            $form_add_po = $this->formAddPO(false);
            $form_linked_parts = "<div>" . $this->formLinkedParts() . "</div>";

            $btn_panel = /** @lang HTML */
                "<div class='btn'>
                    <div>$btn_edit</div>
                    <div>$prim</div>
                    <div>$btn_pl</div>
                    $btn_del $btn_copy_to_doc $btn_set $btn_add_po $form_linked_parts
                </div>";

            $formStatus = self::formStatus($this->kod_part);
            $status = self::getStatus($this->kod_part);
            $ind_part = "";
            if ($status == 2)
                $ind_part = /** @lang HTML */
                    " bgcolor='#CECEF2'";
            elseif ($status == 1)
                $ind_part = " bgcolor='#5ba6fb'";

            $res .=
                '<td  width="365"><a href="form_part.php?kod_part=' . $row['kod_part'] . '&kod_dogovora=' . $this->kod_dogovora . '"><img alt="Edit" src="/img/edit.gif" height="14" border="0" /></a>
                                  <a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '"><b>' . $row['shifr'] . "</b> " . $modif . '</a>' . $pn . $btn_panel . $form_copy_to_doc . $form_add_po . '</td>
                      <td width="70" align="right">' . $row['numb'] . $ostatok . "</td>
                      <td width='80' align='center' $ind >$data_postav_str</td>
                      <td width='40' $ind_part>$nacl $formStatus</td>
                      <td width='120' >" . $price_str . $Val . "</td>
                      <td width='120' >$price_it_str $Val  $NDS" . '</td>
                      <td width="120"><div class="btn"><div>' . Func::Rub($sum_part) . "$Val</div><div>$sum_part_form</div></div>" . $NDS . '</td>
                      <td width="90">' . $PRC . '%</td>
                  </tr>';
        }

        $res .= "</table>";
        return $res;
    }
//-------------------------------------------------------------------------

    /**
     * Все накладные отсортированные по дате
     * @return string
     */
    public function formSGPAll()
    {

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM sklad WHERE del=0 AND kod_part=$this->kod_part");
        $cnt = $db->cnt;

        $res = '';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $date = ' от ' . Func::Date_from_MySQL($row['data']); // Дата документа
            $kod_oborota = $row['kod_oborota']; // Код оборота = код накладной
            $naklad = $row['numb'] . '(№' . $row['naklad'] . $date . ')'; // Номер документа и дата

            $btn_poluch = ""; // Кнопка отметки получения накладной

            if ((int)$row['kod_oper'] == 1) // Поступление
                $res .= '<img alt="In" src="/img/in.gif" height="14" />' . $naklad;
            elseif ((int)$row['kod_oper'] == 2) // Отгрузка
            {
                $res .= '<img alt="Out" src="/img/out.gif" height="14" />' . $naklad;

                // Форма отметки о получении накладной
                if ((int)$row['poluch'] <> 1)
                    $btn_poluch = Func::ActButton2('', "Получено", 'PoluchNaklad', 'kod_oborota_poluch', $kod_oborota);
            } elseif ($row['kod_oper'] == 3) // Акт
                $res .= '<br>По Акту:' . $naklad;
            elseif ($row['kod_oper'] == 4) // Возврат
                $res .= '<br>Возврат:' . $naklad;

            // todo - Права доступа
            $btn_del = Func::ActButton2('', "Удалить", 'DelNaklad', 'kod_oborota_del', $kod_oborota);

            $res = "<div class='btn'>
                        <div>$res</div>
                        <div>$btn_del</div>
                        <div>$btn_poluch</div>
                   </div>";
        }

        return $res;
    }
    //-------------------------------------------------------------------------

    /**
     * Вывод партии с формой добавления накладной, если $AddNaklad=1
     * @param int $AddNacl
     * @return string
     * @throws Exception
     */
    public function formPart($AddNacl = 0)
    {
        // Шапка
        $res = $this->formParts(1, /** @lang MySQL */
            "SELECT * FROM view_rplan WHERE kod_part=$this->kod_part", $AddNacl);
        return $res;
    }
//--------------------------------------------------------------
    //
    /**
     * График Расчетов
     * @param bool $Edit
     * @return string
     */
    public function formPayGraph($Edit = false)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM raschet WHERE del=0 AND kod_part=$this->kod_part ORDER BY data;"); //

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = '<br>График Расчетов<br>
                <table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC">
                        <td>Дата</td>
                        <td>Сумма</td>
                        <td>Тип</td>
                        <td>Платежи</td>
                    </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Данные
            $raschet_summa = (double)$row['summa'];
            $kod_rascheta = $row['kod_rascheta'];
            $summa_pays = $this->getSummPlatByRasch($kod_rascheta);
            $data = Func::Date_from_MySQL($row['data']);
            $summa = Func::Rub($row['summa']);
            $type_rascheta = $row['type_rascheta'];

            if ($type_rascheta == "1")
                $type_rascheta = "АВ";
            else
                $type_rascheta = "OK";

            $ostatok_plat = $raschet_summa - $summa_pays;
            $ostatok_plat = Func::Rub($ostatok_plat);

            $btn_del = Func::ActButton2('', 'Удалить', 'DelRasch', "kod_rascheta_del", $kod_rascheta);

            // Форма для ввода ПП в расчет
            $Body = "<input type='hidden' name='kod_rascheta' value='$kod_rascheta' />
                        <input  name='summa' value='$ostatok_plat' />";

            $res .= '<tr>
                    <td width="80">' . $data . '</td>
                    <td width="100">' . $summa . '</td>
                    <td width="20" align="center">' . $type_rascheta . "<br>" . $btn_del . '</td>
                    <td>' . $this->formPPRascheta($kod_rascheta, $Edit, $Body) . "</td>
                    </tr>";
        }

        $res .= '</table>';
        return $res;
    }
//--------------------------------------------------------------
//
    /**
     * Форма добавления Аванса и Окончательного расчета (2 равсчета)
     * @return string
     */
    public function formAddAVOK()
    {
        $dt = Func::NowE();

        $res = "<form id='form1' name='form1' method='post' action=''>
                              <table width='293' border='0'>
                                    <tr>
                                        <td width='105'>Процент АВ</td>
                                            <td width='172'>
                                            <span id='sprytextfield_AVPr'>
                                                <input name='AVPr' type='text' id='text1' value='50'/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Дата</td>
                                        <td>
                                            <span id='sprytextfield_data'>
                                                <input type='text' name='data' id='data' value='$dt'/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                                <span class='textfieldInvalidFormatMsg'>Invalid format.</span>
                                            </span>
                                        </td>
                                        <td>Дата ОК</td>
                                        <td>
                                            <span id='sprytextfield_data'>
                                                <input type='text' name='data_ok' id='data_ok' value=''/>
                                                <span class='textfieldRequiredMsg'>A value is required.</span>
                                                <span class='textfieldInvalidFormatMsg'>Invalid format.</span>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <input type='submit' name='button' id='button' value='Добавить' />
                                <input type='hidden' name='SubmitAddAVOK' value='1' />
                          </form>";

        $res .= Func::Cansel();
        return $res;
    }

//--------------------------------------------------------------
//
    /**
     * Платежи по Расчету
     * @param $kod_rascheta
     * @param bool $Edit
     * @param string $Body
     * @return string
     */
    public function formPPRascheta($kod_rascheta, $Edit = false, $Body = '')
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_raschety_plat WHERE kod_rascheta=$kod_rascheta");
        $cnt = $db->cnt;

        $res = "";

        if ($cnt == 0) {
            $d = new Doc();
            $d->kod_dogovora = $this->kod_dogovora;
            $res .= "<br>" . $d->formPaySelList('', $Body);
            return $res;
        }

        $res = '<table border=0 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC">
                        <td width="100">Сумма</td>
                        <td width="80">Номер ПП</td>
                        <td width="80">Дата</td>
                        <td>Примечание</td>
                    </tr>';

        $sum = 0.; // Сумма платежей по расчету
        $raschet_summa = $rows[0]['raschet_summa'];


        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Данные
            $summa_raspred = (double)$row['summa_raspred'];
            $data_plat = Func::Date_from_MySQL($row['data_plat']);
            $summa_plat_str = Func::Rub($row['summa_raspred']);
            $nomer = $row['nomer'];
            $prim = $row['prim'];

            $sum += $summa_raspred;

            $res .= '<tr>
                        <td>' . $summa_plat_str . '</td>
                        <td>' . $nomer . '</td>
                        <td>' . $data_plat . '</td>
                        <td>' . $prim . '</td>
                     </tr>';
        }

        $res .= '</table>';
        $res .= 'Итого: ' . Func::Rub($sum);

        if ($Edit) {
            if ($sum < $raschet_summa) {
                $d = new Doc();
                $d->kod_dogovora = $this->kod_dogovora;
                $res .= "<br>" . $d->formPaySelList('', $Body);
            }
        }
        return $res;
    }
//--------------------------------------------------------------
//
    /**
     * Добавить расчет к партии
     * @param $summa
     * @param $data
     * @param $type_rascheta
     */
    public function AddRasch($summa, $data, $type_rascheta)
    {
        $summa = func::clearNum($summa);
        $db = new Db();
        $data = func::Date_to_MySQL($data);
        $type_rascheta = (int)$type_rascheta;
        $db->query(/** @lang MySQL */
            "INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$summa,'$data',$type_rascheta)");
    }
//------------------------------------------------------------------------
//
    /**
     * Удалить расчет
     * @param $kod_rascheta
     */
    public function DelRasch($kod_rascheta)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE raschety_plat SET del=1,kod_user=$kod_user WHERE kod_rascheta=$kod_rascheta");

        $db->query(/** @lang MySQL */
            "UPDATE raschet SET del=1,kod_user=$kod_user WHERE kod_rascheta=$kod_rascheta");
    }
//------------------------------------------------------------------------
//
    /**
     * Добавить расчет 100% во все партии договора
     */
    public function AddRasch100()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora");
        $cnt = $db->cnt;
        $kod_user = func::kod_user();

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_part = $row['kod_part'];
            $sum_part = $row['sum_part'];
            $data = Func::Date_to_MySQL($row['data_postav']); // Дата поставки
            $type = 2; //ОК- расчет

            $db->query(/** @lang MySQL */
                "INSERT INTO raschet (kod_part,summa,data,type_rascheta,kod_user) VALUES($kod_part,$sum_part,'$data',$type,$kod_user)");
        }

        return;
    }
//-----------------------------------------------------------------------
//
    /**
     * Добавляет Платеж в Расчет
     * @param $summa
     * @param $kod_rascheta
     * @param $kod_plat
     */
    public function AddPayToRas($summa, $kod_rascheta, $kod_plat)
    {
        $summa = func::clearNum($summa);
        $summa = func::rnd($summa);

        if ($summa < config::$min_price)
            return;
        $db = new Db();
        $kod_user = func::kod_user();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_plat WHERE kod_plat=$kod_plat");
        if ($db->cnt == 1) {
            $row = $rows[0];
            $ostat = ((double)$row['summa'] - (double)$row['summa_raspred']);
            if ($summa > $ostat)
                $summa = $ostat;
        }
        $kod_plat = (int)$kod_plat;
        $kod_rascheta = (int)$kod_rascheta;
        $db->query(/** @lang MySQL */
            "INSERT INTO raschety_plat (summa,kod_rascheta,kod_plat,kod_user) VALUES($summa,$kod_rascheta,$kod_plat,$kod_user)");
    }
//--------------------------------------------------------------
//
    /**
     * Формирование Расчетов по стандартной схеме АВанс-ОКончательный расчет
     * @param float $AVPr - процент аванса
     * @param $AVDate - дата аванса
     */
    public function setPayGraph($AVDate, $AVPr = 0.6)
    {

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_rplan WHERE kod_part=$this->kod_part");

        if ($db->cnt == 0)
            return;
        $row = $rows[0];
        $sum_part = $row['sum_part'];

        $raschet_summa = func::rnd($sum_part * func::rnd($AVPr)); // Сумма расчета

        $AVDate = func::Date_to_MySQL($AVDate);
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "INSERT INTO raschet (kod_part,summa,data,type_rascheta,kod_user) VALUES($this->kod_part,$raschet_summa,'$AVDate',1,$kod_user)");

        $ostatok = $sum_part - $raschet_summa; // todo - при равенстве величин возвращает значение >0

        if ($ostatok >= 0.01) { // Защита от малых значений
            $OKDate = $row['data_postav']; // Дата окончательного расчета = дата поставки

            if (isset($_POST['data_ok']))
                if (func::validateDate($_POST['data_ok']))
                    $OKDate = func::Date_to_MySQL($_POST['data_ok']);

            $db->query(/** @lang MySQL */
                "INSERT INTO raschet (kod_part,summa,data,type_rascheta,kod_user) VALUES($this->kod_part,$ostatok,'$OKDate',2,$kod_user)");
        }
    }
//--------------------------------------------------------------
//
    /**
     * Добавить Накладную в партию
     * @param $numb - количество
     * @param $naklad - номер
     * @param $data - дата
     * @param $kod_oper - код оператора
     */
    public function AddNaklad($numb, $naklad, $data, $kod_oper)
    {
        $db = new Db();
        $kod_part = $this->kod_part;
        $dataP = self::getData($kod_part);
        if($numb > $dataP['numb_ostat']) // Ограничение на количество
            $numb = $dataP['numb_ostat'];
        $data = func::Date_to_MySQL($data);
        $user = func::user();
        $kod_user = func::kod_user();
        $naklad = $db->real_escape_string($naklad);
        $kod_oper = (int)$kod_oper;

        $db->query(/** @lang MySQL */
            "INSERT INTO sklad (kod_part,numb,naklad,data,kod_oper,oper,kod_user) VALUES($kod_part,$numb,'$naklad','$data',$kod_oper,'$user',$kod_user)");

        return;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление накладной
     * @param $kod_oborota
     */
    public function DelNaklad($kod_oborota)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        if (isset($kod_oborota)) {
            $db->query(/** @lang MySQL */
                "UPDATE sklad SET del=1,kod_user=$kod_user WHERE kod_oborota=$kod_oborota");

        } else
            echo "Ошибка: Не задан ID накладной";
    }
//-----------------------------------------------------------------------

    /**
     * Форма добавления накладной - поставка=отгрузка/ заказ=поступление
     * @param int $numb
     * @param int $Act - действие 1 - Поступление, 2 - Отгрузка
     * @return string
     */
    public function formAddNacl($numb = 1, $Act = 1)
    {
        if ($Act == 2)
            $kod_oper = 'Отгрузка<input id="kod_oper" type="hidden" value="2" name="kod_oper"/>';
        else
            $kod_oper = 'Поступление<input id="kod_oper" type="hidden" value="1" name="kod_oper"/>';

        $res = '
                <form id="form1" name="form1" method="post" action="">
                <table width="200" border="0">
                              <tr>
                                <td>Номер </td>
                                <td><input  name="naklad" id="naklad" /></td>
                              </tr>
                              <tr>
                                <td>Дата</td>
                                <td><input  name="data" id="data" value="' . Func::NowE() . '" /></td>
                              </tr>
                              <tr>
                                <td>Кол-во </td>
                                <td><input  name="numb" id="numb" value="' . $numb . '" /></td>
                              </tr>
                              <tr>
                                <td></td>
                                <td>
                                ' . $kod_oper . '
                                </td>
                              </tr>
                            </table>
                <input type="hidden" name="AddEditNacl" value="1" />
                <input type="hidden" name="kod_part" value=' . $this->kod_part . ' />
                <input type="submit" name="button" id="button" value="Сохранить" />
                </form>
                ';
        $res .= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', 'Cansel');
        return $res;
    }

//-----------------------------------------------------------------------
//
    /**
     * Добавление или редактирование
     * @param $kod_elem - номенклатура
     * @param int $numb - количество
     * @param $data_postav - дата поставки
     * @param float $price - цена без НДС
     * @param string $modif - модификация
     * @param int $nds - НДС
     * @param int $val - валюта
     * @param int $Add
     * @param float $price_or - ориентировочная цена
     * @param string $data_nach - дата начала этапа
     */
    public function AddEdit($kod_elem, $numb, $data_postav, $price = 0., $modif = '', $nds = -1, $val = 1, $Add = 1, $price_or = 0., $data_nach = "")
    {
        if(!(strpos($data_postav,'w') === false))
        {
            $week = (int)func::clearNum($data_postav);
            $data_postav = func::datePlusWeek($week);
        }
        elseif(!(strpos($data_postav,'d') === false))
        {
            $day = (int)func::clearNum($data_postav);
            $data_postav = func::datePlusDay($day);
        }
        else
            $data_postav = func::Date_to_MySQL($data_postav);

        if ($nds < 0)
            $nds = config::$nds_main;

        if ($data_nach != "")
            $data_nach = func::Date_to_MySQL($data_nach);
        else
            $data_nach = "null";

        $numb = func::clearNum($numb);
        $price = func::clearNum($price, 2);
        $price_it = func::rnd($price * (100 + $nds) / 100);

        if ($price < config::$min_price) { // Если цена не задана
            $D = new Doc();
            $D->kod_dogovora = $this->kod_dogovora;
            $dataD = $D->getData();
            if ($Add == 1) // Прошлая цена только при создании
                $price_it = Elem::getLastPriceByOrg($kod_elem, $dataD['kod_org']); // Пытаемся получить прошлую цену по компании
            if ($price_it < config::$min_price)
                $price_it = Elem::getPriceForQuantity((int)$kod_elem, (int)$numb); // Пытаемся получить цену элемента из прайс-листа для указанного количества

            $price = func::rnd($price_it * 100 / (100 + $nds)); // Цена без НДС
        } elseif (isset($_POST['nds_yn']))
            if ((int)$_POST['nds_yn'] == 1) { // Если указана цена с НДС
                $price_it = $price; // Цена с НДС
                $price = func::rnd($price_it * 100 / (100 + $nds)); // Цена без НДС
            }
        $sum_part = func::rnd($price_it * $numb);

        $price_or = func::clearNum($price_or, 2);

        $kod_user = func::kod_user();

        $db = new Db();
        if ($Add == 1) // Если новая партия
        {
            $db->query(/** @lang MySQL */
                "INSERT INTO parts (kod_dogovora,kod_elem,numb,data_postav,price,price_it,modif,nds,val,kod_user,price_or,data_nach,sum_part) VALUES($this->kod_dogovora,$kod_elem,$numb,'$data_postav',$price,$price_it,'$modif',$nds,$val,$kod_user,$price_or,'$data_nach',$sum_part)");
        } else {
            Db::getHistoryString("parts", "kod_part", $this->kod_part);

            $db->query(/** @lang MySQL */
                "UPDATE parts SET kod_elem=$kod_elem, numb=$numb, data_postav='$data_postav',price=$price, price_it=$price_it, modif='$modif',nds=$nds,val=$val,edit=1,kod_user=$kod_user,price_or=$price_or,data_nach='$data_nach', sum_part=$sum_part WHERE kod_part=$this->kod_part");
        }
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма - Добавление или Редактирование партии
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit = 1)
    {

        //Данные
        $modif = "";
        $data_postav = func::NowE();
        $numb = 1;
        $price = "";
        $nds_checked = "checked";
        $nds_main = (int)config::$nds_main;
        $nds_str = $nds_main . "%";
        $nds_0_checked = "";
        $rub_checked = "checked";
        $usd_checked = "";
        $euro_checked = "";
        $form_name = "AddPart";
        $price_or_str = "";
        $data_nach_str = "";
        $price_it = 0;
        $nds_part_input = ""; // Строка для ввода НДС партии, если он отличается от nds_main

        $E = new Elem();

        if (config::$price_or == 1) // Форма Ориентировочной цены
        {
            $price_or_str = "<tr>
                                    <td>Цена ОР Без НДС</td>
                                    <td colspan='2'><input  name='price_or' id='price_or' value='' /></td>
                                  </tr>";
        }

        if (config::$data_nach == 1) // Форма Дата начала
        {
            $data_nach = func::NowE();

            $data_nach_str = "<tr>
                                    <td>Дата Начала</td>
                                    <td colspan='2'>
                                      <input  name='data_nach' id='data_nach' value='$data_nach' />
                                    </td>
                                  </tr>";
        }

        if ($Edit == 1) {

            $db = new Db();
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM parts WHERE del=0 AND kod_part=$this->kod_part");

            $row = $rows[0];//Данные
            $form_name = "EditPart";
            $kod_elem = (int)$row['kod_elem'];
            $modif = $row['modif'];
            $data_postav = Func::Date_from_MySQL($row['data_postav']);
            $numb = $row['numb'];
            $price = $row['price']; // Цена без НДС
            $price_or = $row['price_or']; // Цена ориентировочная
            $price_it = $row['price_it']; // Цена с НДС
            $val = (int)$row['val'];
            $data_nach_str = "";

            $nds_part = (int)$row['nds'];
            if ($nds_part != (int)$nds_main) {
                $nds_part_str = $nds_part . "%";
                $nds_part_input = "<input type='radio' name='nds' value='$nds_part' checked> $nds_part_str<br>";
            }

            $nds_0_checked = "";
            if ($nds_part == 0) {
                $nds_0_checked = "checked";
                $nds_checked = "";
            }

            if (config::$price_or == 1) // Форма Ориентировочной цены
            {
                $price_or_str = "<tr>
                                    <td>Цена ОР Без НДС</td>
                                    <td colspan='2'><input  name='price_or' id='price_or' value='$price_or' /></td>
                                  </tr>";
            }

            if (config::$data_nach == 1) {
                $data_nach = "";
                if (!($row['data_nach'] == NULL))
                    $data_nach = func::Date_from_MySQL($row['data_nach']);

                $data_nach_str = "<tr>
                                    <td>Дата Начала</td>
                                    <td colspan='2'>
                                      <input  name='data_nach' id='data_nach' value='$data_nach' />
                                    </td>
                                  </tr>";
            }

            if ($val == 1)
                $rub_checked = "checked";
            elseif ($val == 2)
                $usd_checked = "checked";
            else
                $euro_checked = "checked";

            $E->kod_elem = $kod_elem;
        }

        $nds_ex_checked = "checked"; // галочка "без НДС"
        $nds_inc_checked = "";       // галочка "вкл. НДС"
        if ($price_it > 0) {
            $nds_ex_checked = "";
            $nds_inc_checked = "checked";
            $price = $price_it;
        }

        $price_str = func::Rub($price);

        $res = /** @lang HTML */
            "<form id='form1' name='form1' method='post' action=''>
                <table border='0' cellspacing='0' width='100%'>
                  <tr>
                    <td width='100'>Элемент</td>
                    <td colspan='2'>" . $E->formSelList2() . "</td>
                  </tr>
                  <tr>
                    <td>Модификация</td>
                    <td colspan='2'><input size='100' name='modif' id='modif' value='$modif'/></td>
                  </tr>
                  $data_nach_str
                  <tr>
                    <td>Дата</td>
                    <td colspan='2'>
                         <input  name='data_postav' id='data_postav' value='$data_postav' />d-дней, w-недель
                     </td>
                  </tr>
                  <tr>
                    <td>Количество</td>
                        <td colspan='2'><input  name='numb' id='numb' value='$numb' /></td>
                  </tr>
                  $price_or_str
                  <tr>
                    <td>Цена</td>
                    <td  width='100'><input  name='price' id='price' value='$price_str' /></td>
                    <td>
                            <input type='radio' name='nds_yn' value='0' $nds_ex_checked>без НДС<br>
                            <input type='radio' name='nds_yn' value='1' $nds_inc_checked>вкл. НДС<br>
                    </td>                    
                  </tr>
                  <tr>
                       <td>НДС</td>
                       <td colspan='2'>
                            <input type='radio' name='nds' value='$nds_main' $nds_checked> $nds_str<br>
                            $nds_part_input
                            <input type='radio' name='nds' value='0' $nds_0_checked> 0%<br>
                       </td>
                  </tr>
                  <tr>
                   <td>Валюта</td>
                       <td colspan='2'>
                           <input type='radio' name='val' value='1' $rub_checked>RUR<br>
                           <input type='radio' name='val' value='2' $usd_checked>USD<br>
                           <input type='radio' name='val' value='3' $euro_checked>EURO<br>
                       </td>
                  </tr>
                </table>
            <input id='EditPartForm' type='hidden' value='1' name='$form_name'/>
            <input type='submit' value='Сохранить' />
            <br>
            </form>";

        $res .= Func::Cansel(0);
        return $res;
    }
//-------------------------------------------------------------------------
    //
    /**
     * Отметка о Получении Накладной
     * @param int $kod_oborota
     */
    public function setPostNacl($kod_oborota)
    {
        $db = new Db();
        $Date = date("Y-m-d");
        $db->query(/** @lang MySQL */
            "UPDATE sklad SET poluch=1, data_poluch='$Date' WHERE kod_oborota=$kod_oborota");
    }
//-------------------------------------------------------------------------
//
    /**
     * Удаление партии и связей
     * @param int $kod_part
     * @param int $recovery
     */
    public static function Delete($kod_part = 0, $recovery = 0)
    {
        if ($kod_part == 0)
            return;

        $del = 1;
        if ($recovery == 1)
            $del = 0;

        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE parts SET del=$del,kod_user=$kod_user WHERE kod_part=$kod_part");

        $db->query(/** @lang MySQL */
            "UPDATE raschet SET del=$del,kod_user=$kod_user WHERE kod_part=$kod_part");

        //todo - проверить
        $db->query(/** @lang MySQL */
            "UPDATE
                            raschety_plat
                           INNER JOIN raschet ON raschet.kod_rascheta = raschety_plat.kod_rascheta
                           SET raschety_plat.del=$del,raschety_plat.kod_user=$kod_user
                           WHERE raschet.kod_part=$kod_part
                          ");

        $db->query(/** @lang MySQL */
            "UPDATE sklad SET del=$del,kod_user=$kod_user WHERE kod_part=$kod_part");
    }
//-------------------------------------------------------------------------
//
    /**
     * Сумма платежей по расчету
     * @param $kod_rascheta
     * @return float
     */
    public static function getSummPlatByRasch($kod_rascheta)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM raschety_plat WHERE del=0 AND kod_rascheta=$kod_rascheta");

        $cnt = $db->cnt;
        if ($cnt == 0)
            return 0.;

        $res = 0.;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Данные
            $res += (double)$row['summa'];
        }

        return $res;

    }
//-------------------------------------------------------------------------
//
    /**
     * Сумма платежей по партии
     * @return float
     */
    public function getSummPlatByPart()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT 
                            Sum(IFNULL(raschety_plat.summa,0)) AS summa_plat,
                            raschet.kod_part
                    FROM
                                raschet
                    LEFT JOIN raschety_plat ON raschety_plat.kod_rascheta = raschet.kod_rascheta
                    WHERE
                                kod_part=$this->kod_part AND raschet.del=0
                    GROUP BY
                                raschet.kod_part");

        $cnt = $db->cnt;
        if ($cnt == 0)
            return 0.;

        $row = $rows[0];
        $res = (double)$row['summa_plat'];

        return $res;
    }
//
//-------------------------------------------------------------------------
    /**
     * // todo - нужно учитывать в какой валюте оплата и цена
     * Процент распределенных платежей от суммы партии
     * @param $rplan_row - поле 'sum_part'
     * @return float
     */
    public function getProcPayByPart($rplan_row)
    {
        $res = 0;
        $sum_part = $rplan_row['sum_part'];

        $summ_plat = $this->getSummPlatByPart();

        if ($summ_plat > 0 and $sum_part > 0)
            $res = func::Proc($summ_plat / $sum_part);

        return $res;
    }
//-------------------------------------------------------------------------

    /**
     * Цена без НДС
     * @param $rplan_row
     * @return float
     */
    public static function getPriceNoNDS($rplan_row)
    {
        $price_it = func::rnd($rplan_row['price_it']);
        if ($price_it >= 0.01) // Если указана цена с НДС то берем ее и вычитаем НДС
            return func::rnd($price_it * 100 / (100 + (int)$rplan_row['nds']));

        $price = func::rnd($rplan_row['price']);
        if ($price < 0.01 and config::$price_or == 1) // Берем ориентировочную
            $price = func::rnd($rplan_row['price_or']);

        return $price;
    }
//-------------------------------------------------------------------------

    /**
     * Цена с НДС
     * @param $rplan_row
     * @return float
     */
    public static function getPriceWithNDS($rplan_row)
    {
        $price_it = func::rnd($rplan_row['price_it']);
        if ($price_it > config::$min_price)
            return $price_it;

        $price = self::getPriceNoNDS($rplan_row);
        $nds = (int)$rplan_row['nds'];
        $summ_nds = 0;
        if ($nds > 0)
            $summ_nds = func::rnd($price * $nds / 100);

        $price_with_nds = $price + $summ_nds;
        return $price_with_nds;
    }
//-------------------------------------------------------------------------

    /**
     * Количество полученное по партии
     * @param $kod_part
     * @return int
     */
    public static function getNumbPoluch($kod_part)
    {
        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                        view_sklad_postuplenie.kod_part,
                        Sum(view_sklad_postuplenie.numb) AS summ_numb
                    FROM
                        view_sklad_postuplenie
                    WHERE
                        view_sklad_postuplenie.kod_part = $kod_part
                    GROUP BY
                        view_sklad_postuplenie.kod_part;");

        if ($db->cnt == 0)
            return 0;

        $res = $rows[0]['summ_numb'];

        return $res;
    }
//-------------------------------------------------------------------------

    /**
     * Обработчик событий
     */
    public function Events()
    {
        $event = false;

        if (isset($_POST['AddPart']) or isset($_POST['EditPart'])) {
            $add = 0;
            if (isset($_POST['AddPart']))
                $add = 1;

            if (isset($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'])) {
                $this->AddEdit($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'], $_POST['modif'], $_POST['nds'], $_POST['val'], $add, $_POST['price_or'], $_POST['data_nach']);
                $event = true;
            }
        }
        if (isset($_POST['kod_oborota_poluch'])) { // Получение накладной
            $this->setPostNacl($_POST['kod_oborota_poluch']);
            $event = true;
        } elseif (isset($_POST['kod_oborota_del'])) { // Удаление накладной
            $this->DelNaklad($_POST['kod_oborota_del']);
            $event = true;
        }

        if (isset($_POST['AddEditNacl']))
            if (isset($_POST['numb'], $_POST['naklad'], $_POST['data'], $_POST['kod_oper'])) {
                $this->AddNaklad($_POST['numb'], $_POST['naklad'], $_POST['data'], $_POST['kod_oper']);
                $event = true;
            }

        if (isset($_POST['summa']) and isset($_POST['data']) and isset($_POST['type_rascheta'])) {
            $this->AddRasch($_POST['summa'], $_POST['data'], $_POST['type_rascheta']);
            $event = true;
        }

        if (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == 'AddRasch100') {
                $this->AddRasch100();
                $event = true;
            } elseif ($_POST['Flag'] == 'DelRasch' and isset($_POST['kod_rascheta_del'])) {
                $this->DelRasch($_POST['kod_rascheta_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'DelPart' and isset($_POST['kod_part_del'])) {
                $this->Delete($_POST['kod_part_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'CopyPartToDoc' and isset($_POST['kod_part_copy'], $_POST['kod_dogovora'])) {
                $addPartLink = false;
                if (isset($_POST['addPartLink']))
                    $addPartLink = true;
                self::copyToDoc((int)$_POST['kod_part_copy'], (int)$_POST['kod_dogovora'], $addPartLink);
                $event = true;
            } elseif ($_POST['Flag'] == 'EditSumPart' and isset($_POST['kod_part'], $_POST['sum_part'])) {
                self::setSumPart($_POST['kod_part'], $_POST['sum_part']);
                $event = true;
            } elseif ($_POST['Flag'] == 'AddPOtoPart' and isset($_POST['kod_part_master'], $_POST['kod_org'])) {
                $this->addPO($_POST['kod_part_master'], $_POST['kod_org']);
                $event = true;
            } elseif ($_POST['Flag'] == 'kod_part_status' and isset($_POST['kod_part'])) {
                $this->addStatus();
                $event = true;
            }
        }

        if (isset($_POST['summa']) and isset($_POST['kod_rascheta']) and isset($_POST['kod_plat'])) {
            $this->AddPayToRas($_POST['summa'], $_POST['kod_rascheta'], $_POST['kod_plat']);
            $event = true;
        }

        if (isset($_POST['AVPr'], $_POST['data'])) {
            $pr = (double)$_POST['AVPr'];
            $pr = round($pr / 100, 2);

            if ($pr > 0. and $pr <= 1.) {
                $this->SetPayGraph($_POST['data'], $pr);
                $event = true;
            }
        }

        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//-------------------------------------------------------------------------

    /**
     * Возвращает код первой партии в договоре
     * @param $kod_dogovora
     * @return mixed
     */
    public static function getFirstPartKod($kod_dogovora)
    {
        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT kod_part FROM parts WHERE del=0 AND kod_dogovora=$kod_dogovora ORDER BY kod_part;");
        return $rows[0]['kod_part'];
    }
//-----------------------------------------------------------------------
//
    /**
     * Нужна колонка kod_part - ALTER TABLE dogovor_prim ADD COLUMN kod_part INT(11) AFTER kod_dogovora
     * Форма - Примечание партии
     * @param int $Del
     * @return string
     */
    public function formPrim($Del = 1)
    {
        $add_prim = 0;
        if (isset($_POST['Flag']))
            if ($_POST['Flag'] == 'AddPrim')
                $add_prim = 1;

        $db = new Db();
        $res = "";
        // Примечание
        $btd_add = Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Примечание', 'AddPrim', "kod_part", $this->kod_part);

        if ($add_prim == 1 and isset($_POST['kod_part'])) {
            if ($_POST['kod_part'] == $this->kod_part) {
                $res = "<form name='form1' method='post' action=''>
                                      <table width='416' border='0'>
                                        <tr>
                                          <td width='185'>Примечание</td>
                                          <td width='215'><span id='sprytextfield'>
                                            <textarea name='Prim' id='Prim' cols='70' rows='3'></textarea>
                                          <span class='textfieldRequiredMsg'>Необходимо ввести значение.</span></span></td>
                                        </tr>
                                        <tr>
                                          <td><input type='submit' name='button' id='button' value='Добавить' /></td>
                                        <td>&nbsp;</td>
                                        </tr>
                                      </table>
                                    <input type='hidden' name='AddPrim' value='1' />
                                    <input type='hidden' name='kod_part' value='$this->kod_part' />
                    </form>";
                $res .= Func::Cansel();
                return $res;
            }
        } else
            $res = $btd_add;

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * 
                                  FROM dogovor_prim 
                                  WHERE kod_part=$this->kod_part AND dogovor_prim.del=0 
                                  ORDER BY dogovor_prim.time_stamp DESC
                                  ");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return $res;

        // Формируем таблицу
        $res = '
                    <div class="btn">
                        <div>Примечание</div><div>' . $btd_add . '</div>
                    </div>
                    <table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC" >
                        <td width="80">Дата</td>
                        <td width="100%">Текст</td>
                    </tr>';

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
//
    /**
     * Форма Цена без НДС
     * @param $rplan_row
     * @return string
     */
    public static function formPrice($rplan_row)
    {
        $price = $rplan_row['price'];
        $price_str = func::Rub($price);

        if ($price == 0)
            $price_str = "";

        if (func::rnd($rplan_row['price_or']) >= config::$min_price)
            $price_str = "<b>" . Func::Rub(func::rnd($rplan_row['price_or'])) . "</b><br>" . $price_str;

        return $price_str;
    }
//-----------------------------------------------------------------------
//
    /**
     * @param $kod_part
     * @return array|bool
     */
    public static function getData($kod_part)
    {
        $db = new Db();
        $kod_part = (int)$kod_part;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_rplan WHERE kod_part=$kod_part");

        if ($db->cnt == 0)
            return false;

        return $rows[0];
    }
//-----------------------------------------------------------------------
//
    /**
     * Копировать партию в договор
     * @param $kod_part
     * @param $kod_dogovora
     * @param bool $add_link
     * @return int
     */
    public static function copyToDoc($kod_part, $kod_dogovora, $add_link = false)
    {
        $kod_user = func::kod_user();
        $kod_part = (int)$kod_part;
        $kod_dogovora = (int)$kod_dogovora;
        $data_postav = date("Y-m-d");

        $data = self::getData($kod_part);
        $nds = config::$nds_main; // Подставляем текущий НДС
        $price = $data['price'];
        $price_it = func::rnd($price * (100 + $nds) / 100);
        $sum_part = func::rnd($price_it * $data['numb']);

        $db = new Db();
        $db->query(/** @lang MySQL */
            "INSERT INTO parts (kod_dogovora,kod_elem,numb,data_postav,price,price_it,sum_part,modif,nds,val,kod_user,price_or,data_nach) 
                              SELECT $kod_dogovora,kod_elem,numb,'$data_postav',price,$price_it,$sum_part,modif,$nds,val,$kod_user,price_or,data_nach 
                              FROM parts WHERE kod_part=$kod_part;");
        $kod_part_slave = $db->last_id;

        if ($add_link)
            self::addLink($kod_part, $kod_part_slave);

        return $kod_part_slave;
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма копирования в договор
     * @param bool $btb
     * @return string
     */
    public function formCopyToDoc($btb = true)
    {
        if (func::user_group() !== "admin")
            return "";

        if ($btb === true)
            return "<div>" . Func::ActButton2('', "Копировать", 'CopyPart', 'kod_part_copy', $this->kod_part) . "</div>";

        $data = self::getData($this->kod_part);
        $kod_dogovora = $data['kod_dogovora'];

        $res = "";
        if (isset($_POST['Flag'], $_POST['kod_part_copy']))
            if ($_POST['Flag'] == "CopyPart" and (int)$_POST['kod_part_copy'] == $this->kod_part) {
                $db_doc = new Db();

                // Пробуем найти подчиненный договор
                // todo - надо упростить и по-другому выбирать договор - по соответствию элемента, либо как составная часть комплекта
                $rows_links = $db_doc->rows(/** @lang MySQL */
                    "SELECT   `doc_links`.`kod_doc_master`,
                                     `doc_links`.`kod_doc_slave`
                            FROM     `doc_links`
                            WHERE kod_doc_master=$kod_dogovora AND del=0;");

                $addPartLink = "";
                if ($db_doc->cnt > 0) {
                    $kod_dogovora = $rows_links[0]['kod_doc_slave'];
                    $addPartLink = "<input type='hidden' name='addPartLink' value='1'>";
                }
                // todo - Может потребоваться выбрать закрытый договор
                $rows_doc = $db_doc->rows(/** @lang MySQL */
                    "SELECT * FROM view_dogovor_data WHERE zakryt=0 ORDER BY nomer;");

                $res = /** @lang HTML */
                    "<div>
                        <form method='post'>
                        " . Doc::formSelList($rows_doc, $kod_dogovora) . "
                        <input type='hidden' name='kod_part_copy' value='$this->kod_part'>
                        <input type='hidden' name='Flag' value='CopyPartToDoc'>
                        <input type='submit' value='Копировать'>
                        $addPartLink
                        </form>
                        " . func::Cansel() . "
                    </div>";
            }
        return $res;
    }
//-----------------------------------------------------------------------
//
    /**
     * @param $kod_part
     * @return string
     */
    public static function getLastNaklDate($kod_part)
    {
        $db = new Db();
        $kod_part = (int)$kod_part;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM sklad WHERE kod_part=$kod_part order by data desc;");

        if ($db->cnt == 0)
            return "NULL";
        else
            return func::Date_from_MySQL($rows[0]['data']);
    }
//-----------------------------------------------------------------------
//
    /**
     * Установка суммы партии
     * @param $kod_part
     * @param $sum_part - сумма партии
     */
    public static function setSumPart($kod_part, $sum_part)
    {
        $db = new Db();
        $kod_part = (int)$kod_part;
        $sum_part = func::clearNum($sum_part, 2);

        $row = self::getData($kod_part);

        $numb = $row['numb'];

        if ($numb == 0)
            $numb = 1;

        $nds = (int)$row['nds'];

        Db::getHistoryString("parts", "kod_part", $kod_part);

        $price_it = func::rnd($sum_part / $numb);
        $price = func::rnd(($sum_part * 100 / (100 + $nds)) / $numb);

        $db->query(/** @lang MySQL */
            "UPDATE parts SET sum_part=$sum_part, price_it=$price_it, price=$price WHERE kod_part=$kod_part;");
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма изменения Суммы партии
     * @return string
     */
    public function formSumPart()
    {
        $kod_part = $this->kod_part;

        $row = self::getData($kod_part);

        $res = /** @lang HTML */
            '<form id="formSumPart" name="formSumPart" method="post" action="">
                <table width="200" border="0">
                              <tr>
                                <td>Сумма с НДС</td>
                                <td><input name="sum_part" value="' . func::Rub($row['sum_part']) . '" /></td>
                              </tr>
                            </table>
                <input type="hidden" name="Flag" value="EditSumPart" />
                <input type="hidden" name="kod_part" value=' . $kod_part . ' />
                <input type="submit" name="button" id="button" value="Сохранить" />
                </form>
                ';
        $res .= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', 'Cansel');

        return $res;
    }
//-----------------------------------------------------------------------
//
    /**
     * Сборка для партии
     *
     */
    public function formPartSet()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM part_set WHERE kod_part=$this->kod_part");

        if ($db->cnt == 0)
            return "Список элементов пуст.";

        $res = /** @lang HTML */
            "<table width='100%' border='1'>
                <tr>
                    <td>№</td>
                    <td>Наименование</td>
                    <td>Код</td>
                    <td width='50'>Кол-во</td>
                    <td>Примечание</td>
                </tr>";

        $sum = 0;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $name = $row['name'];
            $kod = $row['kod_1c'];
            $numb = $row['numb'];
            $sum += (double)$row['sum'];
            $n = $i + 1;

            $res .= /** @lang HTML */
                "<tr>
                    <td>$n</td>
                    <td>$name</td>
                    <td>$kod</td>
                    <td align='right'>$numb</td>
                    <td></td>
                </tr>";
        }
        $res .= "</table>";
        $res .= func::Rub($sum);

        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление позиции в комплектацию
     * @param $kod_item
     * @param $add_type
     */
    public function addItemToSet($kod_item, $add_type)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM sklad_1c WHERE kod_item=$kod_item");

        if ($db->cnt == 0)
            exit("Список элементов пуст.");

        $set_id = 0;
        if(isset($_GET['set_id']))
            if((int)$_GET['set_id'] > 0)
                $set_id = (int)$_GET['set_id'];

        $row = $rows[0];

        $numb_1c = $row['numb'];
        if ($numb_1c < 0.001) // Если брать нечего
            return;

        $name = $row['name'];
        $kod_1c = $row['kod_1c'];
        $price = $row['price'];

        $part_data = self::getData($this->kod_part);

        $numb = 1;
        if (!isset($add_type) or $add_type == 1) {
            $numb = $part_data['numb'];

            if (isset($_GET['numb'])) // Вручную задается количество которое надо добавить в комплектацию
                $numb = (int)$_GET['numb'];
        } elseif ($add_type == 0)
            $numb = $numb_1c;

        if ($numb > $numb_1c)
            $numb = $numb_1c;

        $sum = func::rnd($numb * $price);

        // Проверка, если позиция уже есть в комплектации то обновляем ее
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM part_set WHERE kod_part=$this->kod_part AND kod_1c='$kod_1c' AND del=0 AND set_id=$set_id;");
        if ($db->cnt > 0) {
            $row_1 = $rows[0];
            $numb_old = $row_1['numb'];
            $kod_item_ps = $row_1['kod_item']; // код позиции из таблицы part_set
            $db->query(/** @lang MySQL */
                "UPDATE part_set SET numb=($numb_old + $numb) WHERE kod_item=$kod_item_ps AND set_id=$set_id;");
        } else {
            $sql = /** @lang MySQL */
                "INSERT INTO part_set (name, kod_1c, price, numb, sum, kod_part,set_id) VALUES('$name','$kod_1c',$price,$numb,$sum,$this->kod_part,$set_id);";
            $db->query($sql);
        }

        $numb = func::rnd($row['numb'] - $numb);

        $sql = /** @lang MySQL */
            "UPDATE sklad_1c SET numb=$numb, sum=ROUND(price*numb,2) WHERE kod_item=$kod_item";
        $db->query($sql);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление позиции из комплектации
     * @param $kod_item
     */
    public function deleteItemFromSet($kod_item)
    {
        $db = new Db();
        $db->query(/** @lang MySQL */
            "UPDATE part_set SET del=1 WHERE kod_item=$kod_item;");

        $rows = $db->rows(/** @lang MySQL */
            "SELECT numb,kod_1c FROM part_set WHERE kod_item=$kod_item");

        if ($db->cnt == 0)
            return;

        $row = $rows[0];
        $numb = $row['numb'];
        $kod_1c = $row['kod_1c'];

        // todo - надо подумать, по идее если на сладе нет, то позицию надо добавить

        $db->query(/** @lang MySQL */
            "UPDATE sklad_1c SET numb=(numb+$numb), sum=ROUND(price*numb,2) WHERE kod_1c='$kod_1c';");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Изменение количества позиции в комплектации
     * @param $kod_item
     * @param $numb
     */
    public function setItemNumb($kod_item, $numb)
    {
        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT numb,kod_1c FROM part_set WHERE kod_item=$kod_item");
        $row = $rows[0];
        $numb_old = $row['numb'];
        $kod_1c = $row['kod_1c'];

        // todo - должна быть проверка количества, нельзя взять больше чем есть
        // Проверка - сколько осталось по данным 1С
        if (!isset($_GET['all'])) {
            $rows = $db->rows(/** @lang MySQL */
                "SELECT numb FROM sklad_1c WHERE kod_1c='$kod_1c';");

            if ($db->cnt > 0) {
                $row = $rows[0];
                $numb_1c = $row['numb'];
                $numb_max = $numb_old + $numb_1c; // Максимальное количество которое можно взять со склада

                if ($numb > $numb_max)
                    $numb = $numb_max;
            }
        }

        $db->query(/** @lang MySQL */
            "UPDATE part_set SET numb=$numb,sum=ROUND(price*$numb,2) WHERE kod_item=$kod_item");

        $db->query(/** @lang MySQL */
            "UPDATE sklad_1c SET numb=(numb+$numb_old-$numb), sum=ROUND(price*(numb+$numb_old-$numb),2) WHERE kod_1c='$kod_1c';");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Список всех сборок
     * @return string
     */
    public static function formSetList()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT 
                        kod_dogovora,
                        nomer,
                        view_rplan.numb,
                        kod_org,
                        nazv_krat,
                        view_rplan.kod_part,
                        kod_elem,
                        modif,
                        MAX(kod_item) AS kod_item,
                        view_rplan.name
                    FROM view_rplan JOIN part_set ON part_set.kod_part=view_rplan.kod_part
                    WHERE part_set.del=0
                    GROUP BY view_rplan.kod_part
                    ORDER BY kod_item DESC;");

        if ($db->cnt == 0)
            return "Список элементов пуст.";

        $res = /** @lang HTML */
            "<table width='100%' border='1'>
                <tr>
                    <td>№</td>
                    <td>Организация</td>
                    <td>Код</td>
                    <td width='50'>Кол-во</td>
                </tr>";

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $nomer = $row['nomer'];
            $kod_dogovora = (int)$row['kod_dogovora'];
            $kod_org = (int)$row['kod_org'];
            $nazv_krat = $row['nazv_krat'];
            $kod_part = (int)$row['kod_part'];
            $name_invoice = Elem::getNameForInvoice($row);
            $numb = $row['numb'];

            $res .= /** @lang HTML */
                "<tr>
                    <td><a href='form_dogovor.php?kod_dogovora=$kod_dogovora'>$nomer</a></td>
                    <td><a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a></td>
                    <td><a href='form_set.php?kod_part=$kod_part'>$name_invoice</a></td>
                    <td align='right'>$numb</td>
                </tr>";
        }
        $res .= "</table>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление всей комплектации по партии
     *
     */
    public function deleteSet()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM part_set WHERE kod_part=$this->kod_part AND del=0;");
        if ($db->cnt == 0)
            return;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $kod_item = (int)$row['kod_item'];
            $this->deleteItemFromSet($kod_item);
        }
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Копирует состав комплектации из партии источника в текущую партию
     * @param $kod_part_source - код источника
     */
    public function copyItemsToPart($kod_part_source)
    {
        $data = self::getData($this->kod_part);
        $numb = $data['numb'];

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM part_set WHERE kod_part=$kod_part_source AND del=0;");
        if ($db->cnt == 0)
            return;

        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_1c = (int)$row['kod_1c'];

            $rows_1c = $db->rows(/** @lang MySQL */
                "SELECT * FROM sklad_1c WHERE kod_1c='$kod_1c';");
            if ($db->cnt == 0)
                continue;

            $kod_item = $rows_1c[0]['kod_item'];

            $this->addItemToSet($kod_item, $numb);
        }
    }
//----------------------------------------------------------------------
//
    /**
     * Добавление связи партий и договоров - для отслеживания заказов
     * @param $kod_part_master
     * @param $kod_part_slave
     */
    public static function addLink($kod_part_master, $kod_part_slave)
    {
        $kod_part_master = (int)$kod_part_master;
        $kod_part_slave = (int)$kod_part_slave;

        if ($kod_part_master == 0 or $kod_part_slave == 0 or ($kod_part_master == $kod_part_slave))
            return;

        $db = new Db();
        // Проверяем наличие связи (прямой или обратной)
        $db->rows(/** @lang MySQL */
            "SELECT * FROM part_links WHERE (kod_part_master=$kod_part_master AND kod_part_slave=$kod_part_slave) OR (kod_part_master=$kod_part_slave AND kod_part_slave=$kod_part_master);");
        if ($db->cnt > 0)
            return;

        $kod_user = func::kod_user();
        $db->query(/** @lang MySQL */
            "INSERT INTO part_links(kod_part_master, kod_part_slave, kod_user) VALUES($kod_part_master,$kod_part_slave,$kod_user);");

        // Получаем код основного договора
        $rows = $db->rows(/** @lang MySQL */
            "SELECT kod_dogovora FROM parts WHERE kod_part=$kod_part_master");
        if ($db->cnt > 0)
            $kod_dogovora_master = $rows[0]['kod_dogovora'];
        else
            return;
        // Получаем код подчиненного договора
        $rows = $db->rows(/** @lang MySQL */
            "SELECT kod_dogovora FROM parts WHERE kod_part=$kod_part_slave");
        if ($db->cnt > 0)
            $kod_dogovora_slave = $rows[0]['kod_dogovora'];
        else
            return;

        // Создаем связь для договоров
        Doc::addLink($kod_dogovora_master, $kod_dogovora_slave);
    }
//----------------------------------------------------------------------
//
    /**
     * Добавление заказа к партии - если требуется заказать производство/товар у контрагента
     * @param $kod_part
     * @param $kod_ispolnit
     * @param int $doc_type
     */
    public static function addPO($kod_part, $kod_ispolnit, $doc_type = 3)
    {
        $d = new Doc();
        $nomer = "PO";
        $data_sos = func::NowE();
        $kod_org = config::$kod_org_main;
        $kod_ispolnit = (int)$kod_ispolnit;
        $kod_part = (int)$kod_part;
        $kod_dogovora = $d->Add($nomer, $data_sos, $kod_org, $kod_ispolnit, $doc_type);
        $kod_part_slave = self::copyToDoc($kod_part, $kod_dogovora, true);
        self::addLink($kod_part, $kod_part_slave);
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма создания заказа по партии
     * @param bool $btb
     * @return string
     */
    public function formAddPO($btb = true)
    {
        if ($btb)
            return "<div>" . Func::ActButton2('', "PO", 'AddPO', 'kod_part_master', $this->kod_part) . "</div>";
        $res = "";
        if (isset($_POST['Flag'], $_POST['kod_part_master']))
            if ($_POST['Flag'] == "AddPO" and (int)$_POST['kod_part_master'] == $this->kod_part) {
                $res = /** @lang HTML */
                    "<div>
                        <form method='post'>
                        " . Org::formSelList2() . "
                        <input type='hidden' name='kod_part_master' value='$this->kod_part'>
                        <input type='hidden' name='Flag' value='AddPOtoPart'>
                        <input type='submit' value='Создать заказ'>
                        </form>
                        " . func::Cansel() . "
                    </div>";
            }
        return $res;
    }
//-----------------------------------------------------------------------
//
    /**
     * Связанные партии
     *
     */
    public function formLinkedParts()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM part_links WHERE (kod_part_master=$this->kod_part OR kod_part_slave=$this->kod_part) AND part_links.del=0;");
        if ($db->cnt == 0)
            return "";

        $cnt = $db->cnt;
        $res = "";
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $link = "S";
            if ($row['kod_part_master'] == $this->kod_part)
                $kod_part = $row['kod_part_slave'];
            else {
                $link = "M";
                $kod_part = $row['kod_part_master'];
            }
            $res .= "<a href='form_part.php?kod_part=$kod_part'>$link</a>";
        }

        return $res;
    }

//----------------------------------------------------------------------
//
    /**
     * Вывод списка-выбора
     * @param $rows array
     * @param $kod_part_selected int
     * @return string
     */
    private static function formSelList($rows = [], $kod_part_selected = 0)
    {
        $cnt = count($rows);

        if ($cnt == 0) {
            $db = new Db();
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM view_rplan WHERE zakryt=0 ORDER BY nomer;");
            $cnt = $db->cnt;
        }

        if ($cnt == 0)
            return "";

        $res = /** @lang HTML */
            "<select id='kod_part' name='kod_part' placeholder=\"Выбрать партию...\">";

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $nomer = $row['nomer'];
            $nazv_krat = $row['nazv_krat'];
            if ($row['kod_org'] == config::$kod_org_main)
                $nazv_krat = "* " . $row['ispolnit_nazv_krat'];
            $kod_dogovora = $rows[$i]['kod_dogovora'];
            $kod_part = $rows[$i]['kod_part'];
            $name = $rows[$i]['obozn'];

            $selected = "";
            if ($rows[$i]['kod_part'] == $kod_part_selected)
                $selected = " selected='selected'";

            $res .= /** @lang HTML */
                "<option value='$kod_part' $selected>$nomer $nazv_krat $name $kod_dogovora $kod_part</option>\r\n";
        }
        $res .= /** @lang HTML */
            '</select>
                    <script type="text/javascript">
                                    var kod_part, $kod_part;
                
                                    $kod_part = $("#kod_part").selectize({
                                        onChange: function(value) {
                        if (!value.length) return "";
                    }
                                    });
                        kod_part = $kod_part[0].selectize;
                </script>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Форма истории изменений партии
     * @param $kod_part
     * @return string
     */
    public static function formHistory($kod_part)
    {
        $rows = func::getHistory('parts', $kod_part);
        $cnt = count($rows);

        if ($cnt == 0)
            return "Нет данных: formHistory";

        $E = new Elem();

        $res = "<table><tr>
                            <td>Наименование</td>
                            <td>Модификация</td>
                            <td>Кол-во</td>
                            <td>Дата</td>
                            <td width='100'>Цена с НДС</td>
                            <td width='130'>Сумма</td>
                            <td width='130'>Оператор</td>                            
                       </tr>";
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $E->kod_elem = (int)$row['kod_elem'];
            $name = $E->getFormLink();
            $modif = $row['modif'];
            $numb = $row['numb'];
            $data_postav = func::Date_from_MySQL($row['data_postav']);
            $price_it = func::Rub($row['price_it']);
            $sum_part = func::Rub($row['sum_part']);
            $kod_user = $row['kod_user'];

            $res .= "<tr>
                            <td>$name</td>
                            <td>$modif</td>
                            <td>$numb</td>
                            <td align='center'>$data_postav</td>
                            <td align='right'>$price_it</td>
                            <td align='right'>$sum_part</td>
                            <td>$kod_user</td>
                    </tr>";
        }
        $res .= "</table>";
        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Добавления статуса партии: 1 - Отгружено; 2 - Упаковано(Готов к отгрузке); 3 - Возврат на склад (переупаковка);
     */
    public function addStatus()
    {
        $type = self::getStatus($this->kod_part);

        if ($type == 0)
            $type = 2;
        else
            $type = 1;

        $kod_user = func::kod_user();

        $db = new Db();
        $db->query(/** @lang MySQL */
            "INSERT INTO part_status (kod_part,type,kod_user) VALUES($this->kod_part,$type,$kod_user)");
    }
//----------------------------------------------------------------------
//
    /**
     * Получение текущего статуса партии
     * @param $kod_part
     * @return int
     */
    public static function getStatus($kod_part)
    {
        $kod_part = (int)$kod_part;
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */ "SELECT * FROM part_status WHERE kod_part=$kod_part AND del=0 ORDER BY time_stamp DESC;");

        if ($db->cnt == 0)
            return 0;

        return (int)$rows[0]['type'];
    }

//----------------------------------------------------------------------
//
    /**
     * Форма состояния партии
     * @param $kod_part
     * @return string
     */
    public function formStatus($kod_part)
    {
        $kod_part = (int)$kod_part;
        $type = self::getStatus($kod_part);

        if ($type == 1)
            return "";

        $btn = "Отгружено";
        if ($type == 0)
            $btn = "Упаковано";

        return func::ActButton2("", $btn, "kod_part_status", "kod_part", $kod_part, "Подтвердите действие");
    }

//----------------------------------------------------------------------
//
    /**
     * Примечание по партии
     * @param $kod_part
     * @return string
     */
    public static function formPrimTable($kod_part)
    {
        $res = "";

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * 
                                  FROM dogovor_prim 
                                  WHERE kod_part=$kod_part AND dogovor_prim.del=0 
                                  ORDER BY dogovor_prim.time_stamp DESC
                                  ");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return $res;

        // Формируем таблицу
        $res = ' <table border=1 cellspacing=0 cellpadding=0 width="100%">';

        // Заполняем данными
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $user = "";
            if ($row['user'] != "")
                $user = "<br>" . $row['user'];

            $res .= /** @lang HTML */
                '<tr>
                        <td>' . Func::Date_from_MySQL($row['time_stamp']) . $user . '</td>
                        <td width="100%">' . $row['text'] . '</td>
                     </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Форма удаленных партий
     * @return string
     */
    public function formPartsDeleted()
    {
        $res = "";
        $sql = /** @lang MySQL */
            "select `trin`.`dogovory`.`kod_dogovora`                                            AS `kod_dogovora`,
                           `trin`.`dogovory`.`nomer`                                                   AS `nomer`,
                           `trin`.`dogovory`.`doc_type`                                                AS `doc_type`,
                           `trin`.`org`.`kod_org`                                                      AS `kod_org`,
                           `trin`.`org`.`nazv_krat`                                                    AS `nazv_krat`,
                           `trin`.`parts`.`modif`                                                      AS `modif`,
                           `trin`.`parts`.`numb`                                                       AS `numb`,
                           `trin`.`parts`.`data_postav`                                                AS `data_postav`,
                           `trin`.`parts`.`nds`                                                        AS `nds`,
                           `trin`.`parts`.`sum_part`                                                   AS `sum_part`,
                           `trin`.`parts`.`val`                                                        AS `val`,
                           `trin`.`parts`.`price`                                                      AS `price`,
                           parts.del                                                                   AS del,
                           `trin`.`elem`.`kod_elem`                                                    AS `kod_elem`,
                           `trin`.`elem`.`obozn`                                                       AS `obozn`,
                           `trin`.`elem`.`shifr`                                                       AS `shifr`,
                           `trin`.`parts`.`kod_part`                                                   AS `kod_part`,
                           ifnull(`trin`.`dogovory`.`zakryt`, 0)                                       AS `zakryt`,
                           `trin`.`dogovory`.`kod_ispolnit`                                            AS `kod_ispolnit`,
                           `trin`.`elem`.`name`                                                        AS `name`,
                           `ispolnit`.`nazv_krat`                                                      AS `ispolnit_nazv_krat`,
                           ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`, 0)                           AS `numb_otgruz`,
                           (`trin`.`parts`.`numb` - ifnull(`view_sklad_summ_otgruz`.`summ_otgruz`, 0)) AS `numb_ostat`,
                           `trin`.`parts`.`price_or`                                                   AS `price_or`,
                           `trin`.`parts`.`data_nach`                                                  AS `data_nach`,
                           `trin`.`parts`.`price_it`                                                   AS `price_it`,
                           `trin`.`elem`.`shablon`                                                     AS `shablon`
                    from (((((`trin`.`dogovory` join `trin`.`parts` on ((`trin`.`dogovory`.`kod_dogovora` = `trin`.`parts`.`kod_dogovora`))) join `trin`.`org` on ((`trin`.`org`.`kod_org` = `trin`.`dogovory`.`kod_org`))) join `trin`.`elem` on ((`trin`.`elem`.`kod_elem` = `trin`.`parts`.`kod_elem`))) join `trin`.`org` `ispolnit` on ((`ispolnit`.`kod_org` = `trin`.`dogovory`.`kod_ispolnit`)))
                             left join `trin`.`view_sklad_summ_otgruz`
                   on ((`trin`.`parts`.`kod_part` = `view_sklad_summ_otgruz`.`kod_part`)))
            where parts.del=1 AND parts.kod_dogovora=$this->kod_dogovora ORDER BY data_postav DESC;";

        try {
            $res = $this->formParts(0, $sql, 0);
        } catch (Exception $e) {
        }

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Показывает, больше или меньше цена элемента по сравнению с текущим прайсом
     * @param array $rplan_row - строка запроса rplan
     * @return string
     */
    public static function formPriceIndicator(array $rplan_row)
    {
        $kod_elem = $rplan_row['kod_elem'];
        $numb = $rplan_row['numb'];
        $price_elem_for_numb = Elem::getPriceForQuantity($kod_elem, $numb);

        if ($price_elem_for_numb < config::$min_price)
            return "";

        $price_it = $rplan_row['price_it'];
        if ($price_it > $price_elem_for_numb)
            return "<img src='img/up.png' alt='more'>";
        elseif ($price_it < $price_elem_for_numb)
            return "<img src='img/down.png' alt='less'>";

        return "";
    }

//----------------------------------------------------------------------------------------------------------------------
    /**
     * Возвращает сумму сборки без НДС
     * @param $kod_part
     * @return string
     */
    public static function getPartSetSumm($kod_part)
    {
        $db = new Db();
        $rows = $db->rows("SELECT sum(part_set.sum) AS summ_set FROM part_set WHERE kod_part=$kod_part AND del=0;");
        if ($db->cnt < 1)
            return 0;
        return $rows[0]['summ_set'];
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Выводит процент прибыли
     * @param $kod_part
     * @return string
     */
    public static function formPartProfitProc($kod_part)
    {
        $sum_set = self::getPartSetSumm($kod_part);
        if (!($sum_set > 0))
            return "";

        $sum_set = $sum_set * (100 + config::$nds_main) / 100; // Сумма с НДС
        $part_data = self::getData($kod_part);
        $ostat = $part_data['sum_part'] - $sum_set;
        $prc = func::rnd((100 * $ostat) / $part_data['sum_part'], 0);
        return $prc . "%";
    }
}