<?php
include_once('class_func.php');
include_once('class_elem.php');

class Part
{
    public $kod_part=0;
    public $kod_dogovora;
    public $Item; // Связанный объект Изделие партии
    public $Parts;// Партии Договора
    public $Data; // Данные по партии

    //-------------------------------------------------------------------------
    /**
     * Part constructor.
     */
    public function __construct()
    {
        //
    }
    //-------------------------------------------------------------------------
    /**
     * Формируем таблицу партий по договору. На входе rplan
     * @param int $sgp - вывод накладных о поступлении на склад и об отгрузке (1-вывод,0-не выводить)
     * @param string $SQL - запрос. По умолчанию - "" = Empty
     * @param int $AddNacl
     * @return string
     */
    public function formParts($sgp = 0, $SQL = "", $AddNacl = 0)
    {
        $db = new Db();

        // Если запрос не был передан в параметрах
        if ($SQL == "")
            $SQL = "SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora ORDER BY data_postav ASC"; // Сначала старые партии

        $rows = $db->rows($SQL);
        $cnt = $db->cnt;

        if($cnt==0)
            return Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Партию', 'AddPartForm');

        $btn_auto_ras = '';
        $btn_add_part = '';
        $btn_add_100 = '';

        // Если вызов списка партий - выводим кнопки Добавить партию и Авто-Расчет 100%
        // Если вызов из формы Партия - выводим только Авторасчет
        if($this->kod_part!=0)
            $btn_auto_ras = Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora&kod_part=".$this->kod_part, 'Авто-Расчет', 'AddAVOK');
        else
        {
            $btn_add_part = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить Партию', 'AddPartForm');
            $btn_add_100 = Func::ActButton("form_part.php?kod_dogovora=$this->kod_dogovora", 'Авто-Расчет 100%', 'AddRasch100');
        }

        // Шапка
        // Кнопки Добавить Партию / Добавить расчет во все партии / Добавить расчет в партию
        $res = "<table>
                    <tr>
                        <td>
                            $btn_add_part
                        </td>
                        <td>
                            $btn_add_100
                        </td>
                        <td>
                            $btn_auto_ras
                        </td>
                    </tr>
                </table>";

        $res .= '<table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC">
                        <td width="365">Наименование</td>
                        <td width="40">Кол-во</td>
                        <td width="80">Дата Поставки</td>
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
            $modif ='';
            if($row['modif']!='')
                $modif = ' (' . $row['modif'] . ')'; // Модификация

            $numb = (int)$row['numb']; // Количество товара в партии
            $numb_otgruz = $row['numb_otgruz']; // Количество отгруженного товара по партии
            $part_summa = (double)$row['part_summa'];

            $ost = $row['numb_ostat']; // Осталось отгрузить
            $ostatok = ""; // Строка для вывода остатка по отгрузке
            // Если договор входящий
            $numb_poluch = 0;
            if($row['kod_ispolnit']!=683)
            {
                $numb_poluch = $this->getNumbPoluch($row['kod_part']);
                $ost = $numb - $numb_poluch;
            }

            // Вывод накладных о поступлении и Отгрузке с СГП---------------------------------------
            $nacl = ''; // Строка вывода накладных
            if ($sgp == 1)
                $nacl = $this->formSGPAll();

            // Форма добавления накладной
            if ($AddNacl > 0) {
                if ((int)$row['kod_org'] != 683)
                    $nacl .= $this->formAddNacl($ost, 2); // Отгрузка
                else
                    $nacl .= $this->formAddNacl($ost, 1); // Поступление
            }
            elseif($ost>0) // Выводим кнопку Добавить только когда есть отстаток
                $nacl.= Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'],"Добавить", 'AddNaklad',"kod_part",$row['kod_part']);

            // Цена --------------------------------------------------------------------------------------
            $price = (double)$row['price'];

            // Дата поставки------------------------------------------------------------------------------
            $data_postav = Func::Date_from_MySQL($row['data_postav']);

            // Окраска отруженных/полученных партий в зелёный
            $ind = '';// Индикатор окраски даты поставки
            if ($ost == 0)
                $res .= '<tr bgcolor="#ADFAC2">';// Зеленый
            else {
                $res .= '<tr>';
                // Если отстаок не равен количеству партии то выводим
                if ($ost != $numb) {
                    if($row['kod_ispolnit']==683)
                        $ostatok = " (<abbr title=\"Осталось отгрузить $ost\">$ost</abbr>)<br><abbr title='Отгружено $numb_otgruz'><img src=\"/img/out.gif\" height=\"14\" />$numb_otgruz</abbr>";
                    else
                        $ostatok = " (<abbr title=\"Осталось получить $ost\">$ost</abbr>)<br><abbr title='Получено $numb_poluch'><img src=\"/img/in.gif\" height=\"14\" />$numb_poluch</abbr>";
                }
                else
                    $ostatok = "";

                // Дней до отгрузки
                $drem = Func::DaysRem($data_postav);

                // Если осталось меньше 30 и больше 14 дней то красим в оранжевый
                if ($drem <= 14)
                        $ind = "bgcolor='#F18585'";// Красный
                elseif ($drem <= 30)
                    $ind = 'bgcolor="#FFD222"';// Оранжевый
            }

            //--------------------------
            // НДС
            $NDS = '';

            if (round($row['nds'], 2) != 0.18)
                $NDS = '<br>НДС ' . (int)($row['nds'] * 100) . '%';

            //--------------------------
            // Валюта
            $Val = '';

            // Процент оплаты
            $PRC = 0; // Строка вывода процента // todo - $PRC = ???  Доработать. Не вычисляется если партия не в рублях. Нужно вводить данные о курсе и пересчитывать.
            if ((int)$row['val'] != 1) { // todo - Доработать. Пока только USD, добавить EUR?
                $Val = "$";
            }
            elseif ($dogovor_proc_pay > 0){
                $prc = $this->getProcPayByPart($row);

                if($prc!=$dogovor_proc_pay)
                    $PRC = $prc . "($dogovor_proc_pay)";
                else
                    $PRC = $prc;
            }

            //$sn = ' s/n '. $row['kod_part']; // Идентификатор партии

            // Кнопка редактирования партии
            $btn = Func::ActButton("form_part.php?kod_part=".$row['kod_part'] .'&kod_dogovora=' . $this->kod_dogovora, 'Изменить', 'EditPartForm');

            $res .=
                '<td  width="365"><a href="form_part.php?kod_part=' . $row['kod_part'] . '&kod_dogovora=' . $this->kod_dogovora . '"><img src="/img/edit.gif" height="14" border="0" /></a>
                                  <a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '"><b>' . $row['shifr'] . "</b> " . $modif /*.  $sn*/ . '</a>'.$btn.'</td>
                      <td width="70" align="right">' . (int)$row['numb'] . $ostatok . '</td>
                      <td width="80" align="center" ' . $ind . '>' . $data_postav . '</td>
                      <td width="40">' . $nacl . '</td>
                      <td width="120" >' . Func::Rub($price) . $Val . '</td>
                      <td width="120" >' . Func::Rub($price * (1 + (double)$row['nds'])) . $Val .  '</td>
                      <td width="120">' . Func::Rub($part_summa) . $Val . $NDS . '</td>
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
        $rows = $db->rows("SELECT * FROM sklad WHERE del=0 AND kod_part=$this->kod_part");
        $cnt = $db->cnt;

        $res = '';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $date = ' от ' . Func::Date_from_MySQL($row['data']); // Дата документа
            $kod_oborota = $row['kod_oborota']; // Код оборота = код накладной
            $naklad = $row['numb'] . '(№' . $row['naklad'] . $date . ')'; // Номер документа и дата

            if ((int)$row['kod_oper'] == 1) // Поступление
                $res .= '<br><img src="/img/in.gif" height="14" />' . $naklad;
            else if ((int)$row['kod_oper'] == 2) // Отгрузка
            {
                $res .= '<br><img src="/img/out.gif" height="14" />' . $naklad;

                // Форма отметки о получении накладной
                if ((int)$row['poluch'] <> 1)
                    $res.= Func::ActButton2('', "Получено", 'PoluchNaklad', 'kod_oborota_poluch',$kod_oborota);
            } else if ($row['kod_oper'] == 3) // Акт
                $res .= '<br>По Акту:' . $naklad;
            else
                if ($row['kod_oper'] == 4) // Возврат
                    $res .= '<br>Возврат:' . $naklad;
            $res .= Func::ActButton2('', "Удалить", 'DelNaklad', 'kod_oborota_del',$kod_oborota);
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
        $res = $this->formParts(1, "SELECT * FROM view_rplan WHERE kod_part=$this->kod_part", $AddNacl);
        if(isset($_GET['del']))
            $res .= Func::ActButton2('', "Удалить", 'DelPart', 'kod_part_del',$this->kod_part);

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
        $rows = $db->rows("SELECT * FROM raschet WHERE del=0 AND kod_part=$this->kod_part ORDER BY data ASC"); //

        $cnt = $db->cnt;

        if($cnt==0)
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
            $summa_pays = $this->SummPlatByRasch($kod_rascheta);
            $data = Func::Date_from_MySQL($row['data']);
            $summa = Func::Rub($row['summa']);
            $type_rascheta = $row['type_rascheta'];

            if($type_rascheta=="1")
                $type_rascheta = "АВ";
            else
                $type_rascheta = "OK";

            $ostatok_plat = $raschet_summa - $summa_pays;
            $ostatok_plat = Func::Rub($ostatok_plat);

            $btn_del = Func::ActButton2('','Удалить', 'DelRasch',"kod_rascheta_del",$kod_rascheta);

            // Форма для ввода ПП в расчет
            $Body =    "<input type='hidden' name='kod_rascheta' value='$kod_rascheta' />
                        <input type='text' name='summa' value='$ostatok_plat' />";

            $res.= '<tr>
                    <td width="80">' . $data . '</td>
                    <td width="100">' . $summa . '</td>
                    <td width="20" align="center">' . $type_rascheta ."<br>". $btn_del. '</td>
                    <td>' . $this->formPPRascheta($kod_rascheta, $Edit, $Body)."</td>
                    </tr>";
        }

        $res.= '</table>';
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
        $rows = $db->rows("SELECT * FROM view_raschety_plat WHERE kod_rascheta=$kod_rascheta");
        $cnt = $db->cnt;

        $res = "";

        if($cnt==0)
        {
            $d = new Doc();
            $d->kod_dogovora = $this->kod_dogovora;
            $res .= "<br>" . $d->formPaySelList('', $Body);
            return $res;
        }

        $res = '<table border=0 cellspacing=0 cellpadding=0 width="400">
                    <tr bgcolor="#CCCCCC">
                        <td width="80">Дата</td>
                        <td>Сумма</td>
                        <td width="80">№</td>
                        <td width="150">Примечание</td>
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
                        <td>' . $data_plat . '</td>
                        <td>' . $summa_plat_str . '</td>
                        <td>№' . $nomer . '</td>
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
     * Добавить расчет
     * @param $summa
     * @param $data
     * @param $type_rascheta
     */
    public function AddRasch($summa, $data, $type_rascheta)
    {
        $db = new Db();
        $data = func::Date_to_MySQL($data);
        $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$summa,'$data',$type_rascheta)");
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

        $db->query("UPDATE raschety_plat SET del=1 WHERE kod_rascheta=$kod_rascheta");

        $db->query("UPDATE raschet SET del=1 WHERE kod_rascheta=$kod_rascheta");
    }
//------------------------------------------------------------------------
//
    /**
     * Добавить расчет 100% во все партии договора
     */
    public function AddRasch100()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora");
        $cnt = $db->cnt;

        for($i=0;$i<$cnt;$i++) {
            $row = $rows[$i];
            $kod_part = $row['kod_part'];
            $part_summa = $row['part_summa'];
            $data = Func::Date_to_MySQL($row['data_postav']); // Дата поставки
            $type = 2; //ОК- расчет

            $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($kod_part,$part_summa,'$data',$type)");
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
        $summa = str_replace(' ', '', $summa);
        $summa = doubleval($summa);

        $db = new Db();

        $rows = $db->rows("SELECT * FROM view_plat WHERE kod_plat =$kod_plat");
        if($db->cnt==1)
        {
            $row = $rows[0];
            $ostat = ((double)$row['summa']-(double)$row['summa_raspred']);
            if($summa > $ostat)
            {
                $summa = $ostat;
            }
        }

        $db->query("INSERT INTO raschety_plat (summa,kod_rascheta,kod_plat) VALUES($summa,$kod_rascheta,$kod_plat)");
    }
//--------------------------------------------------------------
//
    /**
     * Формирование Расчетов по стандартной схеме АВанс-ОКончательный расчет
     * @param float $AVPr - процент аванса
     * @param $AVDate - дата аванса
     */
    public function setPayGraph($AVPr = 0.6, $AVDate)
    {

        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_rplan WHERE kod_part=$this->kod_part");

        $part_summa = (double)$rows[0]['part_summa'];

        $raschet_summa = round($part_summa * $AVPr, 2); // Сумма расчета

        $AVDate = func::Date_to_MySQL($AVDate);

        $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$raschet_summa,'$AVDate',1)");

        $ostatok = $part_summa - $raschet_summa;

        if ($ostatok > 0) {
            $data_postav = $rows[0]['data_postav'];
            $OKDate = Func::Date_to_MySQL($data_postav); // Дата окончательного расчета = дата поставки
            $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$ostatok,'$OKDate',2)");
        }
    }
//--------------------------------------------------------------
//
    /**
     * Добавить Накладную
     * @param $numb
     * @param $naklad
     * @param $data
     * @param $kod_oper
     * @param $operator
     */
    public function AddNaklad($numb, $naklad, $data, $kod_oper, $operator)
    {
        $db = new Db();
        $kod_part = $this->kod_part;
        $data = func::Date_to_MySQL($data);

        $db->query("INSERT INTO sklad (kod_part,numb,naklad,data,kod_oper,oper) VALUES($kod_part,$numb,'$naklad','$data',$kod_oper,'$operator')");

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

        if (isset($kod_oborota)) {
            $db->query("UPDATE sklad SET del=1 WHERE kod_oborota=$kod_oborota");

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
                                <td><input type="text" name="naklad" id="naklad" /></td>
                              </tr>
                              <tr>
                                <td>Дата</td>
                                <td><input type="text" name="data" id="data" value="' . Func::NowE() . '" /></td>
                              </tr>
                              <tr>
                                <td>Кол-во </td>
                                <td><input type="text" name="numb" id="numb" value="' . $numb . '" /></td>
                              </tr>
                              <tr>
                                <td></td>
                                <td>
                                ' . $kod_oper . '
                                </td>
                              </tr>
                            </table>
                <input type="hidden" name="AddEditNacl" value="1" />
                <input type="hidden" name="kod_part" value='. $this->kod_part .' />
                <input type="submit" name="button" id="button" value="Сохранить" />
                </form>
                ';
        $res .= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', 'Cansel');
        return $res;
    }

//-----------------------------------------------------------------------

    /**
     * Добавление или редактирование
     * @param $kod_elem
     * @param int $numb
     * @param $data_postav
     * @param int $price
     * @param string $modif
     * @param int $nds
     * @param int $val
     * @param int $Add
     */
    public function AddEdit($kod_elem, $numb = 1, $data_postav, $price = 0, $modif = '', $nds = 18, $val = 1,$Add=1)
    {
        $db = new Db();
        $data_postav = func::Date_to_MySQL($data_postav);
        if($Add==1)
            $db->query("INSERT INTO parts (kod_dogovora,kod_elem,numb,data_postav,price,modif,nds,val) VALUES($this->kod_dogovora,$kod_elem,$numb,'$data_postav',$price,'$modif',$nds,$val)");
        else
            $db->query("UPDATE parts SET kod_elem=$kod_elem, numb=$numb, data_postav='$data_postav',price=$price,modif='$modif',nds=$nds,val=$val,edit=1 WHERE kod_part=$this->kod_part");
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма - Добавление или Редактирование партии
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit=1)
    {

        //Данные
        $modif = "";
        $data_postav = func::NowE();
        $numb = 1;
        $price = "";
        $nds_18 = "checked";
        $nds_0 = "";
        $rub_checked = "checked";
        $usd_checked = "";
        $form_name = "AddPart";

        $E = new Elem();

        if($Edit==1) {

            $db = new Db();
            $rows = $db->rows("SELECT * FROM parts WHERE del=0 AND kod_part=$this->kod_part");

            $row = $rows[0];//Данные
            $form_name = "EditPart";
            $kod_elem = (int)$row['kod_elem'];
            $modif = $row['modif'];
            $data_postav = Func::Date_from_MySQL($row['data_postav']);
            $numb = $row['numb'];
            $price = $row['price'];
            $val = (int)$row['val'];
            $nds = (double)$row['nds'];

            $nds_0 = "";
            if ($nds == 0) {
                $nds_0 = "checked";
                $nds_18 = "";
            }

            $rub_checked = "";
            $usd_checked = "";

            if ($val == 1)
                $rub_checked = "checked";
            elseif ($val == 2)
                $usd_checked = "checked";

            $E->kod_elem = $kod_elem;
        }

        $res =
            '<form id="form1" name="form1" method="post" action="">
                <table border="0">
                  <tr>
                    <td>Элемент</td>
                    <td>' . $E->formSelList() . '</td>
                  </tr>
                  <tr>
                    <td>Модификация</td>
                    <td><input type="text" name="modif" id="modif" value="' . $modif . '" /></td>
                  </tr>
                  <tr>
                    <td>Дата Поставки </td>
                    <td><span id="SDateR">
                              <input type="text" name="data_postav" id="data_postav" value="' . $data_postav . '" />
                              <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                  </tr>
                  <tr>
                    <td>Количество</td>
                        <td><input type="text" name="numb" id="numb" value="' . $numb. '" /></td>
                  </tr>
                  <tr>
                    <td>Цена без НДС</td>
                    <td><input type="text" name="price" id="price" value="' . $price . '" /></td>
                  </tr>
                  <tr>
                       <td>НДС</td>
                       <td>
                            <input type="radio" name="nds" value="0.18" ' .$nds_18. '> 18%<br>
                            <input type="radio" name="nds" value="0" '. $nds_0 .'> 0%<br>
                       </td>
                  </tr>
                  <tr>
                   <td>Валюта</td>
                   <td><input type="radio" name="val" value="1" '.$rub_checked.'> RUR<br>
                   <input type="radio" name="val" value="2" '. $usd_checked .'> USD<br>
                   <input type="radio" name="val" value="3"> EURO<br>
                   </td>
                  </tr>
                </table>

            <input id="EditPartForm" type="hidden" value="1" name="'.$form_name.'"/>
            <input type="submit" value="Сохранить" />
            <br>
            </form>';

        $res.= Func::Cansel(0);
        return $res;
    }
//-------------------------------------------------------------------------
    //
    /**
     * Отметка о Получении Накладной
     * @param int $kod_oborota
     */
    public function PostNacl($kod_oborota)
    {
        $db = new Db();
        $Date = date("Y-m-d");
        $db->query("UPDATE sklad SET poluch=1, data_poluch='$Date' WHERE kod_oborota=$kod_oborota");
    }
//-------------------------------------------------------------------------
//
    /**
     * Удаление партии и связей
     * @param int $kod_part
     */
    public static function Delete($kod_part=0)
    {
        if($kod_part==0)
            return;

        $db = new Db();
        $db->query("UPDATE parts SET del=1 WHERE kod_part=$kod_part");

        $db->query("UPDATE raschet SET del=1 WHERE kod_part=$kod_part");

        //todo - проверить
        $db->query("UPDATE
                            raschety_plat
                           INNER JOIN raschet ON raschet.kod_rascheta = raschety_plat.kod_rascheta
                           SET raschety_plat.del=1
                           WHERE raschet.kod_part=$kod_part
                          ");

        $db->query("UPDATE sklad SET del=1 WHERE kod_part=$kod_part");
    }

//-------------------------------------------------------------------------
//
    /**
     * Сумма платежей по расчету
     * @param $kod_rascheta
     * @return float
     */
    public static function SummPlatByRasch($kod_rascheta)
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM raschety_plat WHERE del=0 AND kod_rascheta=$kod_rascheta");

        $cnt = $db->cnt;
        if($cnt==0)
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
        $rows = $db->rows("SELECT 
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
        if($cnt==0)
            return 0.;

        $row = $rows[0];
        $res = (double)$row['summa_plat'];

        return $res;
    }
//
//-------------------------------------------------------------------------
    /**
     * Процент распределенных платежей от суммы партии
     * @param $rplan_row - поле 'part_summa'
     * @return int
     */
    public function getProcPayByPart($rplan_row)
    {
        $res = 0;
        $part_summa = (double)$rplan_row['part_summa'];
        $summ_plat = $this->getSummPlatByPart();

        if($summ_plat >0 and $part_summa >0)
            $res = (int)($summ_plat / $part_summa * 100);

        return $res;
    }
//
//-------------------------------------------------------------------------
    /**
     * Количество отгруженное по партии
     * @param $kod_part
     * @return int
     */
    public static function getNumbOtgruz($kod_part)
    {
        $db = new Db();

        $rows = $db->rows("SELECT
                                      view_sklad_otgruzka.kod_part,
                                      Sum(view_sklad_otgruzka.numb) AS summ_numb
                                    FROM
                                      view_sklad_otgruzka
                                    WHERE
                                      view_sklad_otgruzka.kod_part = $kod_part
                                    GROUP BY
                                      view_sklad_otgruzka.kod_part"
                            );
        if($db->cnt==0)
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

        $rows = $db->rows("SELECT
                                      view_sklad_postuplenie.kod_part,
                                      Sum(view_sklad_postuplenie.numb) AS summ_numb
                                    FROM
                                      view_sklad_postuplenie
                                    WHERE
                                      view_sklad_postuplenie.kod_part = $kod_part
                                    GROUP BY
                                      view_sklad_postuplenie.kod_part"
        );

        if($db->cnt==0)
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

        if (isset($_POST['AddPart']))
            if(isset($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price']))
            {
                $this->AddEdit($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'], $_POST['modif'], $_POST['nds'], $_POST['val']);
                $event = true;
            }

        if (isset($_POST['EditPart']))
            if(isset($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price']))
            {
                $this->AddEdit($_POST['kod_elem'], $_POST['numb'], $_POST['data_postav'], $_POST['price'], $_POST['modif'], $_POST['nds'], $_POST['val'],0);
                $event = true;
            }

        if (isset($_POST['kod_oborota_poluch'])) { // Получение накладной
            $this->PostNacl($_POST['kod_oborota_poluch']);
            $event = true;
        }
        elseif (isset($_POST['kod_oborota_del'])) { // Удаление накладной
            $this->DelNaklad($_POST['kod_oborota_del']);
            $event = true;
        }

        if (isset($_POST['AddEditNacl']))
            if (isset($_POST['numb'], $_POST['naklad'], $_POST['data'],$_POST['kod_oper'])) {
                $this->AddNaklad($_POST['numb'], $_POST['naklad'], $_POST['data'], $_POST['kod_oper'], $_SESSION['MM_Username']);
                $event = true;
        }

        if (isset($_POST['summa']) and isset($_POST['data']) and isset($_POST['type_rascheta'])) {
            $this->AddRasch($_POST['summa'], $_POST['data'], $_POST['type_rascheta']);
            $event = true;
        }

        if (isset($_POST['Flag']))
        {
            if ($_POST['Flag'] == 'AddRasch100')
            {
                $this->AddRasch100();
                $event = true;
            }
            elseif ($_POST['Flag'] == 'DelRasch' and isset($_POST['kod_rascheta_del']))
            {
                $this->DelRasch($_POST['kod_rascheta_del']);
                $event = true;
            }
            elseif ($_POST['Flag'] == 'DelPart' and isset($_POST['kod_part_del']))
            {
                $this->Delete($_POST['kod_part_del']);
                $event = true;
            }
        }

        if (isset($_POST['summa']) and isset($_POST['kod_rascheta']) and isset($_POST['kod_plat'])) {
            $this->AddPayToRas($_POST['summa'], $_POST['kod_rascheta'], $_POST['kod_plat']);
            $event = true;
        }

        if (isset($_POST['AVPr'],$_POST['data']))
        {
            $pr = (double)$_POST['AVPr'];
            $pr = round($pr / 100, 2);

            if ($pr > 0 and $pr <= 1) {
                $this->SetPayGraph($pr, $_POST['data']);
                $event = true;
            }
        }

        if($event)
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

        $rows = $db->rows(/** @lang SQL */
            "SELECT kod_part FROM parts WHERE del=0 AND kod_dogovora=$kod_dogovora ORDER BY kod_part ASC ");
        return $rows[0]['kod_part'];
    }
}