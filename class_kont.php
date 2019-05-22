<?php
include_once "class_db.php";
include_once "class_func.php";

class Kontakt
{
    public $kod_dogovora = 0; // Код Договора
    public $kod_org = 0; // Код Организации
    public $kod_kontakta = 0; // Код Контакта
    public $Data;
    public $Name;
    public $OrgName;
    public $KontArray;// Массив контактов по Договору
    private $max_str_length = 30; // Максимальная длина строки

//------------------------------------------------------------------------

    /**
     * Формирует массив контактов договора или организации
     * @param string $Doc_Org -
     * @return int
     */
    public function getKontArray($Doc_Org = "Doc")
    {
        $db = new Db();

        if ($Doc_Org == "Doc")
            $this->KontArray = $db->rows(/** @lang MySQL */
                "SELECT * FROM view_kontakty_dogovora WHERE kod_dogovora=$this->kod_dogovora ORDER BY kod_kontakta DESC");
        else
            $this->KontArray = $db->rows(/** @lang MySQL */
                "SELECT * FROM kontakty WHERE kod_org=$this->kod_org AND del=0  ORDER BY kod_kontakta DESC");

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
        $cnt = $this->getKontArray($Doc_Org);

        $btn_add = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Добавить', 'AddKontakt');

        $res = "<div class='btn'>
                    <div><b>Контакты</b></div>
                    <div>$btn_add</div>
                </div>";

        if ($cnt == 0) // если нет контактов то возвращаем список
        {
            // todo: Информировать, что контакт не выбран
            return $res.$this->formSelList();
        }

        $res .= '<table border=1 cellspacing=0 cellpadding=0 width="100%">';
        // Если можно добалять телефон то Разрешено "Добавить контакт из списка"
        if ($AddPh !== 0 and $Doc_Org == "Doc") {
            $res .= '<tr bgcolor="#CCCCCC"><td>';
            $res .= $this->formSelList(); // Список выбора по организации
            $res .= '</td></tr>';
        }

        $exc = array();

        for ($i = 0; $i < $cnt; $i++) {

            $row = $this->KontArray[$i]; // Строка данных

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {

                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                $del_btn = '';
                if ($Doc_Org == 'Doc')
                    $del_btn = func::ActButton2('', 'Удалить', "DelKonaktDog", "kod_kont_dog_del", $row['kod_kont_dog']);

                $dolg = "";
                if ($row['dolg'] != "")
                    $dolg = $row['dolg'] . "<br>";

                // Добавляем должность, фамилию, имя и отчество
                $res .= /** @lang HTML */
                    "<tr>
                        <td>
                            <div class='btn'><div><a href='form_kont.php?kod_kontakta=" . $row['kod_kontakta'] . "' >" . $dolg . $row['famil'] . " " . $row['name'] . " " . $row['otch'] . "</a></div><div>$del_btn</div></div>";

                // Если флаг - Добавить телефон
                $res .= $this->formPhones($row['kod_kontakta'], $AddPh) . '</td></tr>';
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
     * @param int $Del
     * @return string
     */
    public function formPhones($kod_kontakta = -1, $Add = 0, $Del = 0)
    {
        if ($kod_kontakta == -1)
            $kod_kontakta = $this->kod_kontakta;

        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM kontakty WHERE kod_kontakta=$kod_kontakta AND del=0");
        if (count($rows) == 0)
            return "";
        $kontakt_data = $rows[0];

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM kontakty_data WHERE kod_kontakta=$kod_kontakta AND del=0");

        // Формируем таблицу телефонов/адресов/...
        $res = '<table border=0 cellspacing=0 cellpadding=0>';

        for ($i = 0; $i < $db->cnt; $i++) {

            // Строка данных
            $row = $rows[$i];

            // Если это e-mail
            if (strpos($row['data'], '@')) {

                $dogovor = "";
                if(isset($_GET['kod_dogovora']))
                {
                    $d = new Doc();
                    $d->getData((int)$_GET['kod_dogovora']);
                    $nomer = $d->Data['nomer'];
                    $data_sost = func::Date_from_MySQL($d->Data['data_sost']);

                    $type = "Счет";
                    if((int)$d->Data['doc_type'] > 1) {
                        $nomer = $d->Data['kod_dogovora'];
                        switch ((int)$d->Data['doc_type']) {
                            case 2:
                                $type = "Подтверждение";
                                break;
                            case 3:
                                $type = "Заказ";
                                break;
                            case 4:
                                $type = "Предложение";
                                break;
                            case 5:
                                $type = "Запрос";
                                break;
                        }
                    }

                    $dogovor = "$type №$nomer от $data_sost";
                }

                $res .= '<tr>
                            <td>
                                <a href="mailto:' . $row['data'] . '?subject=НВС - '.$dogovor.'&body=Добрый день, ' . $kontakt_data['name'] . ' ' . $kontakt_data['otch'] . '!">'
                    . $row['data'] .
                    '</a>
                            </td>';
            } else
                $res .= '<tr>
                    <td>' . $row['data'] . '</td>';

            if ($Del == 1)
                $res .= '<td>' . func::ActButton2('', 'Удалить', "DelData", "kod_dat_del", $row['kod_dat']) . '</td>';

            $res .= '</tr>';
        }

        $res .= '</table>';

        $btn = "";
        if ($Add == 1)
            $btn = func::btnImage("Добавить");
        $res .= '<form name="form1" method="post" action="">
                           <input name="phone" />
                           <input type="hidden" name="kod_kontakta" value="' . $kod_kontakta . '" />
                           <input type="hidden" name="formPhones" value="formPhones" />
                           ' . $btn . '
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
        if($kod_org == 0) // Если не задан код организации то берем из договора
        {
            $D = new Doc();
            $D->kod_dogovora = $kod_dogovora;
            $D->getData();
            if($D->Data['kod_ispolnit']==config::$kod_org_main) // Если исполнитель - основная компания
                $kod_org = $D->Data['kod_org'];
            else
                $kod_org = $D->Data['kod_ispolnit'];
        }

        if (!isset($FName) and !isset($name)) return;

        $db = new Db();
        $dolg = $db->real_escape_string(Func::Mstr($dolg));
        $famil = $db->real_escape_string(Func::Mstr($famil));
        $name = $db->real_escape_string(Func::Mstr($name));
        $otch = $db->real_escape_string(Func::Mstr($otch));
        $kod_user = func::kod_user();
        $db->query(/** @lang MySQL */
            "INSERT INTO 
                      kontakty (kod_org,dolg,famil,name,otch,kod_user)
                    VALUES ($kod_org,'$dolg','$famil','$name','$otch',$kod_user)");
        $last_id = $db->last_id;

        if ($kod_dogovora > 0)
            $db->query(/** @lang MySQL */
                "INSERT INTO 
                          kontakty_dogovora (kod_kontakta,kod_dogovora,kod_user)
                        VALUES($last_id,$kod_dogovora,$kod_user)");
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

        if (!isset($phone) or $phone == "") return;
        $kod_user = func::kod_user();
        $phone = $db->real_escape_string($phone);
        $db->query(/** @lang MySQL */
            "INSERT INTO kontakty_data (kod_kontakta,data,kod_user)
                    VALUES($kod_kontakta,'$phone',$kod_user)");
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Загрузка данных. todo - Проверить необходимость!
     * @param int $kod_kontakta
     */
    public function getData($kod_kontakta)
    {
        $db = new Db();
        $this->kod_kontakta = $kod_kontakta;

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM kontakty WHERE kod_kontakta=$this->kod_kontakta  AND del=0");

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
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Выпадающий Список контактов по организации
     * @return string
     */
    public function formSelList()
    {
        $db = new Db();

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM kontakty WHERE kod_org=$this->kod_org  AND del=0 ORDER BY kod_kontakta DESC");

        $cnt = $db->cnt; // количество записей

        if ($cnt == 0) return ''; // если нет записей

        // Формируем компонет - список
        $res = /** @lang HTML */
            "<form name='form1' method='post' action='' >
                <select name='kod_kontakta' id='kod_kontakta'>";

        $exc = array(); // Массив ФИО, чтобы не было повторений

        // Формируем элементы списка
        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];

            // Только оригинальные имена
            if (!in_array($row['famil'] . $row['name'] . $row['otch'], $exc)) {

                $dolg = func::clearString($row['dolg']);
                if(strlen($dolg)>$this->max_str_length) // Если должность длиннее максимальной строки
                    $dolg = mb_substr($dolg,0,$this->max_str_length,'UTF-8')."...";

                array_push($exc, $row['famil'] . $row['name'] . $row['otch']);

                $res .= /** @lang HTML */
                    '<option value="' . $row['kod_kontakta'] . '">' .
                    Func::Mstr($row['famil']) . ' ' .
                    Func::Mstr($row['name']) . ' ' .
                    Func::Mstr($row['otch']) . ' - ' .
                    $dolg
                    . '</option>';
            }
        }
        $res .= /** @lang HTML */
            "</select>";

        $res .= /** @lang HTML */
            "<select name='Status' id='Status'>
                    <option value='2'>По Договору</option>
                    <option value='4'>По Отгрузке</option>
                    <option value='1'>Подписант</option>
                    <option value='3'>По Финансированию</option>
                 </select>";

        $res .= /** @lang HTML */
            "<input type='hidden' name='formSelList' id='formSelList' />
             <input alt='Add' type='image' src='img/add.png' name='button' id='button' value='Добавить из списка' />";

        $res .= /** @lang HTML */
            "</form>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Добавление контакта в договор (из Sel List)
     * @param int $kod_dogovora
     */
    public function AddKontaktToDoc($kod_dogovora)
    {
        $db = new Db();
        $kod_user = func::kod_user();
        $kod_dogovora = (int)$kod_dogovora;

        $db->query(/** @lang MySQL */
            "INSERT INTO kontakty_dogovora (kod_kontakta,kod_dogovora,kod_user) VALUES($this->kod_kontakta,$kod_dogovora,$kod_user)");
    }

//----------------------------------------------------------------------------------------------------------------------
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
        $kod_user = func::kod_user();

        // Не обновляется код организации
        $db->query(/** @lang MySQL */
            "UPDATE kontakty SET dolg = '$dolg', famil = '$famil', name = '$name', otch = '$otch',kod_user=$kod_user WHERE kod_kontakta =$this->kod_kontakta");
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Форма - добавления и редактирования
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit = 0)
    {

        $dolg = "";
        $famil = "";
        $name = "";
        $otch = "";
        $form_name = "formAdd";

        if ($Edit == 1) {
            $db = new Db();
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM kontakty WHERE kod_kontakta=$this->kod_kontakta AND del=0");

            $row = $rows[0];

            $dolg = $row['dolg'];
            $famil = $row['famil'];
            $name = $row['name'];
            $otch = $row['otch'];
            $form_name = "formEdit";
        }

        $res = /** @lang HTML */
            "<form name=\"form1\" method=\"post\" action=\"\">
             <table width=\"290\" border=\"0\">
              <tr>
                <td width=\"78\">Должность</td>
                <td width=\"256\">
                  <label>
                    <input name=\"dolg\" id=\"dolg\" size=\"35\" value=\"$dolg\" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Фамилия</td>
                <td><input name=\"famil\" id=\"famil\" size=\"35\" value=\"$famil\" /></td>
              </tr>
              <tr>
                <td>Имя</td>
                <td><input name=\"name\" id=\"name\" size=\"35\" value=\"$name\" /></td>
              </tr>
              <tr>
                <td>Отчество</td>
                <td><input name=\"otch\" id=\"otch\" size=\"35\" value=\"$otch\" /></td>
              </tr>
            </table>
                <label>
                  <input type=\"submit\" name=\"Save\" id=\"Save\" value=\"Сохранить\" />
                  <input type=\"hidden\" name=\"$form_name\" id=\"$form_name\" value=\"$form_name\" />
                </label>
            </form>";

        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Список всех Контактов с телефонами / организацией
     * @param string $query - Запрос на выборку строк из БД с контатами, должен содержать (kod_kontakta,kod_org,dolg,famil,name,otch,nazv_krat)
     * @return string
     */
    public function formAllKontats($query = "")
    {
        $db = new Db();
        if ($query == "")
            $query = /** @lang MySQL */
                "SELECT
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
                                WHERE kontakty.del=0
                                ORDER BY
                                    kontakty.famil;";

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


            $res .= /** @lang HTML */
                '<tr>
                            <td><a href="form_kont.php?kod_kontakta=' . $row['kod_kontakta'] . '">' . Func::Mstr($row['famil']) .
                ' ' . Func::Mstr($row['name']) .
                ' ' . Func::Mstr($row['otch']) . '</a></td>
                            <td>' . $this->formPhones($row['kod_kontakta']) . '</td>
                            <td><a href="form_org.php?kod_org=' . $row['kod_org'] . '">' . $row['nazv_krat'] . '</a></td>
                            <td>' . Func::Mstr($row['dolg']) . '</td>
                 </tr>';
        }

        $res .= '</table>';

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
            if (isset($_POST['kod_kontakta'])) {
                $this->getData($_POST['kod_kontakta']);
                $this->AddKontaktToDoc($this->kod_dogovora);
                $event = true;
            }

        if (isset($_POST['formEdit']))
            if (isset($_POST['famil'], $_POST['name'])) {
                $this->Save($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
                $event = true;
            }

        if (isset($_POST['formAdd']))
            if (isset($_POST['famil'], $_POST['name'])) {
                $this->AddKontakt($_POST['dolg'], $_POST['famil'], $_POST['name'], $_POST['otch']);
                $event = true;
            }

        if (isset($_POST['kod_dat_del'])) {
            $this->DelData($_POST['kod_dat_del']);
            $event = true;
        }

        if (isset($_POST['Flag'])) {
            if ($_POST['Flag'] == "DelKontakt" and isset($_POST['kod_kontakta_del'])) {
                $this->DelKonakt($_POST['kod_kontakta_del']);
                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/form_org.php?kod_org=' . $this->kod_org); // todo - поправить переадресацию, если не корень то не работает
            }

            if ($_POST['Flag'] == 'DelKonaktDog' and isset($_POST['kod_kont_dog_del'])) {
                $this->DelKonaktDog($_POST['kod_kont_dog_del']);
                $event = true;
            }
        }

        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление Контакта
     * @param int $kod_kontakta
     */
    public function DelKonakt($kod_kontakta = -1)
    {
        $db = new Db();

        if ($kod_kontakta < 0)
            $kod_kontakta = $this->kod_kontakta;
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE kontakty SET del=1,kod_user=$kod_user WHERE kod_kontakta=$kod_kontakta");
        $db->query(/** @lang MySQL */
            "UPDATE kontakty_data SET del=1,kod_user=$kod_user WHERE kod_kontakta=$kod_kontakta");
        $db->query(/** @lang MySQL */
            "UPDATE kontakty_dogovora SET del=1,kod_user=$kod_user WHERE kod_kontakta=$kod_kontakta");

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление Контакта из договора
     * @param int $kod_kont_dog
     */
    public function DelKonaktDog($kod_kont_dog)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE kontakty_dogovora SET del=1,kod_user=$kod_user WHERE kod_kont_dog=$kod_kont_dog");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление Данных
     * @param int $kod_dat
     */
    public function DelData($kod_dat)
    {
        $db = new Db();
        $kod_user = func::kod_user();

        $db->query(/** @lang MySQL */
            "UPDATE kontakty_data SET del=1,kod_user=$kod_user WHERE kod_dat=$kod_dat");
    }
//----------------------------------------------------------------------------------------------------------------------
//


}