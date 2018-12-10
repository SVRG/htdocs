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
                "SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora ORDER BY data_postav ASC"; // Сначала старые партии

        $rows = $db->rows($sql);
        $cnt = $db->cnt;

        $btn_auto_ras = '';
        $btn_add_100 = '';

        // Если вызов списка партий - выводим кнопки Добавить партию и Авто-Расчет 100%
        // Если вызов из формы Партия - выводим только Авторасчет
        if ($this->kod_part != 0)
            $btn_auto_ras = "<div>" . Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora&kod_part=" . $this->kod_part, 'Авто-Расчет', 'AddAVOK') . "</div>";
        else {
            if (func::user_group() == "admin") // todo - Придумать глобальную политику прав
                $btn_add_100 = "<div>" . Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora", 'Авто-Расчет 100%', 'AddRasch100') . "</div>";
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
            $sum_part_form = Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], "Изменить", 'formSumPart', "kod_part_edit_sum", $row['kod_part']);;
            if(isset($_POST['kod_part_edit_sum']))
                if ($_POST['kod_part_edit_sum'] == $this->kod_part)
                    $sum_part_form = $this->formSumPart();

            // Цена без НДС-------------------------------------------------------------------------------
            $price_str = self::formPrice($row);

            // Дата поставки------------------------------------------------------------------------------
            $data_postav = Func::Date_from_MySQL($row['data_postav']);
            $data_postav_str = $data_postav;
            if (isset($row['data_nach'])) {
                if ($row['data_nach'] !== '0000-00-00')
                    $data_postav_str = Func::Date_from_MySQL($row['data_nach']) . "<br>" . $data_postav; // Дата начала
            }

            // Окраска отруженных/полученных партий в зелёный
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
                $drem = Func::DaysRem($row['data_postav']);

                // Если осталось меньше 30 и больше 14 дней то красим в оранжевый
                if ($drem <= 14)
                    $ind = "bgcolor='#F18585'";// Красный
                elseif ($drem <= 30)
                    $ind = 'bgcolor="#FFD222"';// Оранжевый
            }

            //--------------------------
            // НДС
            $NDS = '';

            if ((int)$row['nds'] != (int)config::$nds_main)
                $NDS = '<br>НДС ' . (int)$row['nds'] . '%';

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

            //Примечание партии
            $prim = $this->formPrim();

            // Кнопка редактирования партии
            // todo - Придумать ограничение на редактирование/удаление если была оплата (не выводить кнопку редактирования)
            $btn_edit = Func::ActButton("form_part.php?kod_part=" . $row['kod_part'] . '&kod_dogovora=' . $this->kod_dogovora, 'Изменить', 'EditPartForm');
            $btn_del = "";
            $btn_po = "";
            $btn_rfq = "";
            $kod_org_main = config::$kod_org_main;
            if ($row['kod_org'] == $kod_org_main) {
                $btn_rfq = '<div><a target="_blank" href="form_po.php?rfq&kod_dogovora=' . $this->kod_dogovora . '&kod_part=' . $row['kod_part'] . '"><img alt="RFQ" title="RFQ" src="img/rfq.png"></a></div>';
                $btn_po = '<div><a target="_blank" href="form_po.php?kod_dogovora=' . $this->kod_dogovora . '&kod_part=' . $row['kod_part'] . '"><img alt="PO" title="PO" src="img/po.png"></a></div>';
            }

            // todo - Придумать глобальные права
            if (isset($_GET['del']))
                $btn_del = "<div>" . Func::ActButton2('', "Удалить", 'DelPart', 'kod_part_del', $this->kod_part) . "</div>";

            $btn_copy_to_doc = $this->formCopyToDoc();
            $form_copy_to_doc = $this->formCopyToDoc(false);

            $btn_panel = /** @lang HTML */
                "<div class='btn'>
                    <div>$btn_edit</div>
                    <div>$prim</div>
                    $btn_del $btn_rfq $btn_po $btn_copy_to_doc
                </div>";

            $res .=
                '<td  width="365"><a href="form_part.php?kod_part=' . $row['kod_part'] . '&kod_dogovora=' . $this->kod_dogovora . '"><img alt="Edit" src="/img/edit.gif" height="14" border="0" /></a>
                                  <a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '"><b>' . $row['shifr'] . "</b> " . $modif . '</a>' . $pn . $btn_panel . $form_copy_to_doc . '</td>
                      <td width="70" align="right">' . $row['numb'] . $ostatok . '</td>
                      <td width="80" align="center" ' . $ind . '>' . $data_postav_str . '</td>
                      <td width="40">' . $nacl . '</td>
                      <td width="120" >' . $price_str . $Val . '</td>
                      <td width="120" >' . Func::Rub($row['price_it']) . $Val . '</td>
                      <td width="120">' . Func::Rub($sum_part) . $sum_part_form . $Val . $NDS . '</td>
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
            "SELECT * FROM raschet WHERE del=0 AND kod_part=$this->kod_part ORDER BY data ASC"); //

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
    public function formAddAVOK()
    {
        $dt = Func::NowE();

        $res = "<form id='form1' name='form1' method='post' action=''>
                              <table width='293' border='0'>
                                    <tr>
                                        <td width='105'>Процент АВ</td>
                                            <td width='172'>
                                            <span id='sprytextfield_AVPr'>
                                                <input name='AVPr' type='text' id='text1' value='100'/>
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

    /**
     * Добавление или редактирование
     * @param $kod_elem - номенклатура
     * @param int $numb - количество
     * @param $data_postav - дата поставки
     * @param float $price - цена без НДС
     * @param string $modif - модификация
     * @param float $nds - НДС
     * @param int $val - валюта
     * @param int $Add
     * @param float $price_or - ориентировочная цена
     * @param string $data_nach - дата начала этапа
     */
    public function AddEdit($kod_elem, $numb, $data_postav, $price = 0., $modif = '', $nds = 0.18, $val = 1, $Add = 1, $price_or = 0., $data_nach = "")
    {
        $data_postav = func::Date_to_MySQL($data_postav);

        if ($data_nach != "")
            $data_nach = func::Date_to_MySQL($data_nach);
        else
            $data_nach = "null";

        $numb = func::clearNum($numb);
        $price = func::clearNum($price, 2);
        $price_it = func::rnd($price * (100 + $nds) / 100);

        if ($price < config::$min_price) { // Если цена не задана
            Elem::getPriceForQuantity((int)$kod_elem, (int)$numb); // Пытаемся получить цену элемента из прайс-листа для указанного количества
            $price_it = func::rnd($price * (100 + $nds) / 100); // Цена с НДС
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
                         <input  name='data_postav' id='data_postav' value='$data_postav' />
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
     */
    public static function Delete($kod_part = 0)
    {
        if ($kod_part == 0)
            return;

        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE parts SET del=1,kod_user=$kod_user WHERE kod_part=$kod_part");

        $db->query(/** @lang MySQL */
            "UPDATE raschet SET del=1,kod_user=$kod_user WHERE kod_part=$kod_part");

        //todo - проверить
        $db->query(/** @lang MySQL */
            "UPDATE
                            raschety_plat
                           INNER JOIN raschet ON raschet.kod_rascheta = raschety_plat.kod_rascheta
                           SET raschety_plat.del=1,raschety_plat.kod_user=$kod_user
                           WHERE raschet.kod_part=$kod_part
                          ");

        $db->query(/** @lang MySQL */
            "UPDATE sklad SET del=1,kod_user=$kod_user WHERE kod_part=$kod_part");
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
     * Количество отгруженное по партии
     * @param $kod_part
     * @return int
     */
    public static function getNumbOtgruz($kod_part)
    {
        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                        view_sklad_otgruzka.kod_part,
                        Sum(view_sklad_otgruzka.numb) AS summ_numb
                    FROM
                        view_sklad_otgruzka
                    WHERE
                        view_sklad_otgruzka.kod_part = $kod_part
                    GROUP BY
                        view_sklad_otgruzka.kod_part;");
        if ($db->cnt == 0)
            return 0;

        $res = $rows[0]['summ_numb'];

        return $res;
    }
    //
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
                self::copyToDoc((int)$_POST['kod_part_copy'], (int)$_POST['kod_dogovora']);
                $event = true;
            } elseif ($_POST['Flag'] == 'EditSumPart' and isset($_POST['kod_part'], $_POST['sum_part'])) {
                self::setSumPart($_POST['kod_part'],$_POST['sum_part']);
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
            "SELECT kod_part FROM parts WHERE del=0 AND kod_dogovora=$kod_dogovora ORDER BY kod_part ASC ");
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
     */
    public static function copyToDoc($kod_part, $kod_dogovora)
    {
        $kod_user = func::kod_user();
        $kod_part = (int)$kod_part;
        $kod_dogovora = (int)$kod_dogovora;
        $data_postav = date("Y-m-d");

        $db = new Db();
        $db->query(/** @lang MySQL */
            "INSERT INTO parts (kod_dogovora,kod_elem,numb,data_postav,price,price_it,sum_part,modif,nds,val,kod_user,price_or,data_nach) 
                              SELECT $kod_dogovora,kod_elem,numb,$data_postav,price,price_it,sum_part,modif,nds,val,$kod_user,price_or,data_nach 
                              FROM parts WHERE kod_part=$kod_part;");
    }
//-----------------------------------------------------------------------
//
    public function formCopyToDoc($btb = true)
    {
        if (func::user_group() !== "admin")
            return "";

        if ($btb)
            return "<div>" . Func::ActButton2('', "Копировать", 'CopyPart', 'kod_part_copy', $this->kod_part) . "</div>";

        $res = "";
        if (isset($_POST['Flag'], $_POST['kod_part_copy']))
            if ($_POST['Flag'] == "CopyPart" and (int)$_POST['kod_part_copy'] == $this->kod_part) {
                $db_doc = new Db();
                $rows_doc = $db_doc->rows(/** @lang MySQL */
                    "SELECT * FROM view_dogovor_data WHERE zakryt=0 ORDER BY nomer ASC;");
                $res = /** @lang HTML */
                    "<div>
                        <form method='post'>
                        " . Doc::formSelList($rows_doc, 0) . "
                        <input type='hidden' name='kod_part_copy' value='$this->kod_part'>
                        <input type='hidden' name='Flag' value='CopyPartToDoc'>
                        <input type='submit' value='Копировать'>
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

        if($numb == 0)
            $numb = 1;

        $nds = (int)$row['nds'];

        Db::getHistoryString("part", "kod_part", $kod_part);

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
}