<?php
include_once("class_doc.php");
include_once("class_func.php");
include_once("class_elem.php");

class Org
{
    public $kod_org = 0; // Идентификатор
    public $Data;
    public $max_str_length = 30;

//-----------------------------------------------------------

    /**
     * @param int $kod_org_selected
     * @param string $Name
     * @param string $ID
     * @return string
     */
    public static function formSelList($kod_org_selected = 0, $Name = 'nazv_krat', $ID = 'kod_org')
    {
        if (!isset($kod_org_selected))
            $kod_org_selected = -1;

        $res = "<select name='$ID' id='$ID'>";

        $db = new DB();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM org WHERE del=0 ORDER BY poisk;");

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

            $search_name = "$poisk $N";

            $search_name = func::clearString($search_name);
            if(strlen($search_name)>50) // Если должность длиннее максимальной строки
                $search_name = mb_substr($search_name,0,50,'UTF-8')."...";

            $res .= "<option $sel value='$kod_org'>$search_name</option>";

        }

        $res .= '</select>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка-выбора контрагента
     * @param int $kod_org_selected - выбранный элемент
     * @return string
     */
    public static function formSelList2($kod_org_selected = 0)
    {
        $db = new Db();

        $sql = /** @lang MySQL */
            "SELECT * FROM org WHERE del=0 ORDER BY poisk;";

        $rows = $db->rows($sql);

        if ($db->cnt == 0)
            return "";

        $res = "<select id='kod_org' name='kod_org' placeholder=\"Выбрать контрагента...\">
";
        for ($i = 0; $i < $db->cnt; $i++) {
            $name = self::getSearchName($rows[$i]);
            $kod_org = $rows[$i]['kod_org'];

            $selected = "";
            if ($rows[$i]['kod_org'] == $kod_org_selected)
                $selected = " selected='selected'";

            $res .= "<option value='$kod_org' $selected>$name</option>\r\n";
        }
        $res .= '</select>
        <script type="text/javascript">
                        var kod_org, $kod_org;
    
                        $kod_org = $("#kod_org").selectize({
                            onChange: function(value) {
            if (!value.length) return;
        }
                        });
                        kod_org = $kod_org[0].selectize;
                </script>';

        return $res;
    }
//-----------------------------------------------------------

    /**
     */
    public function formRecv()
    {
        $this->getData();

        $row = $this->Data;
        $www = func::Link($row['www']);

        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Изменить', "SetRecv");
        $res = "<div class='btn'>
                    <div><b>Реквизиты</b></div>
                    <div>$btn_add</div>
                </div>";

        if (!func::issetFlag("SetRecv")) {
            $res .= '<table border=1 cellspacing=0 cellpadding=0>
                <tr>
                    <td bgcolor="#CCCCCC" width="50">ИНН</td><td width="250">' . $row['inn'] . '</td>
                    <td width="50" bgcolor="#CCCCCC">КПП</td><td  width="250">' . $row['kpp'] . '</td>
                </tr>
                <tr>
                    <td bgcolor="#CCCCCC" width="50">ОГРН</td><td width="250">' . self::formOGRN($row['ogrn']) . '</td>
                    <td width="50" bgcolor="#CCCCCC"></td><td  width="250"></td>
                </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">Р/сч</td><td>' . $row['r_sch'] . '</td>
			        <td bgcolor="#CCCCCC">К/сч</td><td>' . $row['k_sch'] . '</td>
			    </tr>
			    <tr>
			        <td bgcolor="#CCCCCC">Банк</td><td>' . $row['bank_rs'] . '</td>
			        <td bgcolor="#CCCCCC"></td><td></td>
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
			        <td bgcolor="#CCCCCC">WWW</td><td>' . $www . '</td>
			        <td bgcolor="#CCCCCC">E-mail</td><td>' . $row['e_mail'] . '</td>
			    </tr>
			  </table>';
        } else {
            $res .=
                '
			  <form id="form1" name="form1" method="post" action="">
			  <table border=1 cellspacing=0 cellpadding=0 width="100%">
			  <tr>
                  <td bgcolor="#CCCCCC">ИНН</td><td width="250"><input  name="inn" id="inn" value="' . $row['inn'] . '"/></td>
                  <td bgcolor="#CCCCCC">КПП</td><td  width="250" ><input  name="kpp" id="kpp" value="' . $row['kpp'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">ОГРН</td><td width="250"><input  name="ogrn" id="ogrn" value="' . $row['ogrn'] . '"/></td>
                  <td bgcolor="#CCCCCC"></td><td  width="250" ></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">Р/сч</td><td><input  name="r_sch" id="r_sch" value="' . $row['r_sch'] . '" /></td>
                  <td bgcolor="#CCCCCC">К/сч</td><td><input  name="k_sch" id="k_sch" value="' . $row['k_sch'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">Банк</td><td><textarea rows=3 name="bank_rs" id="bank_rs">' . $row['bank_rs'] . '</textarea></td>                  
                  <td bgcolor="#CCCCCC"></td><td></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">БИК</td><td><input  name="bik" id="bik" value="' . $row['bik'] . '" /></td>
                  <td bgcolor="#CCCCCC">ОКПО</td><td><input  name="okpo" id="okpo" value="' . $row['okpo'] . '" /></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">ОКОНХ</td><td><input name="okonh" id="okonh" value="' . $row['okonh'] . '"/></td>
                  <td bgcolor="#CCCCCC"></td><td></td>
			  </tr>
			  <tr>
                  <td bgcolor="#CCCCCC">WWW</td><td><input name="www" id="www" value="' . $row['www'] . '"/></td>
                  <td bgcolor="#CCCCCC">E-mail</td><td><input name="e_mail" id="e_mail" value="' . $row['e_mail'] . '" /></td>
			  </tr>
			  </table>
			  <input id="AddRecvForm" type="hidden" value="1" name="AddRecvForm"/> 
			  <input type="submit" value="Сохранить" />
			  </form>';
            $res .= func::Cansel();
        }
        return $res;
    }
//-----------------------------------------------------------

    /**
     * @return mixed
     */
    public function getData()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM org WHERE kod_org=$this->kod_org;");
        if ($db->cnt == 0)
            return;

        $this->Data = $rows[0];
        return;
    }

//-------------------------------------
//
    /**
     * Вывод списка Адресов по Организации (юр/факт/почт)
     * @return string
     */
    public function formAdress()
    {

        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddOrgAdr');
        $res = "<div class='btn'>
                    <biv><b>Адреса</b></biv>
                    <div>$btn_add</div>
                </div>";

        if (func::issetFlag("AddOrgAdr")) {
            $res .= '<form id="form1" name="form1" method="post" action="">
                      <table border="0">
                        <tr>
                          <td width="10%">Адрес</td>
                          <td width="80%"><input size="80%" name="adres" id="adres" /></td>
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
                        <input type="submit" name="button" id="button" value="Добавить" />
                        <input type="hidden" name="AddOrgAdr" id="AddOrgAdr" value="1" />
                    </form>';
            $res .= func::Cansel();
        }

        $db = new DB();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM adresa WHERE kod_org=$this->kod_org AND del=0 ORDER BY kod_adresa DESC;");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return $res;

        $res .= '<table border="0" cellspacing=0 cellpadding=0 width="100%">';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $type = "Юридический: ";

            if ($row['type'] == 1)
                $type = "Фактический: ";
            elseif ($row['type'] == 3)
                $type = "Почтовый: ";

            $btn_del = func::ActButton2('', 'Удалить', "DelAddr", "kod_adresa_del", $row['kod_adresa']);
            $res .= '<tr>
                           <td>' . $type . $row['adres'] . '</td><td>' . $btn_del . '</td>
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
    public function formOrg()
    {
        if (!isset($this->Data))
            $this->getData();

        $nazv_krat = $this->Data['nazv_krat'];
        $nazv_poln = $this->Data['nazv_poln'];

        $res = "";

        if ($nazv_krat != $nazv_poln)
            $res .= '<h1>' . $this->getFormLink() . '</h1>' . $nazv_poln . '<br>';
        else
            $res .= '<h1>' . $this->getFormLink() . '</h1>';

        $poisk = $this->Data['poisk'];

        $btn_del = "";
        if ($_SESSION['MM_UserGroup'] === "admin")
            $btn_del = Func::ActButton2($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Удалить', 'DelOrg', "kod_org_del", $this->kod_org);

        $btn_edit = Func::ActButton('', 'Изменить', 'formAddEdit');

        $res .= "<div class='btn'>
                    <div>$poisk</div>
                    <div>$btn_edit</div>
                    <div>$btn_del</div>
                </div>";

        // Save-------------------------
        if (func::issetFlag('formAddEdit')) {
            $res .= $this->formAddEdit(1);
        }

        return $res;
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

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM org WHERE del=0 ORDER BY poisk;");

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

            $nazv_poln_str = '';
            if ($nazv_krat != $nazv_poln)
                $nazv_poln_str = $nazv_poln;

            $inn = "";
            if ($row['inn'] != "")
                $inn = " ИНН " . func::clearNum($row['inn']);

            $ogrn = "";
            if ($row['ogrn'] != "")
                $ogrn = "<br>ОГРН " . self::formOGRN($row['ogrn']);

            $tab_row = /** @lang HTML */
                "<tr>
                      <td></td>
                      <td><a href=\"form_org.php?kod_org=$kod_org \">$poisk</a></td>
                      <td><a href=\"form_org.php?kod_org=$kod_org \">$nazv_krat</a></td>
                      <td><a href=\"form_org.php?kod_org=$kod_org \">$nazv_poln_str $inn</a>$ogrn</td>
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
     * @return string
     */
    public function formPhones()
    {
        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', "AddOrgPhone");
        $res = "<div class='btn'>
                    <biv><b>Телефоны</b></biv>
                    <div>$btn_add</div>
                </div>";

        if (func::issetFlag("AddOrgPhone")) {
            $res .= '<form id="form1" name="form1" method="post" action="">
                      <table border="0">
                        <tr>
                          <td width="133">Номер</td>
                          <td ><input name="phone" id="phone" /></td>
                        </tr>
                      </table>
                    <input type="submit" name="button" id="button" value="Добавить" />
                    <input type="hidden" name="AddOrgPhone" id="AddOrgPhone" value="1" />
                </form>';
            $res .= func::Cansel();
        }

        $db = new DB();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM org_data WHERE del=0 AND kod_org=$this->kod_org;");
        $cnt = $db->cnt;

        if ($cnt == 0)
            return $res;

        // Таблица с телефонами
        $res .= "<table border=0 cellspacing=0 cellpadding=0 width='200'>";
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            $btn_del = func::ActButton2('', 'Удалить', "DelOrgData", "kod_dat_del", $row['kod_dat']);

            $res .= '<tr>
                        <td>' . $row['data'] . '</td>
                        <td>' . $btn_del . '</td>
		             </tr>';
        }
        $res .= "</table>";

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

        $db->query(/** @lang MySQL */
            "UPDATE org_data SET del=1 WHERE kod_dat=$kod_dat");
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

        $body = "";

        if ((int)$Edit > 0) {
            if ((int)$Edit == 1)
                $this->getData();

            if((int)$Edit == 2)
                $body = /** @lang HTML */
                    "<input type='hidden' name='AddAnyway' value='AddAnyway' >";

            $row = $this->Data;

            $poisk = htmlspecialchars($row['poisk']);
            $nazv_krat = htmlspecialchars($row['nazv_krat']);
            $nazv_poln = htmlspecialchars($row['nazv_poln']);
        }

        $res = /** @lang HTML */
            "<form id='form1' name='form1' method='post' action=''>
                      <table border='0'>
                        <tr>
                          <td width='208'>Наименование для поиска</td>
                          <td><span id='sprytextfield_poisk'>
                            <input name='poisk' id='poisk' size='30' value='$poisk' />
                          <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td>Краткое наименование</td>
                          <td><span id='sprytextfield_nazv_krat'>
                            <input name='nazv_krat' id='nazv_krat' size='30' value='$nazv_krat' />
                          <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td>Полное наименование</td>
                          <td><span id='sprytextfield_nazv_poln'>
                            <input name='nazv_poln' id='nazv_poln' size='30' value='$nazv_poln' />
                          <span class='textfieldRequiredMsg'>A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td><input type='submit' name='button' id='button' value='Сохранить' />
                          <td><input type='hidden' value='FormAddEdit' name='FormName'>$body</td>
                        </tr>
                      </table>
                    </form>";
        $res .= func::Cansel();
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
        Db::getHistoryString("org", "kod_org", $this->kod_org);

        $db = new Db();
        $poisk = $db->real_escape_string($poisk);
        $nazv_krat = $db->real_escape_string($nazv_krat);
        $nazv_poln = $db->real_escape_string($nazv_poln);

        $db->query(/** @lang MySQL */
            "UPDATE org SET poisk = '$poisk', nazv_krat='$nazv_krat', nazv_poln='$nazv_poln' WHERE kod_org=$this->kod_org;");
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
        if (!isset($poisk, $nazv_krat))
            return;

        $db = new Db();
        $poisk = $db->real_escape_string($poisk);
        $nazv_krat = $db->real_escape_string($nazv_krat);
        $nazv_poln = $db->real_escape_string($nazv_poln);

        // todo - Подумать как сделать красивей
        if (!isset($_POST['AddAnyway'])) {
            // Проверка на наличие контрагента
            $db->rows(/** @lang MySQL */
                "SELECT * FROM org WHERE (poisk='$poisk') OR (nazv_krat='$nazv_krat') OR (nazv_poln='$nazv_poln')");
            if ($db->cnt > 0) {
                echo "Данная организация уже сущетвует. Подтвердите добавление.";

                $this->Data['poisk']=$poisk;
                $this->Data['nazv_krat']=$nazv_krat;
                $this->Data['nazv_poln']=$nazv_poln;

                echo $this->formAddEdit(2);
                exit("");
            }
        }
        $db->query(/** @lang MySQL */
            "INSERT INTO org (poisk,nazv_krat,nazv_poln) VALUES('$poisk','$nazv_krat','$nazv_poln')");
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Реквизиты
     * @param string $inn
     * @param string $kpp
     * @param string $ogrn
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
    public function SetRecv($inn = '', $kpp = '', $ogrn = '', $r_sch = '', $bank_rs = '', $k_sch = '', $bank_ks = '', $bik = '', $okpo = '', $okonh = '', $www = '', $e_mail = '')
    {

        $kod_org = $this->kod_org;

        $inn = func::clearNum($inn);
        $kpp = func::clearNum($kpp);
        $ogrn = func::clearNum($ogrn);
        $r_sch = func::clearNum($r_sch);
        $k_sch = func::clearNum($k_sch);
        $bik = func::clearNum($bik);

        Db::getHistoryString("org", "kod_org", $kod_org);
        $db = new DB();
        $db->query(/** @lang MySQL */
            "UPDATE org SET inn = '$inn', kpp = '$kpp', ogrn='$ogrn', r_sch = '$r_sch', bank_rs = '$bank_rs', k_sch = '$k_sch', bank_ks = '$bank_ks', 
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
            $db->query(/** @lang MySQL */
                "UPDATE adresa SET del=1 WHERE kod_adresa=$kod_adresa");

        } else
            echo "Ошибка: Не задан ID адреса";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление организации
     * @param $kod_org
     */
    public function Delete($kod_org)
    {
        $db = new Db();

        if (isset($kod_org)) {
            $db->query(/** @lang MySQL */
                "UPDATE org SET del=1 WHERE kod_org=$kod_org");

        } else
            exit("Ошибка: Не задан ID организации");
    }
//----------------------------------------------------------------------
//

    /**
     * Добавить Адрес
     * @param string $adres
     * @param int $type
     */
    public function AddAdr($adres = "", $type = 1)
    {
        if ($adres == "")
            return;

        $db = new DB();
        $kod_org = $this->kod_org;
        $kod_user = func::kod_user();
        $adres = $db->real_escape_string($adres);
        $type = (int)$type;
        $db->query(/** @lang MySQL */
            "INSERT INTO adresa (adres,kod_org,type,kod_user) VALUES('$adres',$kod_org,$type,$kod_user)");
    }
//----------------------------------------------------------------------
//
    /**
     * Добавить Телефон
     * @param $phone
     */
    public function AddPhone($phone)
    {
        if ($phone == "")
            return;

        $db = new DB();
        $kod_org = $this->kod_org;
        $kod_user = func::kod_user();
        $phone = $db->real_escape_string($phone);
        $db->query(/** @lang MySQL */
            "INSERT INTO org_data (data,kod_org,kod_user) VALUES('$phone',$kod_org,$kod_user)");
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
     * Оплаченная номенклатура по Договорам
     * @param string $sql
     * @return string
     */
    public function formOrgNomen($sql = "")
    {
        $where = ""; // Фильтры
        if (isset($_GET['y'])) {
            $data_s = (int)$_GET['y'] . "-01-01";
            $data_e = ((int)$_GET['y'] + 1) . "-01-01";
            $where = " AND data_postav>='$data_s' AND data_postav<'$data_e'";
        }

        $db = new Db();
        if ($sql != "")
            $rows = $db->rows($sql);
        else
            $rows = $db->rows(/** @lang MySQL */
                "SELECT view_rplan.kod_elem, 
                            view_rplan.name,
                            view_rplan.data_postav, 
                            sum(view_rplan.numb) AS summ_numb, 
                            view_rplan.kod_org, 
                            view_dogovor_summa_plat.summa_plat
                        FROM view_rplan INNER JOIN view_dogovor_summa_plat ON view_rplan.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                        WHERE view_rplan.kod_org=$this->kod_org $where
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
     * @return float
     */
    public function getSummPlatByCurrentDocs()
    {
        // Сумма платежей по действующим договорам
        $sql = /** @lang MySQL */
            "SELECT
                    Sum(view_dogovor_summa_plat.summa_plat) AS summa_plat,
                    dogovory.kod_org
                FROM
                  view_dogovor_summa_plat
                INNER JOIN dogovory ON dogovory.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                WHERE dogovory.kod_org=$this->kod_org AND doc_type = 1
                AND
                      dogovory.zakryt = 0
                GROUP BY
                  dogovory.kod_org
                ";
        $db = new Db();
        $rows = $db->rows($sql);
        if (count($rows) >= 1) {
            $row = $rows[0];
            return $summa_plat = (double)$row['summa_plat']; // Сумма платежей по действующим договорам
        } else
            return 0.;
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

        // Сумма действующих договоров
        $sql = /** @lang MySQL */
            "SELECT
                    dogovory.kod_org,
                    Sum(view_dogovor_summa.dogovor_summa) AS summa_dogovorov
                FROM
                    view_dogovor_summa
                INNER JOIN dogovory ON view_dogovor_summa.kod_dogovora = dogovory.kod_dogovora
                WHERE dogovory.kod_org=$this->kod_org AND dogovory.zakryt = 0 AND doc_type = 1
                GROUP BY
                    dogovory.kod_org
                ";
        $rows = $db->rows($sql);
        if (count($rows) >= 1)
            $row = $rows[0];
        else
            return "0"; // По текущим договорам - 0

        $summa_dogovorov = (double)$row['summa_dogovorov']; // Сумма действующих договоров

        // Сумма платежей по действующим договорам
        $sql = /** @lang MySQL */
            "SELECT
                    Sum(view_dogovor_summa_plat.summa_plat) AS summa_plat,
                    dogovory.kod_org
                FROM
                  view_dogovor_summa_plat
                INNER JOIN dogovory ON dogovory.kod_dogovora = view_dogovor_summa_plat.kod_dogovora
                WHERE dogovory.kod_org=$this->kod_org AND doc_type = 1
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

        return $res = Func::Rub($summa_dogovorov - $summa_plat); // Возвращаем разницу между суммой договоров и суммой платежей
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Сумма отгруженного товара
     * @return float
     */
    public function getSummOtgruz()
    {
        $db = new Db();

        $sql = /** @lang MySQL */
            "SELECT * 
                FROM view_rplan 
                WHERE kod_org = $this->kod_org
                AND zakryt=0 AND doc_type=1";

        $rows = $db->rows($sql);

        if ($db->cnt == 0)
            return 0.;

        $summ = 0.;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if ((int)$row['numb_otgruz'] == 0)
                continue;

            $summ += (int)$row['numb_otgruz'] * Part::getPriceWithNDS($row);
        }

        return $summ;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Задолженность по отгруженному товару - разница между суммой отгруженного товара и суммой платежей
     * @return string
     */
    public function getDolgOtgruz()
    {
        $res = $this->getSummOtgruz() - $this->getSummPlatByCurrentDocs();

        if ((int)$res == 0)
            $res = "";
        else
            $res = func::Rub($res);

        return $res;
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
        $kod_org_main = config::$kod_org_main;

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
                    AND kod_org <> $kod_org_main
                    AND doc_type = 1
                    GROUP BY
                    view_dogovor_data.kod_org,
                    view_dogovor_data.nazv_krat
                    ORDER BY
                    summa_dogovor_ostat DESC");

        $res = '<table>
                    <tr>
                       <td>Название</td>
                       <td>К оплате</td>
                       <td>По отгрузкам</td>
                    </tr>';

        if ($db->cnt == 0)
            return '';

        $summ = 0;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $this->kod_org = $row['kod_org'];
            $res .= '<tr>
                        <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                        <td align="right">' . Func::Rub($row['summa_dogovor_ostat']) . '</td>
                        <td align="right">' . $this->getDolgOtgruz() /* todo - медленный запрос, надо переделать */ . '</td>
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

        if (isset($_POST['FormName']))
            if ($_POST['FormName'] == "FormAddEdit")
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
            $this->SetRecv($_POST['inn'], $_POST['kpp'], $_POST['ogrn'], $_POST['r_sch'], $_POST['bank_rs'], $_POST['k_sch'], $_POST['bank_ks'], $_POST['bik'], $_POST['okpo'], $_POST['okonh'], $_POST['www'], $_POST['e_mail']);
            $event = true;
        }

        if (isset($_POST['AddContact']) and (isset($_POST['famil']) or isset($_POST['name']))) {
            $this->AddKontakt($_POST['golg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
            $event = true;
        }

        if (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == 'DelOrgData' and isset($_POST['kod_dat_del'])) {
                $this->DelData($_POST['kod_dat_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'DelOrgLink' and isset($_POST['kod_link_del'])) {
                $this->DelOrgLink($_POST['kod_link_del']);
                $event = true;
            } elseif ($_POST['Flag'] == 'AddOrgLink' and isset($_POST['kod_org_slave'])) {
                $this->AddOrgLink($this->kod_org, $_POST['kod_org_slave'], $_POST['prim']);
                $event = true;
            } elseif ($_POST['Flag'] == 'DelOrg' and isset($_POST['kod_org_del'])) {
                $this->Delete($_POST['kod_org_del']);
                header('Location: http://' . $_SERVER['HTTP_HOST'] . "/form_orglist.php");
            }
        }

        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Связи контрагента с другими контрагентами
     * @param $kod_org
     * @return string
     */
    public static function formLinks($kod_org)
    {
        if (!isset($kod_org))
            return "";

        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddOrgLinkForm');
        $res = "<div class='btn'>
                    <biv><b>Связи</b></biv>
                    <div>$btn_add</div>
                </div>";

        $org = new Org();
        $org->kod_org = (int)$kod_org;

        if (func::issetFlag("AddOrgLinkForm")) {
            $sel = self::formSelList(0, 'nazv_krat', 'kod_org_slave');

            $res .= /** @lang HTML */
                "<form id='form1' name='form1' method='post' action=''>
                      <table border='0'>
                        <tr>
                          <td width='133'>Связь</td>
                          <td >$sel</td>
                          <td><input name='prim'></td>                         
                        </tr>
                      </table>
                    <input type='submit' name='button' id='button' value='Добавить' />
                    <input type='hidden' name='Flag' id='Flag' value='AddOrgLink' />
                </form>";
            $res .= func::Cansel();
        }

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                      org.nazv_krat,
                      org.kod_org,
                      org_slave.nazv_krat AS nazv_krat_slave,
                      org_slave.kod_org AS kod_org_slave,
                      org_links.prim,
                      org_links.kod_link
                    FROM
                      org
                      JOIN org_links
                        ON org.kod_org = org_links.master
                      JOIN org AS org_slave
                        ON org_links.slave = org_slave.kod_org
                    WHERE
                      (org_links.master = $kod_org
                      OR org_links.slave = $kod_org) AND org_links.del=0
                      ");

        if ($db->cnt == 0)
            return $res;

        $res .= '<table>';

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $kod = $row['kod_org_slave'];
            $nazv = $row['nazv_krat_slave'];
            $prim = $row['prim'];
            if ($kod_org == $row['kod_org_slave']) {
                $kod = $row['kod_org'];
                $nazv = $row['nazv_krat'];
            }

            $btn_del = func::ActButton2('', 'Удалить', "DelOrgLink", "kod_link_del", $row['kod_link']);
            $res .= "<tr>
                            <td><a href='form_org.php?kod_org=$kod'>$nazv</a></td>
                            <td align='right'>$prim</td>
                            <td align='right'>$btn_del</td>
                    </tr>";
        }
        $res .= '</table>';
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Удаление связи организации
     * @param $kod_link
     */
    private function DelOrgLink($kod_link)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE org_links SET del=1,kod_user=$kod_user WHERE kod_link=$kod_link");
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Удаление связи организации
     * @param $kod_org_master
     * @param $kod_org_slave
     * @param string $prim
     * @internal param $kod_link
     */
    private function AddOrgLink($kod_org_master, $kod_org_slave, $prim = "")
    {
        $db = new Db();
        $kod_user = func::kod_user();
        $prim = $db->real_escape_string($prim);
        $db->query(/** @lang MySQL */
            "INSERT INTO org_links (master, slave, prim,kod_user) VALUE ($kod_org_master,$kod_org_slave,'$prim',$kod_user)");
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Выдает строку для поиска по организациям - Поиск - Название крат - Название полн - Код
     * @param $row
     * @return mixed|string
     */
    public static function getSearchName($row)
    {
        if (count($row) === 0)
            return "";

        $nazv_krat = $row['nazv_krat'];
        $nazv_poln = $row['nazv_poln'];
        $poisk = $row['poisk'];
        $kod_org = $row['kod_org'];

        if ($poisk !== "" and $nazv_krat == $poisk)
            $nazv_krat = "";

        if ($nazv_poln !== "" and strpos($nazv_krat, $nazv_poln) !== false)
            $nazv_poln = "";

        if ($nazv_krat == $nazv_poln)
            $nazv_poln = "";

        return "$poisk $nazv_krat $nazv_poln $kod_org";
    }
//-----------------------------------------------------------
//
    /**
     * Вывод списка организаций с суммами платежей в заданный год - $_GET['y']
     * @param bool $itog
     * @return string
     */
    public function formOrgPays($itog = true)
    {
        $db = new DB();
        $year = date("Y");
        $kod_org_main = config::$kod_org_main;

        if (isset($_GET['y'])) {
            $year = (int)$_GET['y'];
        }
        $year_next = $year + 1;

        $w_kod_org = "kod_org<>$kod_org_main AND plat.del=0";
        if (isset($_GET['kod_org'])) {
            $kod_org = (int)$_GET['kod_org'];
            $w_kod_org .= " AND view_dogovory_nvs.kod_org=$kod_org ";
        }

        $rows = $db->rows(/** @lang MySQL */
            "SELECT sum(plat.summa) AS summ, 
                                        view_dogovory_nvs.nazv_krat,
                                        kod_org
                                  FROM plat INNER JOIN view_dogovory_nvs ON plat.kod_dogovora = view_dogovory_nvs.kod_dogovora
                                  WHERE DATE(plat.`data`) >= DATE('$year-01-01') AND DATE(plat.`data`) < DATE('$year_next-01-01')
                                          AND $w_kod_org
                                  GROUP BY view_dogovory_nvs.kod_org
                                  ORDER BY summ DESC;");
        $year_p = $year - 1;
        $res = /** @lang HTML */
            "<a href='form_orglist.php?pays&y=$year_p'>$year_p</a>
            $year
            <a href='form_orglist.php?pays&y=$year_next'>$year_next</a>
                <table><tr><td>Название</td><td>Сумма за период</td></tr>";

        if ($db->cnt == 0)
            return $res;

        $summ = 0;

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $res .= '<tr>
                        <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                        <td align="right"><a href="form_org_stat.php?kod_org=' . $row['kod_org'] . "&y=$year\">" . Func::Rub($row['summ']) . '</a></td>
                     </tr>';
            $summ += $row['summ'];
        }
        $res .= '</table>';
        if ($itog)
            $res .= '<br>Сумма: ' . Func::Rub($summ);
        return $res;
    }
//-----------------------------------------------------------
//
    /**
     * Форма со ссылкой на Контур.Фокус
     * @param $ogrn
     * @return string
     */
    public static function formOGRN($ogrn)
    {
        $ogrn = func::clearNum($ogrn);
        $res = /** @lang HTML */
            "<a target='_blank' href='https://focus.kontur.ru/entity?query=$ogrn'>$ogrn</a>";
        return $res;
    }

    //-----------------------------------------------------------------
}