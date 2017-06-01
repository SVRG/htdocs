<?php
include_once "class_db.php";

class Kontakt
{
    public $kod_dogovora = 0; // Код Договора
    public $kod_org = 0; // Код Организации
    public $kod_kontakta = 0; // Код Контакта
    public $Data;
    public $Name;
    public $OrgName;
    public $KontArray;// Массив контактов по Договору

    /**
     * Kontakt constructor.
     */
    public function __construct()
    {

    }

    //------------------------------------------------------------------------
    /**
     * Формирует массив контактов договора или организации
     * @param string $Doc_Org -
     * @return int
     */
    public function getData($Doc_Org = "Doc")
    {
        $db = new Db();

        if ($Doc_Org == "Doc")
            $this->KontArray = $db->rows("SELECT * FROM view_kontakty_dogovora WHERE kod_dogovora=$this->kod_dogovora");
        else
            $this->KontArray = $db->rows("SELECT * FROM kontakty WHERE kod_org=$this->kod_org");

        return $db->cnt;
    }
    //------------------------------------------------------------------------
    /**
     * Форма со списком контактов по Договору или Организации
     * @param int $AddPh - 0 не добавлять телефон
     * @param string $Doc_Org - поиск по Организации или Договору
     * @return string
     */
    public function formKontakts($AddPh = 0, $Doc_Org = "Doc")
    {
        // Формируем массив контактов
        $cnt = $this->getData($Doc_Org);

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        //$res .= '<tr bgcolor="#CCCCCC" ><td width="200">Контакты</td></tr>';

        // Если можно добалять телефон то Разрешено "Добавить контакт из списка"
        if ($AddPh !== 0 and $Doc_Org == "Doc") {
            $res .= '<tr bgcolor="#CCCCCC"><td>';
            $res .= '<br>' . $this->formSelList(); // Список выбора по организации
            $res .= '</td></tr>';
        }

        if($cnt==0)
        {
            // todo: Информировать, что контакт не выбран
            return $this->formSelList();
        }

        $exc = array();

        for ($i = 0; $i < $cnt; $i++) {

            $row = $this->KontArray[$i]; // Строка данных

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {

                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                // Добавляем должность, фамилию, имя и отчество
                $res .= /** @lang HTML */
                    "<tr><td><a href='form_kont.php?kod_kontakta=" . $row['kod_kontakta'] . "' >" . $row['dolg'] . "<br>" . $row['famil'] . " " . $row['name'] . " " . $row['otch'] . "</a>";

                // Если флаг - Добавить телефон
                $res .= $this->formPhones($row['kod_kontakta'], $AddPh); // Форма добавления телефона

                $res .= '</td></tr>';
            }
        }

        $res .= '</table>';
        return $res;
    }
//-----------------------------------------------------------------
    /**
     * формирование списка телефонов контакта
     * @param int $kod_kontakta
     * @param int $Add
     * @return string
     */
    public function formPhones($kod_kontakta=-1, $Add = 0)
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
            $res .= '<form id="form1" name="form1" method="post" action="">
                           <input type="text" name="phone" id="phone" />
                           <input type="hidden" name="kod_kontakta" value="' . $kod_kontakta . '" />
                           <input type="hidden" name="formPhones" value="formPhones" />
                           <input type="submit" name="Добавить" id="button" value="Добавить" />
                     </form>';

        return $res;
    }
    //--------------------------------------------------------------------
    //
    /**
     * Добавить контакт в Договор и Организацию
     * @param string $dolg - Должность
     * @param string $famil - Фамилия
     * @param string $name - Имя
     * @param string $otch - Отчество
     * @return void
     */
    public function AddKontakt($dolg, $famil, $name, $otch)
    {

        $kod_dogovora = $this->kod_dogovora;
        $kod_org = $this->kod_org;

        if (!isset($FName) and !isset($name)) return;

        $dolg = Func::Mstr($dolg);
        $famil = Func::Mstr($famil);
        $name = Func::Mstr($name);
        $otch = Func::Mstr($otch);

        $db = new Db();
        $db->query(/** @lang SQL */
            "INSERT INTO 
                      kontakty (kod_org,dolg,famil,name,otch)
                    VALUES ($kod_org,'$dolg','$famil','$name','$otch')");

        if ($kod_dogovora > 0)
            $db->query(/** @lang SQL */
                "INSERT INTO 
                          kontakty_dogovora (kod_kontakta,kod_dogovora)
                        VALUES(LAST_INSERT_ID(),$kod_dogovora)");
    }
    //-----------------------------------------------------------------
    //
    /**
     * Добавить телефон
     * @param string $phone
     * @return void
     */
    public function AddPhone($phone)
    {
        $db = new Db();
        $kod_kontakta = $this->kod_kontakta;

        if (!isset($phone) or $phone=="") return;

        $db->query("INSERT INTO kontakty_data (kod_kontakta,data)
                    VALUES($kod_kontakta,'$phone')");
    }
    //-----------------------------------------------------------------
    //
    /**
     * Загрузка данных. Проверить необходимость!
     * @param int $kod_kontakta
     */
    public function Set($kod_kontakta)
    {
        $db = new Db();
        $this->kod_kontakta = $kod_kontakta;

        $rows = $db->rows(/** @lang SQL */
            "SELECT * FROM kontakty WHERE kod_kontakta=$this->kod_kontakta");

        $row = $rows[0];

        $this->Name = "<a href='form_kont.php?kod_kontakta=" . $row['kod_kontakta'] . " '>" . $row['dolg'] . "<br>" . $row['famil'] . " " . $row['name'] . " " . $row['otch'] . "</a>";
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
    public function formSelList()
    {
        $db = new Db();

        $rows = $db->rows("SELECT * FROM kontakty WHERE kod_org=" . $this->kod_org);

        $cnt = $db->cnt; // количество записей

        if ($cnt == 0) return ''; // если нет записей

        // Формируем компонет - список
        $res = /** @lang HTML */
            "<form id='form1' name='form1' method='post' action='' >
                <select name='kod_kontakta' id='kod_kontakta'>";

        $exc = array();

        // Формируем элементы списка
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {
                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                $res .= /** @lang HTML */
                    '<option value="' . $row['kod_kontakta'] . '">' .
                    Func::Mstr($row['dolg']) . ' ' . Func::Mstr($row['famil']) . ' ' .
                    Func::Mstr($row['name']) . ' ' .
                    Func::Mstr($row['otch']) . ' '
                    . '</option>';
            }
        }

        $res .= /** @lang HTML */
            "</select>
                    <select name='Status' id='Status'>
                    <option value='2' selected='selected'>По Договору</option>
                    <option value='4'>По Отгрузке</option>
                    <option value='1'>Подписант</option>
                    <option value='3'>По Финансированию</option>
                 </select>";

        $res.= /** @lang HTML */
                    "<input type='hidden' name='formSelList' id='formSelList' />
                    <br><input type='submit' name='button' id='button' value='Добавить из списка' />
                </form>";

        return $res;
    }
    //------------------------------------------------------------------------
    //
    /**
     * Добавление контакта в договор (из Sel List)
     * @param int $kod_dogovora
     */
    public function AddKontaktToDoc($kod_dogovora)
    {
        $db = new Db();
        $db->query("INSERT INTO kontakty_dogovora (kod_kontakta,kod_dogovora) VALUES($this->kod_kontakta,$kod_dogovora)");
    }

    //------------------------------------------------------------------------
    // Save
    /**
     * Обновление данных контакта
     * @param string $dolg
     * @param string $famil
     * @param string $name
     * @param string $otch
     */
    public function Save($dolg, $famil, $name, $otch)
    {
        $db = new Db();
        // Не обновляется код организации
        $db->query("UPDATE kontakty SET dolg = '$dolg', famil = '$famil', name = '$name', otch = '$otch' WHERE kod_kontakta =$this->kod_kontakta");
    }
    //------------------------------------------------------------------------
    //
    /**
     * Форма - добавления и редактирования
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit=0)
    {

        $dolg = "";
        $famil = "";
        $name = "";
        $otch = "";
        $form_name = "formAdd";

        if($Edit==1) {
            $db = new Db();
            $rows = $db->rows("SELECT * FROM kontakty WHERE kod_kontakta=$this->kod_kontakta");

            $row = $rows[0];

            $dolg = $row['dolg'];
            $famil = $row['famil'];
            $name = $row['name'];
            $otch = $row['otch'];
            $form_name = "formEdit";
        }

        $res = /** @lang HTML */
            "<form id=\"form1\" name=\"form1\" method=\"post\" action=\"\">
             <table width=\"290\" border=\"0\">
              <tr>
                <td width=\"78\">Должность</td>
                <td width=\"256\">
                  <label>
                    <input name=\"dolg\" type=\"text\" id=\"dolg\" size=\"35\" value=\"$dolg\" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Фамилия</td>
                <td><input name=\"famil\" type=\"text\" id=\"famil\" size=\"35\" value=\"$famil\" /></td>
              </tr>
              <tr>
                <td>Имя</td>
                <td><input name=\"name\" type=\"text\" id=\"name\" size=\"35\" value=\"$name\" /></td>
              </tr>
              <tr>
                <td>Отчество</td>
                <td><input name=\"otch\" type=\"text\" id=\"otch\" size=\"35\" value=\"$otch\" /></td>
              </tr>
            </table>
                <label>
                  <input type=\"submit\" name=\"Save\" id=\"Save\" value=\"Сохранить\" />
                  <input type=\"hidden\" name=\"$form_name\" id=\"$form_name\" value=\"$form_name\" />
                </label>
            </form>";

        return $res;
    }

//------------------------------------------------------------------------

    /**
     * Список всех Контактов с телефонами / организацией
     * @param string $query - Запрос на выборку строк из БД с контатами, должен содержать (kod_kontakta,kod_org,dolg,famil,name,otch,nazv_krat)
     * @return string
     */
    public function formAllKontats($query="")
    {
        $db = new Db();
        if($query=="")
            $query =           "SELECT
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
                                    kontakty.famil ASC";

        $rows = $db->rows($query);

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $res = /** @lang HTML */
            '<table border=1 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="#CCCCCC" >
                        <td width="200">Фамилия Имя Отчество</td>
                        <td width="200">Организация</td>
                        <td width="200">Должность</td>
                        <td width="200">Контакты</td>
                    </tr>';

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];


            $res.= /** @lang HTML */
                '<tr>
                            <td><a href="form_kont.php?kod_kontakta=' . $row['kod_kontakta'] . '">' . Func::Mstr($row['famil']) .
                ' ' . Func::Mstr($row['name']) .
                ' ' . Func::Mstr($row['otch']) . '</a></td>
                            <td>' . $this->formPhones($row['kod_kontakta']) . '</td>
                            <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                            <td>' . Func::Mstr($row['dolg']) . '</td>
                 </tr>';
        }

        $res.= '</table>';

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Перехватчик событий
     * Добавление телефона в контакт
     * Добавление контакта из списка
     *
     */
    public function Events()
    {
        $event = false;

        if (isset($_POST['formPhones']))
            if (isset($_POST['kod_kontakta']) and isset($_POST['phone'])) {
                $this->kod_kontakta = $_POST['kod_kontakta'];
                $this->AddPhone($_POST['phone']);
                $event = true;
            }

        if (isset($_POST['formSelList']))
            if(isset($_POST['kod_kontakta'])){
                $this->Set($_POST['kod_kontakta']);
                $this->AddKontaktToDoc($this->kod_dogovora);
                $event = true;
        }

        if (isset($_POST['formEdit']))
            if(isset($_POST['famil'], $_POST['name']))
            {
            $this->Save($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
            $event = true;
            }

        if (isset($_POST['formAdd']))
            if(isset($_POST['famil'], $_POST['name']))
            {
            $this->AddKontakt($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
            $event = true;
            }

        if(isset($_POST['Flag']))
        if($_POST['Flag']=="DelKontakt" and isset($_POST['kod_kontakta']))
        {
            $this->DelKonakt($_POST['kod_kontakta']); // todo - придумать защиту от случайного удаления
            header('Location: /form_org.php?kod_org='.$this->kod_org);
        }

        if($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление Контакта
     * @param int $kod_kontakta
     */
    public function DelKonakt($kod_kontakta=-1)
    {
        $db = new Db();

        if ($kod_kontakta<0)
            $kod_kontakta = $this->kod_kontakta;

        $db->query("DELETE FROM kontakty WHERE kod_kontakta=$kod_kontakta");
        $db->query("DELETE FROM kontakty_data WHERE kod_kontakta=$kod_kontakta");
        $db->query("DELETE FROM kontakty_dogovora WHERE kod_kontakta=$kod_kontakta");

    }
//----------------------------------------------------------------------------------------------------------------------


}