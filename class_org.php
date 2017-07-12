<?php
include_once("class_doc.php");
include_once("class_func.php");
include_once "class_elem.php";

class Org
{
    public $kod_org = 0; // Идентификатор
    public $Data;

//-----------------------------------------------------------

    /**
     * @param int $kod_org_selected
     * @param string $Name
     * @param string $ID
     * @return string
     */
    public static function formSelList($kod_org_selected = -1, $Name = 'nazv_krat', $ID = 'kod_org')
    {
        if (!isset($kod_org_selected))
            $kod_org_selected = -1;

        $res = "<select name='$ID' id='$ID'>";

        $db = new DB();

        $rows = $db->rows("SELECT * FROM org WHERE del=0 ORDER BY poisk");

        $cnt = $db->cnt;


        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Организация
            $kod_org = (int)$row['kod_org'];
            $poisk = $row['poisk'];

            $sel = '';
            if ($row['kod_org'] == $kod_org_selected)
                $sel = "selected";

            $N = '';
            if ($Name !== '')
                $N = ' - ' . $row['nazv_krat'];

            $res .= "<option $sel value='$kod_org'>$poisk $N</option>";

        }

        $res .= '</select>';

        return $res;
    }

//-----------------------------------------------------------

    /**
     * @param int $Edit
     */
    public function formRecv($Edit = 0)
    {
        $this->getData();

        $row = $this->Data;
        $www = func::Link($row['www']);

        if ($Edit == 0) {
            echo '<table border=1 cellspacing=0 cellpadding=0>';
            echo
                '<tr>
                    <td bgcolor="#CCCCCC" width="50">ИНН</td><td width="250">' . $row['inn'] . '</td>
                    <td width="50" bgcolor="#CCCCCC">КПП</td><td  width="250">' . $row['kpp'] . '</td>
                </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">Р/сч</td><td>' . $row['r_sch'] . '</td>
			        <td bgcolor="#CCCCCC">К/сч</td><td>' . $row['k_sch'] . '</td>
			    </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">Банк Р/сч</td><td>' . $row['bank_rs'] . '</td>
			        <td bgcolor="#CCCCCC">Банк К/сч</td><td>' . $row['bank_ks'] . '</td>
			    </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">БИК</td><td>' . $row['bik'] . '</td>
			        <td bgcolor="#CCCCCC">ОКПО</td><td>' . $row['okpo'] . '</td>
			    </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">ОКОНХ</td><td>' . $row['okonh'] . '</td>
			        <td bgcolor="#CCCCCC"></td><td></td>
			    </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">WWW</td><td>'. $www .'</td>
			        <td bgcolor="#CCCCCC">E-mail</td><td>' . $row['e_mail'] . '</td>
			    </tr>
			  </table>';
        } else {
            echo
                '
			  <form id="form1" name="form1" method="post" action="">
			  <br>Реквизиты<br><table border=1 cellspacing=0 cellpadding=0 width="100%">
			  <tr>
                  <td bgcolor="#CCCCCC">ИНН</td><td width="250"><input type="text" name="inn" id="inn" value="' . $row['inn'] . '"/></td>
                  <td bgcolor="#CCCCCC">КПП</td><td  width="250" ><input type="text" name="kpp" id="kpp" value="' . $row['kpp'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">Р/сч</td><td><input type="text" name="r_sch" id="r_sch" value="' . $row['r_sch'] . '" /></td>
                  <td bgcolor="#CCCCCC">К/сч</td><td><input type="text" name="k_sch" id="k_sch" value="' . $row['k_sch'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">Банк Р/сч</td><td><textarea rows=3 name="bank_rs" id="bank_rs">' . $row['bank_rs'] . '</textarea></td>                  
                  <td bgcolor="#CCCCCC">Банк К/сч</td><td><textarea rows=3 name="bank_ks" id="bank_ks">' . $row['bank_ks'] . '</textarea></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">БИК</td><td><input type="text" name="bik" id="bik" value="' . $row['bik'] . '" /></td>
                  <td bgcolor="#CCCCCC">ОКПО</td><td><input type="text" name="okpo" id="okpo" value="' . $row['okpo'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">ОКОНХ</td><td><input type="text" name="okonh" id="okonh" value="' . $row['okonh'] . '"/></td>
                  <td bgcolor="#CCCCCC"></td><td></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">WWW</td><td><input type="text" name="www" id="www" value="' . $row['www'] . '"/></td>
                  <td bgcolor="#CCCCCC">E-mail</td><td><input type="text" name="e_mail" id="e_mail" value="' . $row['e_mail'] . '" /></td>
			  </tr>
			  </table>
			  <input id="AddRecvForm" type="hidden" value="1" name="AddRecvForm"/> 
			  <input type="submit" value="Сохранить" />
			  </form>
			  ';
            echo Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');
        }
    }

//-----------------------------------------------------------

    /**
     * @return mixed
     */
    public function getData()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM org WHERE del=0 AND kod_org=$this->kod_org");
        $this->Data = $rows[0];
        return;
    }

//-------------------------------------
//
    /**
     * Вывод списка Адресов по Организации (юр/факт/почт)
     * @param int $Add
     * @return string
     */
    public function formAdressList($Add = 0)
    {

        $res = "";
        if ($Add == 1) {
            return '<form id="form1" name="form1" method="post" action="">
                      <table border="0">
                        <tr>
                          <td width="10%">Адрес</td>
                          <td width="80%"><input type="text" size="80%" name="adres" id="adres" /></td>
                          <td width="10%">Тип</td>
                          <td >
                              <select name="type" id="type">
                                <option value="1">Фактический</option>
                                <option value="2">Юридический</option>
                                <option value="3">Почтовый</option>
                              </select>
                          </td>
                          </tr>
                      
                        </table>
                      <p>
                        <input type="submit" name="button" id="button" value="Добавить" />
                        <input type="hidden" name="AddOrgAdr" id="AddOrgAdr" value="1" />
                    </form>'. Func::Cansel();
        }

        $db = new DB();

        $rows = $db->rows("SELECT * FROM adresa WHERE kod_org=$this->kod_org AND del=0");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res .= '<table border="0" cellspacing=0 cellpadding=0 width="100%">';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $type = "Юридический: ";

            if ($row['type'] == 1)
                $type = "Фактический: ";
            elseif ($row['type'] == 3)
                $type = "Почтовый: ";

            $btn_del = func::ActButton2('','Удалить',"DelAddr","kod_adresa_del",$row['kod_adresa']);
            $res .= '<tr>
                           <td>' . $type . $row['adres'] .'</td><td>'. $btn_del . '</td>
                     </tr>';
        }

        $res .= '</table>';

        return $res;
    }

//-----------------------------------------------------------
//
    /**
     * Договоры по организации
     * @return string
     */
    public function formDocs()
    {
        $doc = new Doc();
        $doc->kod_org = $this->kod_org;
        return $doc->formDocsByOrg();
    }
//-----------------------------------------------------------
//
    /**
     * Вывод списка организаций
     * @param bool $echo
     * @return string
     */
    public function formOrgList($echo = false)
    {
        $db = new DB();

        $rows = $db->rows("SELECT * FROM org WHERE del=0 ORDER BY poisk");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "Список организаций пуст";

        $res = /** @lang HTML */
            "<table border=1 cellspacing=0 width=\"70%\" rules=\"rows\" frame=\"void\">
	                    <tr bgcolor=\"#CCCCCC\">
	                            <td width=\"20\">№</td>
	                            <td width=\"100\">Поиск</td>
	                            <td width=\"200\">Наименование краткое</td>
	                            <td width=\"200\">Наименование полное</td>
	                            <td width=\"100\">WWW</td>
	                            <td width=\"500\"></td>
	                    </tr>";

        if ($echo)
            echo $res;


        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $kod_org = $row['kod_org'];
            $nazv_krat = $row['nazv_krat'];
            $nazv_poln = $row['nazv_poln'];
            $poisk = $row['poisk'];
            $www = func::Link($row['www']);

            $FN = '';
            if ($nazv_krat != $nazv_poln)
                $FN = $nazv_poln;

            $tab_row = /** @lang HTML */
                "<tr>
                      <td></td>
                      <td><a href=\"form_org.php?kod_org= $kod_org \"> $poisk </a></td>
                      <td><a href=\"form_org.php?kod_org= $kod_org \">$nazv_krat</a></td>
                      <td><a href=\"form_org.php?kod_org= $kod_org \"> $FN </a></td>
                      <td> $www </td>
		            </tr>";
            if ($echo)
                echo $tab_row;
            else
                $res .= $tab_row;

        }
        $tab_row = '</table>';

        if ($echo)
            echo $tab_row;
        else {
            $res .= $tab_row;
            return $res;
        }
        return "";
    }
//-----------------------------------------------------------------
//
    /**
     * Телефоны организации + Форма добавления
     * @param int $Add
     * @return string
     */
    public function formPhones($Add = 0)
    {
        if ($Add == 1) {
            echo '<form id="form1" name="form1" method="post" action="">
                      <table border="0">
                        <tr>
                          <td width="133">Номер</td>
                          <td ><input type="text" name="phone" id="phone" /></td>
                        </tr>
                      </table>
                  <p>
                    <input type="submit" name="button" id="button" value="Добавить" />
                    <input type="hidden" name="AddOrgPhone" id="AddOrgPhone" value="1" />
                </form>';
            Func::Cansel(1);
        }

        $db = new DB();

        $rows = $db->rows("SELECT * FROM org_data WHERE del=0 AND kod_org=$this->kod_org");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";


        $res = '<br>Телефоны<br>
                <table border=0 cellspacing=0 cellpadding=0 width="100%">';


        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $btn_del = func::ActButton2('','Удалить',"DelOrgData","kod_dat_del",$row['kod_dat']);

            $res .= '<tr>
                        <td>' . $row['data'] . '</td>
                        <td>'.$btn_del.'</td>
		            </tr>';
        }
        $res .= '</table>';

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление Данных
     * @param int $kod_dat
     */
    public function DelData($kod_dat)
    {
        $db = new Db();

        $db->query("UPDATE org_data SET del=1 WHERE kod_dat=$kod_dat");
    }
//---------------------------------------------------------------------
//
    /**
     * Форма Добавления/Редактирования Организации
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit = 0)
    {

        $poisk = "";
        $nazv_krat = "";
        $nazv_poln = "";

        if ($Edit == 1) {

            $this->getData();
            $row = $this->Data;

            $poisk = htmlspecialchars($row['poisk']);
            $nazv_krat = htmlspecialchars($row['nazv_krat']);
            $nazv_poln = htmlspecialchars($row['nazv_poln']);
        }

        $res = /** @lang HTML */
            '<form id="form1" name="form1" method="post" action="">
                      <table border="0">
                        <tr>
                          <td width="208">Наименование для поиска</td>
                          <td><span id="sprytextfield_poisk">
                            <input name="poisk" type="text" id="poisk" size="30" value="' . $poisk . '" />
                          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td>Краткое наименование</td>
                          <td><span id="sprytextfield_nazv_krat">
                            <input name="nazv_krat" type="text" id="nazv_krat" size="30" value="' . $nazv_krat . '" />
                          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td>Полное наименование</td>
                          <td><span id="sprytextfield_nazv_poln">
                            <input name="nazv_poln" type="text" id="nazv_poln" size="30" value="' . $nazv_poln . '" />
                          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td><input type="submit" name="button" id="button" value="Сохранить" />
                          <td><input type="hidden" value="FormAddEdit" name="FormName"></td>
                        </tr>
                      </table>
                    </form>';
        $res .= Func::Cansel(0);

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Сохранить изменения
     * @param $poisk
     * @param $nazv_krat
     * @param $nazv_poln
     */
    public function Save($poisk, $nazv_krat, $nazv_poln)
    {
        $db = new Db();

        $db->query("UPDATE org SET poisk = '$poisk', nazv_krat='$nazv_krat', nazv_poln='$nazv_poln' WHERE kod_org=" . $this->kod_org);
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Организацию
     * @param $poisk
     * @param $nazv_krat
     * @param $nazv_poln
     */
    public function AddOrg($poisk, $nazv_krat, $nazv_poln)
    {
        $db = new Db();

        $db->query("INSERT INTO org (poisk,nazv_krat,nazv_poln) VALUES('$poisk','$nazv_krat','$nazv_poln')");
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Реквизиты
     * @param string $inn
     * @param string $kpp
     * @param string $r_sch
     * @param string $bank_rs
     * @param string $k_sch
     * @param string $bank_ks
     * @param string $bik
     * @param string $okpo
     * @param string $okonh
     * @param string $www
     * @param string $e_mail
     */
    public function SetRecv($inn = '', $kpp = '', $r_sch = '', $bank_rs = '', $k_sch = '', $bank_ks = '', $bik = '', $okpo = '', $okonh = '', $www = '', $e_mail = '')
    {
        $db = new DB();
        $kod_org = $this->kod_org;

        $db->query("UPDATE org SET inn = '$inn', kpp = '$kpp', r_sch = '$r_sch', bank_rs = '$bank_rs', k_sch = '$k_sch', bank_ks = '$bank_ks', 
                    bik = '$bik', okpo = '$okpo', okonh = '$okonh', www = '$www', e_mail = '$e_mail' WHERE kod_org =$kod_org");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление адреса
     * @param $kod_adresa
     */
    public function DelAddr($kod_adresa)
    {
        $db = new Db();

        if (isset($kod_adresa)) {
            $db->query("UPDATE adresa SET del=1 WHERE kod_adresa=$kod_adresa");

        } else
            echo "Ошибка: Не задан ID адреса";
    }

//----------------------------------------------------------------------
//

    /**
     * Добавить Адрес
     * @param string $adres
     * @param int $type
     */
    public function AddAdr($adres = '', $type = 1)
    {
        $db = new DB();
        $kod_org = $this->kod_org;

        $db->query("INSERT INTO adresa (adres,kod_org,type) VALUES('$adres',$kod_org,$type)");
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Телефон
     * @param $phone
     */
    public function AddPhone($phone)
    {
        $db = new DB();
        $kod_org = $this->kod_org;

        $db->query("INSERT INTO org_data (data,kod_org) VALUES('$phone',$kod_org)");
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Контакт
     * @param $dolg
     * @param $famil
     * @param $name
     * @param $otch
     */
    public function AddKontakt($dolg, $famil, $name, $otch)
    {
        $c = new Kontakt();
        $c->kod_org = $this->kod_org;
        $c->AddKontakt($dolg, $famil, $name, $otch);
    }
//----------------------------------------------------------------------
//
    /**
     * Заказанная номенклатура по Договорам
     * @return string
     */
    public function formOrgNomen()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang SQL */
                "SELECT view_rplan.kod_elem, 
                            view_rplan.name, 
                            sum(view_rplan.numb) AS summ_numb, 
                            view_rplan.kod_org, 
                            view_dogovor_summa_plat.summa_plat
                        FROM view_rplan INNER JOIN view_dogovor_summa_plat ON view_rplan.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                        WHERE view_rplan.kod_org=$this->kod_org
                        AND
                        view_dogovor_summa_plat.summa_plat>0
                        GROUP BY view_rplan.kod_elem
                        ORDER BY summ_numb DESC
                      ");

        if ($db->cnt == 0)
            return "";

        $res = '<table border=0 cellspacing=0 cellpadding=0 width="100%">';

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            $name = $row['name'];
            $form_link = "<a href='form_elem.php?kod_elem=$kod_elem'>$name</a>";

            $res .= '<tr>
                        <td width="100%">' . $form_link . ' </td>
                        <td align="right">' . (int)$row['summ_numb'] . '</td>
                      </tr>';

        }
        $res .= '</table>';
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Документы Организации
     * @return string
     */
    public function Docum()
    {
        $d = new Docum();
        return $d->formDocum('Org', $this->kod_org);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Задолженность по действующим договорам
     * @return string
     */
    public function getDolg()
    {
        $db = new DB();
        $sql = "SELECT
                    dogovory.kod_org,
                    Sum(view_dogovor_summa.dogovor_summa) AS summa_dogovorov
                FROM
                    view_dogovor_summa
                INNER JOIN dogovory ON view_dogovor_summa.kod_dogovora = dogovory.kod_dogovora
                WHERE dogovory.kod_org=$this->kod_org AND dogovory.zakryt = 0
                GROUP BY
                    dogovory.kod_org
                ";
        $rows = $db->rows($sql);
        if (count($rows) >= 1)
            $row = $rows[0];
        else
            return "0"; // По текущим договорам - 0

        $summa_dogovorov = (double)$row['summa_dogovorov']; // Сумма действующих договоров

        $sql = "SELECT
                    Sum(view_dogovor_summa_plat.summa_plat) AS summa_plat,
                    dogovory.kod_org
                FROM
                  view_dogovor_summa_plat
                INNER JOIN dogovory ON dogovory.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                WHERE dogovory.kod_org=$this->kod_org
                AND
                      dogovory.zakryt = 0
                GROUP BY
                  dogovory.kod_org
                ";

        $rows = $db->rows($sql);
        if (count($rows) >= 1) {
            $row = $rows[0];
            $summa_plat = (double)$row['summa_plat']; // Сумма платежей по действующим договорам
        } else
            return Func::Rub($summa_dogovorov);

        return $res = Func::Rub($summa_dogovorov - $summa_plat);
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Должники
     * @return string
     */
    public function formDolgOrg()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang SQL */
            "SELECT
                    view_dogovor_data.kod_org,
                    view_dogovor_data.nazv_krat,
                    Sum(view_dogovor_data.dogovor_ostat) AS summa_dogovor_ostat
                    FROM
                        view_dogovor_data
                    WHERE
                        zakryt <> 1
                    AND dogovor_ostat > 1
                    AND kod_org <> 683
                    GROUP BY
                    view_dogovor_data.kod_org,
                    view_dogovor_data.nazv_krat
                    ORDER BY
                    summa_dogovor_ostat DESC");

        $res = '<table><tr><td>Название</td><td>Задолженность</td></tr>';

        if ($db->cnt == 0)
            return '';

        $summ = 0;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $res .= '<tr>
                        <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                        <td align="right">' . Func::Rub($row['summa_dogovor_ostat']) . '</td>
                     </tr>';
            $summ += $row['summa_dogovor_ostat'];
        }
        $res .= '</table>';
        $res .= '<br>Сумма: ' . Func::Rub($summ);
        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Ссылка на форму Огранизации
     * @return string
     */
    public function getFormLink()
    {
        if (!isset($this->Data))
            $this->getData();

        $kod_org = $this->kod_org;
        $nazv_krat = $this->Data['nazv_krat'];

        return "<a href='form_org.php?kod_org=$kod_org'>$nazv_krat</a>";
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * События форм
     *
     */
    public function Events()
    {
        $event = false;
        if (isset($_POST['AddOrgPhone']) and (isset($_POST['phone']))) {
            $this->AddPhone($_POST['phone']);
            $event = true;
        }

        if (isset($_POST['AddOrgAdr']) and (isset($_POST['adres']))) {
            $this->AddAdr($_POST['adres'], $_POST['type']);
            $event = true;
        }

        if(isset($_POST['FormName']))
            if($_POST['FormName']=="FormAddEdit")
                if (isset($_POST['poisk']) and isset($_POST['nazv_krat']) and isset($_POST['nazv_poln']))
                    if ($_POST['poisk'] != '' and $_POST['nazv_krat'] != '' and $_POST['nazv_poln'] != '') {
                        $this->Save($_POST['poisk'], $_POST['nazv_krat'], $_POST['nazv_poln']);
                        $event = true;
                    }

        if (isset($_POST['kod_adresa_del'])) { // Удаление накладной
            $this->DelAddr($_POST['kod_adresa_del']);
            $event = true;
        }

        if (isset($_POST['AddRecvForm'])) {
            $this->SetRecv($_POST['inn'], $_POST['kpp'], $_POST['r_sch'], $_POST['bank_rs'], $_POST['k_sch'], $_POST['bank_ks'], $_POST['bik'], $_POST['okpo'], $_POST['okonh'], $_POST['www'], $_POST['e_mail']);
            $event = true;
        }

        if (isset($_POST['AddContact']) and (isset($_POST['famil']) or isset($_POST['name']))) {
            $this->AddKontakt($_POST['golg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
            $event = true;
        }

        if(isset($_POST['Flag'])){
            if($_POST['Flag']=='DelOrgData' and isset($_POST['kod_dat_del']))
            {
                $this->DelData($_POST['kod_dat_del']);
                $event = true;
            }
        }

        if($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);

    }

}