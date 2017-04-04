<?php
include_once('class_func.php');
include_once('class_elem.php');

class Part
{
    public $kod_part;
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
     * @param int $t -тип вывода(1-таблица с шапкой, 2-только шапка, 3-толко таблица)
     * @param int $sgp - вывод накладных о поступлении на склад и об отгрузке (1-вывод,0-не выводить)
     * @param string $SQL - запрос. По умолчанию - "" = Empty
     * @param int $AddNacl
     * @return string
     */
    public function getParts($t = 3, $sgp = 0, $SQL = "", $AddNacl = 0)
    {
        // Шапка
        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">';

        if ($t != 3) {
            $res .= '<tr bgcolor="#CCCCCC">
                        <td width="365">Наименование</td>
                        <td width="40">Кол-во</td>
                        <td width="80">Дата Поставки</td>
                        <td width="80">Склад</td>
                        <td width="120">Цена без НДС</td>
                        <td width="120">Цена c НДС</td>
                        <td width="120">Сумма</td>
                        <td width="90">Оплата</td>
                    </tr>';

            // Вывод только заголовка
            if ($t == 2) {
                $res .= "</table>";
                return $res;
            }
        }

        $db = new Db();

        // Если запрос не был передан в параметрах
        if ($SQL == "")
            $SQL = "SELECT * FROM view_rplan WHERE kod_dogovora=$this->kod_dogovora";

        $rows = $db->rows($SQL);
        $cnt = $db->cnt;

        $dogovor_proc_pay = Doc::getProcPay($this->kod_dogovora); // Процент платежей по договору. Строка вида "70%"

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $this->kod_part = $row['kod_part'];
            $modif ='';
            if($row['modif']!='')
                $modif = ' (' . $row['modif'] . ')'; // Модификация

            $numb = (int)$row['numb']; // Количество товара в партии
            $numb_otgruz = self::getNumbOtgruz($row['kod_part']); // Количество отгруженного товара по партии
            $part_summa = (double)$row['part_summa'];
            $ost = $numb - $numb_otgruz; // Осталось отгрузить
            $ostatok = ""; // Строка для вывода остатка по отгрузке

            // Вывод накладных о поступлении и Отгрузке с СГП---------------------------------------
            $nacl = ''; // Строка вывода накладных
            if ($sgp == 1)
                $nacl = $this->SGPAll();

            // Форма добавления накладной
            if ($AddNacl > 0) {
                if ((int)$row['kod_org'] != 683)
                    $nacl .= $this->Form_AddNaclSM($ost, 2); // Отгрузка
                else
                    $nacl .= $this->Form_AddNaclSM($ost, 1); // Поступление
            }
            else
                $nacl.= Func::ActForm($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'],
                                        "<input type='hidden' name='kod_part' id='kod_part' value='$this->kod_part'  />", 'Добавить', 'AddNacl');

            // Цена --------------------------------------------------------------------------------------
            $price = (double)$row['price'];

            // Дата поставки------------------------------------------------------------------------------
            $data_postav = Func::DateE($row['data_postav']);

            // Окраска отруженных партий в зелёный
            $ind = '';// Индикатор окраски даты поставки
            if ($ost == 0)
                $res .= '<tr bgcolor="#ADFAC2">';// Зеленый
            else {

                $res .= '<tr>';
                // Если отстаок не равен количеству партии то выводим
                if ($ost != (int)$row['numb'] and $ost > 0)
                    $ostatok = '(' . $ost . ')<br><img src="/img/out.gif" height="14" />' . $numb_otgruz;
                else
                    $ostatok = "";

                // Дней до отгрузки
                $drem = Func::DaysRem($data_postav);

                // Если осталось меньше 30 и больше 14 дней то красим в оранжевый
                if ($drem <= 30 and $drem > 14)
                    $ind = 'bgcolor="#FFD222"';// Оранжевый
                else // если меньше 14 то в красный
                    if ($drem <= 14)
                        $ind = 'bgcolor="#F18585"';// Красный
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
            $PRC = 0; // Строка вывода процента

            if ((int)$row['val'] != 1) { // Доработать. Пока только USD, добавить EUR
                $Val = "$";
                //$PRC = ???  Доработать. Не вычисляется если партия не в рублях. Нужно вводить данные о курсе и пересчитывать.
            }
            elseif ($dogovor_proc_pay > 0){
                $prc = $this->getProcPayByPart($row);

                if($prc!=$dogovor_proc_pay)
                    $PRC = $prc . "($dogovor_proc_pay)";
                else
                    $PRC = $prc;
            }

            $res .=
                '<td  width="365"><a href="form_part.php?kod_part=' . $row['kod_part'] . '&kod_dogovora=' . $row['kod_dogovora'] . '"><img src="/img/edit.gif" height="14" border="0" /></a>
                                  <a href="form_elem.php?kod_elem=' . $row['kod_elem'] . '"><b>' . $row['obozn'] . "</b> " /*. $row['name']*/ . $modif . '</a></td>
                      <td width="40">' . (int)$row['numb'] . $ostatok . '</td>
                      <td width="80" ' . $ind . '>' . $data_postav . '</td>
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
     * Все накладные по дате
     * @return string
     */
    public function SGPAll()
    {

        $db = new Db();
        $rows = $db->rows("SELECT * FROM sklad WHERE kod_part=$this->kod_part");
        $cnt = $db->cnt;

        $res = '';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $date = ' от ' . Func::DateE($row['data']); // Дата документа

            $naklad = $row['numb'] . '(№' . $row['naklad'] . $date . ')'; // Номер документа и дата

            if ((int)$row['kod_oper'] == 1) // Поступление
                $res .= '<br><img src="/img/in.gif" height="14" />' . $naklad;
            else if ((int)$row['kod_oper'] == 2) // Отгрузка
            {
                $res .= '<br><img src="/img/out.gif" height="14" />' . $naklad;

                // Форма отметки о получении накладной
                $Nacl = $row['kod_oborota']; // Код оборота - ID накладной

                if ((int)$row['poluch'] <> 1)
                    $res .= '<form id="form1" name="form1" method="post" action="">
                    <input type="hidden" name="PostNacl" value=' . $Nacl . ' />
                    <input type="submit" name="button" id="button" value="Получено" />
                    </form>';

            } else if ($row['kod_oper'] == 3) // Акт
                $res .= '<br>По Акту:' . $naklad;
            else
                if ($row['kod_oper'] == 4) // Возврат
                    $res .= '<br>Возврат:' . $naklad;
        }

        return $res;
    }
    //-------------------------------------------------------------------------
    /**
     * Вывод одной партии по коду PartID с формой добавления накладной, если $AddNacl=1
     * @param int $AddNacl
     */
    public function ShowPart($AddNacl = 0)
    {
        // Шапка
        $res = '<br>Информация по Партии<br><table border=1 cellspacing=0 cellpadding=0 width="100%">';

        $res .= $this->getParts(1, 1, "SELECT * FROM view_rplan WHERE kod_part=$this->kod_part", $AddNacl);

        echo $res;
    }
//--------------------------------------------------------------
    // График Расчетов
    public function PayGraph($Edit = false)
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM raschet WHERE kod_part=$this->kod_part"); //

        $cnt = $db->cnt;

        if($cnt==0)
            return;

        echo '<br>График Расчетов<br>
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
            $data = Func::DateE($row['data']);
            $summa = Func::Rub($row['summa']);
            $type_rascheta = $row['type_rascheta'];

            if($type_rascheta=="1")
                $type_rascheta = "АВ";
            else
                $type_rascheta = "OK";

            $ostatok_plat = $raschet_summa - $summa_pays;
            $ostatok_plat = Func::Rub($ostatok_plat);

            // Форма для ввода ПП в расчет
            $Body =    "<input type='hidden' name='RsID' value='$kod_rascheta' />
                        <input type='text' name='RsSumm' value='$ostatok_plat' />";

            echo '<tr>
                    <td width="80">' . $data . '</td>
                    <td width="100">' . $summa . '</td>
                    <td width="20">' . $type_rascheta . '</td>
                    <td>' . $this->PPRascheta($kod_rascheta, $Edit, $Body);
                    echo Func::ActForm('', "<input type='hidden' name='RsID' value='$kod_rascheta' />", 'Удалить Расчет', 'DelRasch') .
                    "</td>
                    </tr>";
        }

        echo '</table>';
    }
//--------------------------------------------------------------
// Платежи по Расчету
    public function PPRascheta($RasID, $Edit = false, $Body = '')
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_raschety_plat WHERE kod_rascheta=$RasID");
        $cnt = $db->cnt;

        $res = "";

        if($cnt==0)
        {
            $d = new Doc();
            $d->kod_dogovora = $this->kod_dogovora;
            $res .= "<br>" . $d->PaySelList('', $Body);
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
            $data_plat = Func::DateE($row['data_plat']);
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
                $res .= "<br>" . $d->PaySelList('', $Body);
            }
        }
        return $res;
    }
//--------------------------------------------------------------
// Добавить расчет
    /**
     * @param $Summ
     * @param $Date
     * @param $Type
     */
    public function AddRasch($Summ, $Date, $Type)
    {
        $db = new Db();
        $PartID = $this->kod_part;

        $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($PartID,$Summ,'$Date',$Type)");
    }
//------------------------------------------------------------------------
// Удалить расчет
    /**
     * @param $ID
     */
    public function DelRasch($ID)
    {
        $db = new Db();

        $db->query("DELETE FROM raschety_plat WHERE kod_rascheta=" . $ID);
        //echo "DELETE FROM raschety_plat WHERE kod_rascheta=" . $ID;

        $db->query("DELETE FROM raschet WHERE kod_rascheta=" . $ID);
        //echo "DELETE FROM raschet WHERE kod_rascheta=" . $ID;
    }
//------------------------------------------------------------------------
// Добавить расчет 100% во все партии договора
    public function AddRasch100()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM view_dogovory_parts WHERE kod_dogovora=$this->kod_dogovora");
        $cnt = $db->cnt;

        for($i=0;$i<$cnt;$i++) {
            $row = $rows[$i];
            $PartID = $row['kod_part'];
            $Summ = $row['part_summa'];
            $Date = Func::NowE(); // Текущий момент времени
            $Type = 2; //ОК- расчет

            $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($PartID,$Summ,'$Date',$Type)");
        }

        return;
    }
//-----------------------------------------------------------------------
// Добавляет Платеж в Расчет
    /**
     * @param $Summ
     * @param $RsID
     * @param $PayID
     */
    public function AddPayToRas($Summ, $RsID, $PayID)
    {
        $db = new Db();
        $Summ = str_replace(' ', '', $Summ);
        $Summ = doubleval($Summ);

        $db->query("INSERT INTO raschety_plat (summa,kod_rascheta,kod_plat) VALUES($Summ,$RsID,$PayID)");

        return;
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

        $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$raschet_summa,'$AVDate',1)");

        $ostatok = $part_summa - $raschet_summa;

        if ($ostatok > 0) {
            $OKDate = Func::DateE($this->Data['data_postav']); // Дата окончательного расчета = дата поставки
            $db->query("INSERT INTO raschet (kod_part,summa,data,type_rascheta) VALUES($this->kod_part,$ostatok,'$OKDate',2)");
        }
    }
//--------------------------------------------------------------
// Добавить Накладную
    /**
     * @param $Numb
     * @param $Nacl
     * @param $Date
     * @param $Oper
     * @param $Operator
     */
    public function AddNacl($Numb, $Nacl, $Date, $Oper, $Operator)
    {
        $db = new Db();
        $PartID = $this->kod_part;

        $db->query("INSERT INTO sklad (kod_part,numb,naklad,data,kod_oper,oper) VALUES($PartID,$Numb,'$Nacl','$Date',$Oper,'$Operator')");

        return;
    }
//-----------------------------------------------------------------------
    // Данные: NaclR, DateR, NumbR
    // Форма добавления накладной - поставка=отгрузка/ заказ=поступление
    public function Form_AddNaclSM($Numb = 1, $Act = 1)
    {

        if ($Act == 2)
            $oper = 'Отгрузка<input id="Oper" type="hidden" value="2" name="Oper"/>';
        else
            $oper = 'Поступление<input id="Oper" type="hidden" value="1" name="Oper"/>';

        $res = '
                <form id="form1" name="form1" method="post" action="">
                <table width="200" border="0">
                              <tr>
                                <td>Номер </td>
                                <td><input type="text" name="Nacl" id="Nacl" /></td>
                              </tr>
                              <tr>
                                <td>Дата</td>
                                <td><input type="text" name="DateR" id="DateR" value="' . Func::NowE() . '" /></td>
                              </tr>
                              <tr>
                                <td>Кол-во </td>
                                <td><input type="text" name="Numb" id="Numb" value="' . $Numb . '" /></td>
                              </tr>
                              <tr>
                                <td>Операция</td>
                                <td>
                                ' . $oper . '
                                </td>
                              </tr>
                            </table>
                <input type="hidden" name="AddNacl" value="1" />
                <input type="hidden" name="kod_part" value='. $this->kod_part .' />
                <input type="submit" name="button" id="button" value="Submit" />
                </form>
                ';
        $res .= Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', 'Cansel');
        return $res;
    }

//-----------------------------------------------------------------------
    /**
     * @param $ElemID
     * @param int $Numb
     * @param $PDate
     * @param int $PriceTF
     * @param string $Mod
     * @param int $NDS
     * @param int $VAL
     */
    public function Add($ElemID, $Numb = 1, $PDate, $PriceTF = 0, $Mod = '', $NDS = 18, $VAL = 1)
    {
        $db = new Db();

        $db->query("INSERT INTO parts (kod_dogovora,kod_elem,numb,data_postav,price,modif,nds,val) VALUES($this->kod_dogovora,$ElemID,$Numb,'$PDate',$PriceTF,'$Mod',$NDS,$VAL)");

    }
//-----------------------------------------------------------------------
    //  Редактирование партии
    /**
     * @param $ElemID
     * @param int $Numb
     * @param $PDate
     * @param int $PriceTF
     * @param string $Mod
     * @param int $NDS
     * @param int $VAL
     */
    public function Edit($ElemID, $Numb = 1, $PDate, $PriceTF = 0, $Mod = '', $NDS = 18, $VAL = 1)
    {
        $db = new Db();
        $PartID = $this->kod_part;

        $PDate = func::DateR($PDate);

        $db->query("UPDATE parts SET kod_elem=$ElemID, numb=$Numb, data_postav='$PDate',price=$PriceTF,modif='$Mod',nds=$NDS,val=$VAL WHERE kod_part=$PartID");

        //echo "UPDATE parts SET kod_elem=$ElemID, numb=$Numb, data_postav='$PDate',price=$PriceTF,mod='$Mod',nds=$NDS,val=$VAL WHERE kod_part=$PartID";

        return;
    }
//-----------------------------------------------------------------------
//
    /**
     * Форма - Редактирование партии
     */
    public function EditForm()
    {

        $db = new Db();
        $rows = $db->rows("SELECT * FROM parts WHERE kod_part=$this->kod_part");

        $row = $rows[0];

        //Данные
        $kod_elem = (int)$row['kod_elem'];
        $modif = $row['modif'];
        $data_postav = Func::DateE($row['data_postav']);
        $numb = $row['numb'];
        $price = $row['price'];
        $val = (int)$row['val'];
        $nds = (double)$row['nds'];

        $nds_18 = "";
        $nds_0 = "";
        if($nds>0)
            $nds_18 = "checked";
        else
            $nds_0 =  "checked";

        $rub_checked = "";
        $usd_checked = "";
        if($val==1)
            $rub_checked = "checked";
        elseif($val==2)
            $usd_checked = "checked";

        // Чтобы подцепить Sellist по номенклатуре
        $E = new Elem();
        $E->kod_elem = $kod_elem;

        echo
            '<form id="form1" name="form1" method="post" action="">
                <table border="0">
                  <tr>
                    <td>Элемент</td>
                    <td>' . $E->SelList() . '</td>
                  </tr>
                  <tr>
                    <td>Модификация</td>
                    <td><input type="text" name="Mod" id="Mod" value="' . $modif . '" /></td>
                  </tr>
                  <tr>
                    <td>Дата Поставки </td>
                    <td><span id="SDateR">
                              <input type="text" name="SDateR" id="SDate" value="' . $data_postav . '" />
                              <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                  </tr>
                  <tr>
                    <td>Количество</td>
                        <td><input type="text" name="Numb" id="Numb" value="' . $numb. '" /></td>
                  </tr>
                  <tr>
                    <td>Цена без НДС</td>
                    <td><input type="text" name="PriceTF" id="PriceTF" value="' . $price . '" /></td>
                  </tr>
                  <tr>
                       <td>НДС</td>
                       <td>
                            <input type="radio" name="NDS" value="0.18" ' .$nds_18. '> 18%<br>
                            <input type="radio" name="NDS" value="0" '. $nds_0 .'> 0%<br>
                       </td>
                  </tr>
                  <tr>
                   <td>Валюта</td>
                   <td><input type="radio" name="VAL" value="1" '.$rub_checked.'> RUR<br>
                   <input type="radio" name="VAL" value="2" '. $usd_checked .'> USD<br>
                   <input type="radio" name="VAL" value="3"> EURO<br>
                   </td>
                  </tr>
                </table>

            <input id="EditPartForm" type="hidden" value="1" name="EditPart"/>
            <input type="submit" value="Сохранить" />
            <br>
            </form>';

        echo Func::Cansel(1);
    }
//-----------------------------------------------------------------------
// Форма - Добавление партии
    /**
     *
     */
    public function AddForm()
    {
        $E = new Elem();

        echo
            '<form id="form1" name="form1" method="post" action="">
                <table border="0">
                  <tr>
                    <td>Элемент</td>
                    <td>' . $E->SelList() . '</td>
                  </tr>
                  <tr>
                    <td>Модификация</td>
                    <td><input type="text" name="Mod" id="Mod" value="" /></td>
                  </tr>
                  <tr>
                    <td>Дата Поставки </td>
                    <td><span id="SDateR">
                              <input type="text" name="SDateR" id="SDate" value="' . date('d.m.Y') . '" />
                              <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Неправильный формат даты. Пример - 01.01.2001</span></span></td>
                  </tr>
                  <tr>
                    <td>Количество</td>
                        <td><input type="text" name="Numb" id="Numb" value="1" /></td>
                  </tr>
                  <tr>
                    <td>Цена без НДС</td>
                        <td><input type="text" name="PriceTF" id="PriceTF" value="0" /></td>
                  </tr>
                  <tr>
                   <td>НДС</td>
                   <td><input type="radio" name="NDS" value="0.18" checked> 18%<br><input type="radio" name="NDS" value="0"> 0%<br></td>
                  </tr>
                  <tr>
                   <td>Валюта</td>
                   <td><input type="radio" name="VAL" value="1" checked> RUR<br>
                   <input type="radio" name="VAL" value="2"> USD<br>
                   <input type="radio" name="VAL" value="3"> EURO<br>
                   </td>
                  </tr>  
                </table>
                
                <input id="AddPartForm" type="hidden" value="1" name="AddPart"/> 
                <input type="submit" value="Сохранить" />
                <br>
             </form>';

        echo Func::Cansel(1);
    }
//-------------------------------------------------------------------------
    // Отметка о Получении Накладной
    /**
     * @param $Nacl
     */
    public function PostNacl($Nacl)
    {
        $db = new Db();
        $Date = Func::NowE();
        $db->query("UPDATE sklad SET poluch=1, data_poluch='$Date' WHERE kod_oborota=$Nacl");
    }

//-------------------------------------------------------------------------
    /**
     *
     */
    public function setParts()
    {
        $db = new Db();
        $this->Parts = array();
        // Партии договора
        $this->Parts = $db->rows("SELECT * FROM parts WHERE kod_dogovora=" . $this->kod_dogovora);

    }
//-------------------------------------------------------------------------
// Удаление партии
    public function Delete()
    {
        $db = new Db();
        $db->query("DELETE FROM parts WHERE kod_part=" . $this->kod_part);

        $db->query("DELETE FROM raschet WHERE kod_part=" . $this->kod_part);

        $db->query("DELETE FROM sklad WHERE kod_part=" . $this->kod_part);

    }

//-------------------------------------------------------------------------
// Сумма платежей по расчету
    /**
     * @param $kod_rasch
     * @return float
     */
    public static function SummPlatByRasch($kod_rasch)
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM raschety_plat WHERE kod_rascheta=$kod_rasch");

        $cnt = $db->cnt;
        if($cnt==0)
            return 0.;

        $res = 0.;

        for ($i = 1; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Данные
            $res += (double)$row['summa'];
        }

        return $res;

    }
//-------------------------------------------------------------------------
// Сумма платежей по партии
    /**
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
                                kod_part=$this->kod_part
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
     * Процент оплаты распледеленный по партии
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
     * @param $kod_part
     * @return int
     */
    public static function getNumbOtgruz($kod_part)
    {
        $db = new Db();

        $rows = $db->rows("
                            SELECT
                              view_sklad_otgruzka.kod_part,
                              Sum(view_sklad_otgruzka.numb) AS summ_numb
                            FROM
                              view_sklad_otgruzka
                            WHERE
                              view_sklad_otgruzka.kod_part = $kod_part
                            GROUP BY
                              view_sklad_otgruzka.kod_part");
        if($db->cnt==0)
            return 0;

        $res = $rows[0]['summ_numb'];

        return $res;
    }
}