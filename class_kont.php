<?php
include_once "class_db.php";

class Kontact
{
    public $kod_dogovora = 0; // Код Договора
    public $kod_org = 0; // Код Организации
    public $kod_kontakta = 0; // Код Контакта
    public $Data;
    public $Name;
    public $OrgName;
    public $ContArray; // Массив контактов по Договору

    //------------------------------------------------------------------------
    // Контакты Договора
    /**
     * Формирует массив контактов договора или организации
     * @param string $Doc_Org -
     * @return int
     */
    public function GetConts($Doc_Org = "Doc")
    {
        $db = new Db();

        if ($Doc_Org == "Doc")
            $this->ContArray = $db->rows("SELECT * FROM view_kontakty_dogovora WHERE kod_dogovora=" . $this->kod_dogovora);
        else
            $this->ContArray = $db->rows("SELECT * FROM kontakty WHERE kod_org=" . $this->kod_org);

        return $db->cnt;
    }
    //------------------------------------------------------------------------
    /**
     * Форма со списком контактов по Договору или Организации
     * @param int $AddPh
     * @param string $Doc_Org - поиск по Организации или Договору
     * @return string
     */
    public function Contacts($AddPh = 0, $Doc_Org = "Doc")
    {
        // Формируем массив контактов
        $cnt = $this->GetConts($Doc_Org);

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        //$res .= '<tr bgcolor="#CCCCCC" ><td width="200">Контакты</td></tr>';

        // Если можно добалять телефон то Разрешено "Добавить контакт из списка"
        if ($AddPh !== 0 and $Doc_Org == "Doc") {
            $res .= '<tr bgcolor="#CCCCCC"><td>';
            $res .= '<br>' . $this->SelListForm(); // Список выбора по организации
            $res .= '</td></tr>';
        }

        if($cnt==0)
            return $this->SelListForm();

        $exc = array();

        for ($i = 0; $i < $cnt; $i++) {

            $row = $this->ContArray[$i]; // Строка данных

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {

                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                // Добавляем должность, фамилию, имя и отчество
                $res .= "<tr><td><a href='form_cont.php?kod_kontakta=" . $row['kod_kontakta'] . "' >" . $row['dolg'] . "<br>" . $row['famil'] . " " . $row['name'] . " " . $row['otch'] . "</a>";

                // Если флаг - Добавить телефон
                $res .= $this->Phones($row['kod_kontakta'], $AddPh); // Форма добавления телефона

                $res .= '</td></tr>';
            }
        }

        $res .= '</table>';
        return $res;
    }
    //-----------------------------------------------------------------
    // Телефоны контакта
    /**
     * формирование списка телефонов контакта
     * @param int $kod_kontakta
     * @param int $Add
     * @return string
     */
    public function Phones($kod_kontakta=-1, $Add = 0)
    {
        if($kod_kontakta==-1)
            $kod_kontakta=$this->kod_kontakta;

        $db = new Db();

        $rows = $db->rows("SELECT * FROM kontakty WHERE kod_kontakta=" . $kod_kontakta);
        if(count($rows)==0)
            return "";
        $kontakt_data = $rows[0];

        $rows = $db->rows("SELECT * FROM kontakty_data WHERE kod_kontakta=" . $kod_kontakta);

        // Формируем таблицу телефонов/адресов/...
        $res = '<table border=0 cellspacing=0 cellpadding=0>';

        for ($i = 0; $i < $db->cnt; $i++) {

            // Строка данных
            $row = $rows[$i];

            // Если это e-mail
            if (strpos($row['data'], '@')) {
                $res .= '<tr>
                    <td>
                    <form action="mailto:' . $row['data'] . '?subject=НВС -&body=Добрый день, ' . $kontakt_data['name'] . ' ' . $kontakt_data['otch'] . '!" method="post" enctype="text/plain">'
                    . $row['data'] .
                    ' <input type="submit" value="E-MAIL" />
                    </form></td></tr>';
            } else
                $res .= '<tr>
                    <td>' . $row['data'] . '</td>
                    </tr>';
        }

        $res .= '</table>';

        if ($Add == 1)
            $res .= '<br><form id="form1" name="form1" method="post" action="">
           <input type="text" name="Numb" id="Numb" />
           <input type="checkbox" name="FaxTrue" id="FaxTrue" /> Факс
           <input type="hidden" name="kod_kontakta" value="' . $kod_kontakta . '" />
           <input type="hidden" name="AddPhone" value="AddPhone" />
           <input type="submit" name="Добавить" id="button" value="Добавить" />
           </form>';

        return $res;
    }
    //--------------------------------------------------------------------
    //
    /**
     * Добавить контакт в Договор и Организацию
     * @param $Dolg
     * @param $SName
     * @param $Name
     * @param $PName
     * @return void
     */
    public function AddContToDoc($Dolg, $SName, $Name, $PName)
    {
        $db = new Db();

        $DocID = $this->kod_dogovora;
        $OrgID = $this->kod_org;

        if (!isset($FName) and !isset($Name)) return;

        $Dolg = Func::Mstr($Dolg);
        $SName = Func::Mstr($SName);
        $Name = Func::Mstr($Name);
        $PName = Func::Mstr($PName);

        $db->query("INSERT INTO kontakty (kod_org,dolg,famil,name,otch)
        VALUES ($OrgID,'$Dolg','$SName','$Name','$PName')");
        $db->query("INSERT INTO kontakty_dogovora (kod_kontakta,kod_dogovora)
        VALUES(LAST_INSERT_ID(),$DocID)");

    }
    //-----------------------------------------------------------------
    //
    /**
     * Добавить контакт в Организацию
     * @param $Dolg
     * @param $SName
     * @param $Name
     * @param $PName
     * @return void
     * @internal param string $Prim
     */
    public function AddContToOrg($Dolg, $SName, $Name, $PName)
    {
        $db = new Db();

        $OrgID = $this->kod_org;

        if (!isset($FName) and !isset($Name)) return;

        $Dolg = Func::Mstr($Dolg);
        $SName = Func::Mstr($SName);
        $Name = Func::Mstr($Name);
        $PName = Func::Mstr($PName);

        $db->query("INSERT INTO kontakty (kod_org,dolg,famil,name,otch)
        VALUES ($OrgID,'$Dolg','$SName','$Name','$PName')");

    }
    //-----------------------------------------------------------------
    //
    /**
     * Добавить телефон
     * @param $Numb
     * @return void
     */
    public function AddPhone($Numb)
    {
        $db = new Db();
        $kod_kontakta = $this->kod_kontakta;

        if (!isset($Numb)) return;

        $db->query("INSERT INTO kontakty_data (kod_kontakta,data)
                    VALUES($kod_kontakta,'$Numb')");
    }
    //-----------------------------------------------------------------
    //
    /**
     * Загрузка данных. Проверить необходимость!
     * @param $kod_kontakta
     */
    public function Set($kod_kontakta)
    {
        $db = new Db();
        $this->kod_kontakta = $kod_kontakta;

        $rows = $db->rows("SELECT * FROM kontakty WHERE kod_kontakta=" . $this->kod_kontakta);

        $row = $rows[0];

        $this->Name = "<a href='form_cont.php?kod_kontakta=" . $row['kod_kontakta'] . " '>" . $row['dolg'] . "<br>" . $row['famil'] . " " . $row['name'] . " " . $row['otch'] . "</a>";
        $this->Data = $row;

        if (isset($row['kod_org'])) {
            $this->kod_org = $row['kod_org'];
        } else {
            $this->kod_org = 0;
            $this->OrgName = '';
        }

    }
    //------------------------------------------------------------------------
    //
    /**
     * Выпадающий Список контактов по организации
     * @return string
     */
    public function SelList()
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM kontakty WHERE kod_org=" . $this->kod_org);

        $cnt = $db->cnt; // количество записей

        if ($cnt == 0) return ''; // если нет записей

        // Формируем компонет - список
        $res = "<select name='SLContID' id='SLContID'>";

        $exc = array();

        // Формируем элементы списка
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {
                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                $res .= '<option value="' . $row['kod_kontakta'] . '">' .
                    Func::Mstr($row['dolg']) . ' ' . Func::Mstr($row['famil']) . ' ' .
                    Func::Mstr($row['name']) . ' ' .
                    Func::Mstr($row['otch']) . ' '
                    . '</option>';
                array_push($exc, $row['famil']);
            }
        }

        $res .= '</select>';

        $res .= "<select name='Status' id='Status'>
                <option value='2' selected='selected'>По Договору</option>
                <option value='4'>По Отгрузке</option>
                <option value='1'>Подписант</option>
                <option value='3'>По Финансированию</option>
                </select>";

        return $res;
    }
    //------------------------------------------------------------------------
    //
    /**
     * Добавление контакта в договор (из Sel List)
     * @param $DocID
     */
    public function AddKontaktToDoc($DocID)
    {
        $db = new Db();
        $db->query("INSERT INTO kontakty_dogovora (kod_kontakta,kod_dogovora) VALUES($this->kod_kontakta,$DocID)");
    }

    //------------------------------------------------------------------------
    //
    /**
     * Создает форму со списком
     * @param string $Action
     * @return string
     */
    public function SelListForm($Action = '')
    {
        $sl = $this->SelList();

        if ($sl == '') return '';

        $res = "<form id='form1' name='form1' method='post' action='$Action' >"
            . $sl .
            "<input type='hidden' name='AddContFromList' id='AddContFromList' />" .
            "<input type='submit' name='button' id='button' value='Добавить из списка' />
                </form>";
        return $res;
    }

    //------------------------------------------------------------------------
    // Договоры контакта. Передалать в class_doc!
    /**
     *
     */
    public function ShowDocs()
    {
        return Doc::getDocsByKontakt($this->kod_kontakta);
    }
    //------------------------------------------------------------------------
    // Save
    /**
     * Обновление данных контакта
     * @param $Dolg
     * @param $FName
     * @param $Name
     * @param $SName
     */
    public function Save($Dolg, $FName, $Name, $SName)
    {
        $db = new Db();
        // Не обновляется код организации
        $db->query("UPDATE kontakty SET dolg = '$Dolg', famil = '$FName', name = '$Name', otch = '$SName' WHERE kod_kontakta =$this->kod_kontakta");
    }
    //------------------------------------------------------------------------
    // Save Form
    public function SaveForm()
    {
        $db = new Db();
        $rows = $db->rows("SELECT * FROM kontakty WHERE kod_kontakta=$this->kod_kontakta");

        $row = $rows[0];

        $res = '<form id="form1" name="form1" method="post" action=""><table width="290" border="1">
          <tr>
            <td width="78">Должность</td>
            <td width="256">
              <label>
                <input name="Dolg" type="text" id="Dolg" size="35" value="' . $row['dolg'] . '" />
              </label>
            </td>
          </tr>
          <tr>
            <td>Фамилия</td>
            <td><input name="FName" type="text" id="FName" size="35" value="' . $row['famil'] . '" /></td>
          </tr>
          <tr>
            <td>Имя</td>
            <td><input name="Name" type="text" id="Name" size="35" value="' . $row['name'] . '" /></td>
          </tr>
          <tr>
            <td>Отчество</td>
            <td><input name="SName" type="text" id="SName" size="35" value="' . $row['otch'] . '" /></td>
          </tr>
        </table>
          <p>
            <label>
              <input type="submit" name="Save" id="Save" value="Сохранить" />
              <input type="hidden" name="SaveContForm" id="SaveContForm" />
            </label>
          </p>
        </form>';
        return $res;
    }

//------------------------------------------------------------------------
    /**
     * Список всех Контактов с телефонами / организацией
     * @return string
     */
    public function All()
    {
        $db = new Db();

        $rows = $db->rows("SELECT
                                kontakty.kod_kontakta,
                                kontakty.kod_org,
                                kontakty.dolg,
                                kontakty.famil,
                                kontakty.`name`,
                                kontakty.otch,
                                org.nazv_krat
                            FROM
                                kontakty
                            INNER JOIN org ON kontakty.kod_org = org.kod_org
                            ORDER BY
                                kontakty.famil ASC");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC" >
                        <td width="200">Фамилия Имя Отчество</td>
                        <td width="200">Организация</td>
                        <td width="200">Должность</td>
                        <td width="200">Контакты</td>
                    </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];


            $res.= '<tr>
                            <td><a href="form_cont.php?kod_kontakta=' . $row['kod_kontakta'] . '">' . Func::Mstr($row['famil']) .
                ' ' . Func::Mstr($row['name']) .
                ' ' . Func::Mstr($row['otch']) . '</a></td>
                            <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                            <td>' . Func::Mstr($row['dolg']) . '</td>
                            <td>' . $this->Phones($row['kod_kontakta']) . '</td>
                 </tr>';
        }

        $res.= '</table>';

        return $res;
    }
//------------------------------------------------------------------------
}