<?php
include_once "class_func.php";
include_once "class_db.php";
include_once "class_docum.php";
include_once "class_org.php";

class Elem
{
    public $kod_elem;
    public $Mod = '';
    public $Data;       // Данные полей записи

//----------------------------------------------------------------------
// Ссылка на форму
    /**
     * @param string $name
     * @param int $kod_elem
     * @return string
     */
    public function getFormLink($name = '', $kod_elem = -1)
    {
        if ($name == '' or $kod_elem == -1) {
            $kod_elem = $this->kod_elem;
            $this->getData($this->kod_elem);
            $name = $this->Data['name'];
        }

        return "<a href='form_elem.php?kod_elem=$kod_elem'>$name</a>";
    }
//----------------------------------------------------------------------
// Запрос данных
    /**
     * @param int $kod_elem
     * @return array
     */
    public function getData($kod_elem = 0)
    {
        if ($kod_elem != 0)
            $this->kod_elem = $kod_elem;

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM elem WHERE kod_elem=$this->kod_elem AND del=0");
        $this->Data = $rows[0];

        return $this->Data;
    }

//----------------------------------------------------------------------
// Наименование Элемента
    /**
     * @param string $field
     * @param int $Link
     * @return string
     */
    public function formName($field = 'name', $Link = 1) //
    {
        $db = new Db();
        $rows = $db->rows(/** @lang SQL */
            "SELECT * FROM elem WHERE kod_elem=$this->kod_elem");

        if ($db->cnt == 0)
            return "Элемент не найден";

        $res = "";
        $btn_edit = "";
        if ($_SESSION['MM_UserGroup'] == "admin") {
            $btn_edit = Func::ActButton('', 'Изменить', 'formAddEdit');
            if (isset($_POST['Flag']))
                if ($_POST['Flag'] == 'formAddEdit') {
                    return $this->formAddEdit(1);
                }
        }

        $row = $rows[0];

        if ($field == 'shablon' and $row['shablon'] != "")
            $res .= str_replace('[Mod]', $this->Mod, $row['shablon']);
        elseif ($field == "all") {
            $name = $row['name'];
            $obozn = $row['obozn'];
            $shifr = $obozn;

            if ($row['shifr'] != "")
                $shifr = $row['shifr'];

            if ($name == $row['shifr'])
                $name = "";

            if ($name !== "" and $obozn !== "") {
                if (strpos($row['name'], $obozn) !== false)
                    $obozn = "";
            }

            $res .= /** @lang HTML */
                "<div class='btn'><div><b>$shifr</b></div><div>$btn_edit</div></div>$name $obozn";
        } else
            $res = $row[$field];

        if ($Link == 1)
            return '<a href="form_elem.php?kod_elem=' . $this->kod_elem . '">' . $res . '</a>';

        return $res;
    }

//----------------------------------------------------------------------
//
    /**
     * Вывод списка элементов, которые можно поставлять + остальные
     *
     */
    public function formNomen()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                                    view_elem.name AS elem_name,
                                    view_elem.kod_elem,
                                    view_elem.shifr,
                                    view_elem.nomen,
                                    photo.path,
                                    web_link
                                  FROM view_elem 
                                  LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                  GROUP BY view_elem.kod_elem,shifr
                                  ORDER BY shifr;");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        $all = false;
        if (isset($_GET['all']))
            $all = true;

        $res = /** @lang HTML */
            "<table border=1 cellspacing=0 width=\"70%\" rules=\"rows\" frame=\"void\">
                 <tr bgcolor=\"#CCCCCC\">
                     <td width=\"10%\">Фото</td>
                     <td width='70%' align=\"center\">Наименование <a href=\"form_nomen.php?all\">Показать все</a></td>
                     <td>Цены</td>
                 </tr>";

        $other = "";

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            $shifr = $row['shifr'];
            $img = "";
            $link = "form_elem.php?kod_elem=$kod_elem";
            $link_modif = "<a href='form_nomen.php?kod_elem=$kod_elem'><img alt='Modif' title='Посмотреть модификации' src='img/view_properties.png'></a>";

            $web_link = self::formWEBLink($row);

            if (isset($row['path'])) {
                $path = $row['path'];
                $img = "<a href='$link'><img alt='Path' src='$path' width='100' border='0' /></a>";
            }

            $this->kod_elem = $kod_elem;

            $name = "";
            if ($row['shifr'] != $row['elem_name'])
                $name = $row['elem_name'];

            $price_list = $this->formPriceList();

            $row_nomen = "<tr>
                            <td align='left' valign='top'>$img $link_modif $web_link</td>
                            <td valign='top'><a href='$link'><h1> $shifr </h1> $name </a></td>
                            <td valign='top'>$price_list</td>
                         </tr>";

            if ($row['nomen'] == 1)
                $res .= $row_nomen;
            elseif ($row['nomen'] == 0 and $all)
                $other .= $row_nomen;
        }

        if ($all) {
            $res .= '<tr bgcolor="#CCCCCC">
                     <td width="10%">Остальная номенклатура</td>
                     <td align="center"></td>
                    </tr>';
            $res .= $other;
        }

        $res .= '</table>';

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Договоры по Элементу
     * @return string
     * @throws Exception
     */
    public function formDocs()
    {
        return Doc::formDocByElem($this->kod_elem);
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка-выбора Элементов с автозаполнением
     * @return string
     */
    public function formSelList2()
    {
        $db = new Db();

        $sql = /** @lang MySQL */
            "SELECT * FROM view_elem WHERE nomen=1 ORDER BY shifr";

        $rows = $db->rows($sql);

        if ($db->cnt == 0)
            return "";

        $res = /** @lang HTML */
            "<select id='kod_elem' name='kod_elem' placeholder=\"Выбрать элемент...\">
                ";
        for ($i = 0; $i < $db->cnt; $i++) {
            $name = self::getSearchName($rows[$i]);
            $kod_elem = $rows[$i]['kod_elem'];

            $selected = "";
            if ($rows[$i]['kod_elem'] == $this->kod_elem)
                $selected = " selected='selected'";

            $res .= "<option value='$kod_elem' $selected>$name</option>\r\n";
        }
        $res .= /** @lang HTML */
            '</select>
            <script type="text/javascript">
                            let kod_elem, $kod_elem;
                            $kod_elem = $("#kod_elem").selectize({
                                onChange: function(value) {
                if (!value.length) return;
            }
                            });
                            kod_elem = $kod_elem[0].selectize;
            </script>';

        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Документы Элемента
     * @return string
     */
    public function formDocum()
    {
        return Docum::formDocum('Elem', $this->kod_elem);
    }
//------------------------------------------------------------------------
//
    /**
     * Фото Элемента
     * @return string
     */
    public function formPhoto()
    {
        $sql = /** @lang MySQL */
            "SELECT
                        docum.`name`,
                        docum.path
                    FROM
                        docum
                    INNER JOIN docum_elem ON docum_elem.kod_docum = docum.kod_docum 
                    WHERE docum.`name`='Фото' AND docum_elem.kod_elem=$this->kod_elem AND docum.del=0";
        $link = 'form_elem.php?kod_elem=' . $this->kod_elem;

        $db = new Db();
        $rows = $db->rows($sql);
        if ($db->cnt == 0)
            return "";

        $res = '';
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            if (file_exists($row['path']))
                $res .= ' <a href="' . $link . '"><img alt="Path" src="' . $row['path'] . '" width="100" border="0" /></a>';
        }
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Добавление элемента
     * @param string $obozn
     * @param string $name
     * @param string $shifr
     * @param int $nomen
     * @param string $shablon
     * @param string $web_link
     */
    public function AddElem($obozn, $name, $shifr, $nomen = 1, $shablon = '', $web_link = "")
    {
        if ($obozn === "" or $name === "")
            return;

        $obozn = ltrim($obozn);
        $name = ltrim($name);
        $kod_user = func::kod_user();
        $db = new Db();
        $obozn = $db->real_escape_string($obozn);
        $name = $db->real_escape_string($name);
        $shablon = $db->real_escape_string($shablon);
        $shifr = $db->real_escape_string($shifr);
        $nomen = (int)$nomen;

        $db->query(/** @lang MySQL */
            "INSERT INTO elem (obozn,name,nomen, shifr,shablon,kod_user,web_link) VALUES('$obozn','$name',$nomen,'$shifr','$shablon',$kod_user,'$web_link')");
    }
//------------------------------------------------------------------------
//
    /**
     * Сохранить изменения
     * @param string $obozn
     * @param string $name
     * @param string $shablon
     * @param string $shifr
     * @param string $web_link
     */
    public function Save($obozn = '', $name = '', $shablon = '', $shifr = '', $web_link = "")
    {
        $kod_elem = $this->kod_elem;

        if ($obozn == '' or $name == '')
            return;
        $kod_user = func::kod_user();

        Db::getHistoryString("elem", "kod_elem", $kod_elem);
        $db = new Db();
        $obozn = $db->real_escape_string($obozn);
        $name = $db->real_escape_string($name);
        $shablon = $db->real_escape_string($shablon);
        $shifr = $db->real_escape_string($shifr);
        $web_link = $db->real_escape_string($web_link);

        $db->query(/** @lang MySQL */
            "UPDATE elem SET obozn = '$obozn', name = '$name', shablon='$shablon', shifr='$shifr', web_link='$web_link', kod_user=$kod_user WHERE kod_elem = $kod_elem");
    }
//------------------------------------------------------------------------
//
    /**
     * Список Организаций которые покупали данный элемент
     * @return string
     */
    public function formOrgByElem()
    {
        $db = new Db();
        $kod_org_main = config::$kod_org_main;
        $kod_elem = $this->kod_elem;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM view_elem_org WHERE kod_org<>$kod_org_main AND kod_elem=$kod_elem");

        if ($db->cnt == 0)
            return "";

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">
		            <tr bgcolor="#CCCCCC">
		                <td>Название</td>
		                <td>Количество</td>
		            </tr>';

        $sum = 0;
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $nazv_krat = $row['nazv_krat'];
            $numb = func::Rub($row['numb'], 0);
            $kod_org = (int)$row['kod_org'];

            $org_link = "form_elem.php?kod_elem=$this->kod_elem&kod_org=" . $kod_org;

            $res .= "<tr>
                        <td><a href='$org_link'> $nazv_krat </a></td>
                        <td align='right'> $numb </td>
        		  	</tr>";
            $sum += (int)$row['numb'];
        }
        $res .= '<tr bgcolor="#CCCCCC">
                    <td align="right">Сумма</td>
                    <td align="right">' . func::Rub($sum, 0) . '</td>
                 </tr>';

        $res .= '</table>';
        return $res;
    }

//-----------------------------------------------------------------------------
//
    /**
     * Форма добавления / внесения изменений
     * @param int $Edit
     * @return string
     */
    public function formAddEdit($Edit = 0)
    {

        $shifr = "";
        $obozn = "";
        $name = "";
        $shablon = "";
        $FormName = "formAdd";
        $btn_nomen = "";
        $web_link = "";

        if ($Edit == 1) {
            $this->getData();
            $row = $this->Data;
            $shifr = $row['shifr'];
            $obozn = $row['obozn'];
            $name = $row['name'];
            $shablon = $row['shablon'];
            $web_link = $row['web_link'];
            $FormName = "formEdit";

            if (isset($_SESSION['MM_UserGroup'])) // todo - придумать глобальную политику прав
                if ($_SESSION['MM_UserGroup'] === "admin") {
                    if ($row['nomen'] == 1)
                        $btn_nomen = func::ActButton2('', 'Удалить из номенклатуры', "UnsetNomen", "kod_elem_set", $row['kod_elem']);
                    elseif ($row['nomen'] == 0)
                        $btn_nomen = func::ActButton2('', 'Добавить в номенклатуру', "SetNomen", "kod_elem_set", $row['kod_elem']);
                }
        }

        $res = $btn_nomen;
        $res .= '
            <form id="form1" name="form1" method="post" action="">
                  <table width="521" border="0">
                      <tr>
                        <td width="200">Шифр</td>
                        <td width="400">
                          <label>
                            <input name="shifr" id="shifr" value="' . $shifr . '" />
                          </label>
                        </td>
                    </tr>
                    <tr>
                        <td width="200">Обозначение (ДМ)*</td>
                        <td width="400">
                          <label>
                            <input name="obozn" id="obozn" value="' . $obozn . '" />
                            </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Наименование по ТУ*</td>
                        <td>
                          <label>
                          <textarea rows=7 cols=60 name="name" id="name">' . $name . '</textarea>  
                            </label>
                         </td>
                      </tr>
                        <tr>
                        <td>Шаблон Модификаций [Mod]</td>
                        <td>
                          <label>
                          <textarea rows=2 cols=60 name="shablon" id="shablon">' . $shablon . '</textarea>  
                            </label>
                         </td>
                      </tr>
                       <tr>
                        <td>WEB-Link</td>
                        <td>
                          <label>
                          <textarea rows=2 cols=60 name="web_link" id="web_link">' . $web_link . '</textarea>  
                            </label>
                         </td>
                      </tr>
                  </table>
                  <input id="' . $FormName . '" type="hidden" value="' . $FormName . '" name="' . $FormName . '"/>
                  <input type="submit" value="Сохранить" />
                  <input type=\'button\' value=\'Отмена\' onClick="document.location.href=\'form_nomen.php\'" />
                </form>';
        return $res;
    }
//------------------------------------------------------------------
//

    /**
     * Удаление элемента с заменой - чтоб не плодить одноразовые наименования
     * Заменяем указанный элемент в партиях на код Комплектующие=1001,
     * в модификации добавляем наименование+модификацию старого элемента
     * @param int $kod_elem - код удаляемого элемента
     * @param int $kod_dest - код элемента на который надо заменить
     */
    public function DeleteReplace($kod_elem, $kod_dest = 1001)
    {
        if (func::user_group() !== "admin") // todo - Придумать глобальные права
            return;

        $db = new Db();

        $elem_name = $db->rows(/** @lang MySQL */
            "SELECT * FROM elem WHERE kod_elem = $kod_elem AND del=0"); // Получаем название удаляемого элемента

        $name = $elem_name[0]['name'];
        $obozn = $elem_name[0]['obozn'];
        $kod_user = func::kod_user();

        $new_name = $obozn;
        if (substr_count($name, $obozn) == 0)
            $new_name = $name . ' ' . $obozn;

        $db->query(/** @lang MySQL */
            "UPDATE elem SET del=1, kod_user=$kod_user WHERE kod_elem=$kod_elem"); // Удаляем элемент
        $Docum = new Docum();
        $Docum->DeleteElemFiles($kod_elem); // Удаляем связные документы

        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM parts WHERE kod_elem=$kod_elem AND del=0"); // Получаем список партий, где участвовал удаленный элемент
        if ($db->cnt == 0)
            return;

        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $modif = $row['modif'];
            if ($modif != '')
                $modif = '(' . $modif . ')';

            $modif = $new_name . $modif;
            $kod_part = $row['kod_part'];

            $db->query(/** @lang MySQL */
                "UPDATE parts SET kod_elem=$kod_dest, modif='$modif', edit=1, kod_user=$kod_user WHERE kod_part=$kod_part"); // заменяем код уделенного элемента
        }

    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Обработка событий
     */
    public function Events()
    {
        $event = false;
        if (isset($_GET['setCompl'], $_GET['kod_elem'])) {
            if ((int)$_GET['setCompl'] > 0)
                $this->DeleteReplace($_GET['kod_elem'], (int)$_GET['setCompl']);
            else
                $this->DeleteReplace($_GET['kod_elem']);
            header('Location: http://' . $_SERVER['HTTP_HOST'] . "/form_nomen.php");
        }

        if (isset($_POST['formEdit'], $_POST['obozn'], $_POST['name'])) {
            $this->Save($_POST['obozn'], $_POST['name'], $_POST['shablon'], $_POST['shifr'], $_POST['web_link']);
            $event = true;
        }

        if (isset($_POST['formAdd'], $_POST['obozn'], $_POST['name'])) {
            $this->AddElem($_POST['obozn'], $_POST['name'], $_POST['shifr'], 1, $_POST['shablon'], $_POST['web_link']);
            $event = true;
        }

        if (isset($_POST['Flag'])) {
            $flag = $_POST['Flag'];

            if ($flag == 'SetNomen' and isset($_POST['kod_elem_set'])) {
                $this->setNomen($_POST['kod_elem_set'], 1);
                $event = true;
            } elseif ($flag == 'UnsetNomen' and isset($_POST['kod_elem_set'])) {
                $this->setNomen($_POST['kod_elem_set'], 0);
                $event = true;
            } elseif ($flag == "AddSubElem" and isset($_POST['kod_elem_base'], $_POST['kod_elem'])) {
                $this->addSubElem($_POST['kod_elem']);
                $event = true;
            } elseif ($flag == "DelFromSpec" and isset($_POST['kod_spec_del'])) {
                $this->delSpec($_POST['kod_spec_del']);
                $event = true;
            } elseif ($flag == "AddPrice" and isset($_POST['price'], $_POST['quantity'])) {
                $this->addPrice($_POST['price'], $_POST['quantity']);
                $event = true;
            } elseif ($flag == "DelPrice" and isset($_POST['kod_price_del'])) {
                $this->delPrice($_POST['kod_price_del']);
                $event = true;
            }
        }

        if ($event)
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Установить признак номенклатуры
     * @param $kod_elem
     * @param int $nomen - 0-не в номенклатуре(не продаем), 1-в номенклатуре(продаем)
     */
    public static function setNomen($kod_elem, $nomen = 0)
    {
        $db = new Db();

        $db->query(/** @lang MySQL */
            "UPDATE elem SET nomen = $nomen WHERE kod_elem = $kod_elem");
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Выдает строку для поиска по элементам - Шифр - Обозначение - Наименование - Код
     * @param $row
     * @return mixed|string
     */
    public static function getSearchName($row)
    {
        if (count($row) === 0)
            return "";

        $name = $row['name'];
        $obozn = $row['obozn'];
        $shifr = $row['shifr'];
        $kod_elem = $row['kod_elem'];

        if ($shifr !== "" and $name == $shifr)
            $name = "";

        if ($obozn !== "" and strpos($name, $obozn) !== false)
            $obozn = "";

        if ($shifr == $obozn)
            $obozn = "";

        return "$shifr $obozn $name $kod_elem";
    }
//------------------------------------------------------------------------
//
    /**
     * Добавление элемента в спецификацию
     * @param $kod_elem - код подчиненного элемента
     * @param int $quantity - количество/применяемость
     * @param int $type - тип (обязательный или нет)
     */
    public function addSubElem($kod_elem, $quantity = 1, $type = 1)
    {
        if ($kod_elem == $this->kod_elem) // Самого в себя нельзя добавить
            return;
        // todo - возможно потребуется проверка вложений, нет ли вхождений в подчиненные элементы
        $db = new Db();
        // Проверка - может элемент уже есть в спецификации
        $db->rows(/** @lang MySQL */
            "SELECT * FROM specs WHERE kod_elem_base=$this->kod_elem AND kod_elem_sub=$kod_elem");
        if ($db->cnt > 0)
            return;

        $quantity = func::clearNum($quantity);

        $kod_user = func::kod_user();
        $db->query(/** @lang MySQL */
            "INSERT INTO specs (kod_elem_base,kod_elem_sub,quantity,type,kod_user) VALUES($this->kod_elem,$kod_elem,$quantity,$type,$kod_user)");
    }

//------------------------------------------------------------------------
    public function formSpecTotal()
    {
        $res = "";
        if (isset($_SESSION['MM_UserGroup'])) // todo - придумать глобальную политику прав
            if ($_SESSION['MM_UserGroup'] === "admin")
                $res = func::ActButton2("", "Добавить в спецификацию", "AddSubElem", "kod_elem", $this->kod_elem);

        if (isset($_POST['kod_elem']))
            if ($_POST['kod_elem'] == $this->kod_elem) {
                $res .= "<form method='post'>";
                $res .= $this->formSelList2(); // kod_elem
                $res .= "<input type='hidden' name='kod_elem_base' value='$this->kod_elem'>";
                $res .= "<input type='hidden' name='Flag' value='AddSubElem'>";
                $res .= "<input type='submit' value='Добавить'>";
                $res .= "</form>";
                $res .= func::Cansel();
            }

        $spec = $this->formSpec();
        if ($spec !== "")
            $res .= "<h3>Спецификация</h3>" . $spec;

        $spec = $this->formSpecSub();
        if ($spec !== "")
            $res .= "<h3><a href='form_main.php?sgp=8&kod_elem=$this->kod_elem'>Входит в состав</a></h3>" . $spec;

        return $res;
    }
//----------------------------------------------------------------------
//
    /**
     * Вывод списка элементов, которые входят в состав данного элемента
     *
     */
    public function formSpec()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                                      view_elem.name AS elem_name,
                                      view_elem.kod_elem,
                                      view_elem.shifr,
                                      view_elem.nomen,
                                      photo.path,
                                      specs.quantity,
                                      specs.kod_spec
                                    FROM specs
                                      INNER JOIN view_elem ON kod_elem_sub=view_elem.kod_elem
                                      LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                    WHERE specs.kod_elem_base=$this->kod_elem AND specs.del=0
                                    ORDER BY shifr;");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        return $this->getSpec($rows);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Вывод списка элементов, в состав которых входит данный элемент
     *
     */
    public function formSpecSub()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT
                                      view_elem.name AS elem_name,
                                      view_elem.kod_elem,
                                      view_elem.shifr,
                                      view_elem.nomen,
                                      photo.path,
                                      specs.quantity,
                                      specs.kod_spec
                                    FROM specs
                                      INNER JOIN view_elem ON kod_elem_base=view_elem.kod_elem
                                      LEFT JOIN (SELECT * FROM view_docum_elem WHERE name='Фото' ORDER BY view_docum_elem.kod_docum DESC) AS photo ON view_elem.kod_elem=photo.kod_elem
                                    WHERE specs.kod_elem_sub=$this->kod_elem AND specs.del=0
                                    ORDER BY shifr;");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "";

        return $this->getSpec($rows);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $kod_spec
     */
    public function delSpec($kod_spec)
    {
        if (func::user_group() !== "admin") // todo - Придумать глобальные права
            return;

        $db = new Db();
        $db->query(/** @lang MySQL */
            "UPDATE specs SET del=1 WHERE kod_spec=$kod_spec"); // Удаляем элемент
    }

//----------------------------------------------------------------------------------------------------------------------

    public function getSpec($rows)
    {
        $all = false;
        if (isset($_GET['all']))
            $all = true;

        $res = '<table border=0 cellspacing=0 cellpadding=0 rules="rows" frame="below" width="100%">
                 <tr bgcolor="#CCCCCC">
                     <td width="10%">Фото</td>
                     <td align="center">Наименование</td>
                     <td align="center">Применяемость</td>
                 </tr>';

        $other = "";
        $cnt = count($rows);

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $kod_elem = $row['kod_elem'];
            $shifr = $row['shifr'];
            $img = "";
            $link = "form_elem.php?kod_elem=$kod_elem";
            if (isset($row['path'])) {
                $path = $row['path'];
                $img = "<a href='$link'><img alt='Path' src='$path' width='100' border='0' /></a>";
            }

            $btn_nomen = "";
            if (isset($_SESSION['MM_UserGroup'])) // todo - придумать глобальную политику прав
                if ($_SESSION['MM_UserGroup'] === "admin")
                    $btn_nomen = func::ActButton2('', 'Удалить', "DelFromSpec", "kod_spec_del", $row['kod_spec']);

            $name = "";
            if ($row['shifr'] != $row['elem_name'])
                $name = $row['elem_name'];

            $quantity = $row['quantity'];

            $row_nomen = "<tr>
                            <td align='left' valign='top'>$img</td>
                            <td valign='top'><a href='$link'><h1> $shifr </h1> $name </td>
                            <td valign='top'>$quantity $btn_nomen</td>
                         </tr>";

            if ($row['nomen'] == 1)
                $res .= $row_nomen;
            elseif ($row['nomen'] == 0 and $all)
                $other .= $row_nomen;
        }

        if ($all) {
            $res .= '<tr bgcolor="#CCCCCC">
                     <td width="10%">Остальная номенклатура</td>
                     <td align="center"></td>
                    </tr>';
            $res .= $other;
        }

        $res .= '</table>';
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Добавление цены
     * @param int $price
     * @param int $quantity
     */
    public function addPrice($price = 1, $quantity = 10)
    {
        $kod_elem = $this->kod_elem;
        $db = new Db();
        $price = func::clearNum($price);
        if ($price <= 0.01 or (int)$quantity <= 0)
            return;
        $quantity = (int)$quantity;
        $kod_user = func::kod_user();
        $db->query(/** @lang MySQL */
            "INSERT INTO price_list (kod_elem, price, quantity,kod_user) VALUES ($kod_elem,$price,$quantity,$kod_user)");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаление цены
     * @param $kod_price
     */
    public function delPrice($kod_price)
    {
        $kod_price = (int)$kod_price;
        $db = new Db();
        $db->query(/** @lang MySQL */
            "UPDATE price_list SET del=1 WHERE kod_price=$kod_price");
    }

//----------------------------------------------------------------------------------------------------------------------
    public function formPriceList()
    {
        $db = new Db();
        $kod_elem = $this->kod_elem;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM price_list WHERE kod_elem=$kod_elem AND del=0 ORDER BY quantity;");

        $btn_add = "";
        if (func::user_group() == "admin")
            $btn_add = Func::ActButton('', 'Добавить', 'formAddPrice');

        $res = /** @lang HTML */
            "<div class='btn'><div><h1>Прайс-лист</h1></div><div>$btn_add</div></div>";

        if (isset($_POST['Flag']) and func::user_group() == "admin")
            if ($_POST['Flag'] == 'formAddPrice') {
                $res .= /** @lang HTML */
                    "<form action='' method='post'>
                    Цена<input title='price' name='price'>
                    Кол-во<input title='quantity' name='quantity'>
                    <input type='hidden' name='kod_elem' value='$kod_elem'>
                    <input type='submit' value='Добавить'>
                    <input type='hidden' name='Flag' value='AddPrice'>                       
                </form>";
                $res .= func::Cansel();
            }

        $res .= "<table border='0'>";
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $btn_del = "";
            if (func::user_group() == "admin")
                $btn_del = func::ActButton2('', 'Удалить', "DelPrice", "kod_price_del", $row['kod_price']);

            $price = func::Rub($row['price']);
            $quantity = $row['quantity'];

            $res .= /** @lang HTML */
                "<tr><td><$quantity</td><td>-</td><td>$price</td><td>$btn_del</td></tr>";
        }
        $res .= "</table>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Цена из прайса в зависимости от количества
     * @param $kod_elem
     * @param int $quantity_for
     * @return float
     */
    public static function getPriceForQuantity($kod_elem, $quantity_for = 1)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT price,quantity FROM price_list WHERE kod_elem=$kod_elem AND del=0 ORDER BY quantity;");

        if ($db->cnt == 0)
            return 0.;

        $price = func::rnd($rows[0]['price']);
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $quantity = $row['quantity'];

            if ($quantity_for >= $quantity) {
                $price = func::rnd($row['price']);
            }
        }
        return $price;
    }

//----------------------------------------------------------------------------------------------------------------------
    public function formPriceListAll()
    {
        $db = new Db();
        $kod_elem = $this->kod_elem;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT elem.kod_elem,kod_price,price,quantity,obozn,name,shifr FROM price_list INNER JOIN elem ON elem.kod_elem=price_list.kod_elem WHERE price_list.del=0 ORDER BY shifr,quantity;");

        $btn_add = "";
        if (func::user_group() == "admin")
            $btn_add = Func::ActButton('', 'Добавить', 'formAddPrice');

        $res = /** @lang HTML */
            "<div class='btn'><div><h1>Прайс-лист</h1></div><div>$btn_add</div></div>";

        if (isset($_POST['Flag']) and func::user_group() == "admin")
            if ($_POST['Flag'] == 'formAddPrice') {
                $res .= /** @lang HTML */
                    "<form action='' method='post'>
                    Цена<input title='price' name='price'>
                    Кол-во<input title='quantity' name='quantity'>
                    <input type='hidden' name='kod_elem' value='$kod_elem'>
                    <input type='submit' value='Добавить'>
                    <input type='hidden' name='Flag' value='AddPrice'>                       
                </form>";
                $res .= func::Cansel();
            }

        $res .= "<table border='0'>";
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $btn_del = "";
            if (func::user_group() == "admin")
                $btn_del = func::ActButton2('', 'Удалить', "DelPrice", "kod_price_del", $row['kod_price']);

            $price = func::Rub($row['price']);
            $quantity = $row['quantity'];
            $shifr = $row['shifr'];
            $res .= /** @lang HTML */
                "<tr><td>$shifr</td><td><$quantity</td><td>-</td><td>$price</td><td>$btn_del</td></tr>";
        }
        $res .= "</table>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Форма Комплектация - элементы неосновной номенклатуры
     * @param int $kod_elem
     * @return string
     */
    public function formNomenModif($kod_elem = 1001)
    {
        $kod_elem = (int)$kod_elem;

        $db = new Db();
        if ($kod_elem > 0)
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM parts where kod_elem=$kod_elem and del=0 group by modif order by modif;");
        else
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM parts where del=0 group by modif order by modif;");

        $cnt = $db->cnt;

        if ($cnt == 0)
            return "Нет данных по коду $kod_elem";

        $res = /** @lang HTML */
            "<table border=0 cellspacing=5 cellpadding=10 rules=\"rows\" frame=\"below\" width=\"100%\">
                 <tr bgcolor=\"#CCCCCC\">
                     <td align=\"center\">Наименование</td>
                 </tr>";

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $modif = $row['modif'];

            $btn = func::ActButton2("", "Выбрать", "Select", "modif", $modif);

            $res .= /** @lang HTML */
                "<tr>
                            <td valign='top'><div class='btn'><div>$btn</div><div>$modif</div></div></td>
                     </tr>";
        }
        $res .= /** @lang HTML */
            "</table>";

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $modif
     * @param $kod_elem
     * @return string
     * @throws Exception
     */
    public function formNomenDocs($modif, $kod_elem)
    {
        if (!isset($modif, $kod_elem))
            return "";

        $kod_elem = (int)$kod_elem;

        $db = new Db();
        $modif = $db->real_escape_string($modif);
        $and = "";
        if (isset($_GET['p']))
            $and = " AND doc_type=1";
        $sql = /** @lang MySQL */
            "SELECT * FROM view_rplan WHERE kod_elem=$kod_elem and $and modif='$modif' order by view_rplan.data_postav;";
        $rows = $db->rows($sql);

        if ($db->cnt == 0)
            return "";

        return Doc::formRPlan_by_Doc($rows);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Наименование для документов
     * @param $rplan_row [] - kod_elem, modif, name, [shablon]
     * @return mixed|string
     */
    public static function getNameForInvoice($rplan_row)
    {
        if (!isset($rplan_row['kod_elem']))
            return "";

        $kod_elem = (int)$rplan_row['kod_elem'];

        if ($rplan_row['modif'] == "")
            return $rplan_row['name'];

        if (!isset($rplan_row['shablon'])) {
            $db = new Db();
            $rows = $db->rows(/** @lang MySQL */
                "SELECT * FROM elem WHERE kod_elem=$kod_elem;");
            if ($db->cnt == 0)
                return "";
            $row = $rows[0];
            $shablon = $row['shablon'];
        } else
            $shablon = $rplan_row['shablon'];

        if ($shablon == "")
            return $rplan_row['name'] . " (" . $rplan_row['modif'] . ")";

        if (strpos($shablon, "[Mod]") === false)
            return $rplan_row['name'] . " (" . $rplan_row['modif'] . ")";

        return str_replace("[Mod]", $rplan_row['modif'], $shablon);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Шаблон комплектации элемента - подбор позиций из 1с в соотвествии с заданным шаблоном
     *
     */
    public function formElemSetTemplate()
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM elem_set_template WHERE kod_elem=$this->kod_elem AND del=0;");
        if ($db->cnt == 0)
            return "Список пуст";

        $res = /** @lang HTML */
            "<table>
                <tr>
                   <td>Наименование</td>
                   <td>Кол-во</td>
                   <td>Список</td>
             </tr>";
        $cnt = $db->cnt;

        for ($i = 0; $i < $cnt; $i++) {
            $row = $rows[$i];
            $name = $row['name'];
            $numb = $row['numb'];

            $list = "";

            $rows_n = $db->rows(/** @lang MySQL */
                "SELECT * FROM sklad_1c WHERE name LIKE name;");

            for ($j = 0; $j < $db->cnt; $j++) {
                $row_n = $rows_n[$j];
                $list .= $row_n['name'];
            }

            $res .= /** @lang HTML */
                "<tr>
                    <td>$name</td>
                    <td>$numb</td>
                    <td>$list</td>
                </tr>";
        }

        $res .= /** @lang HTML */
            "</table>";
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Список Организаций которые поставляли данный элемент
     * @return string
     */
    public function formSuppliers()
    {
        $db = new Db();

        $kod_elem = $this->kod_elem;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT   `view_pplan`.`kod_org`,
                             `view_pplan`.`modif`,
                             `view_pplan`.`kod_elem`,
                             `view_pplan`.`shifr`,
                             SUM( `view_pplan`.`numb_postup` )  AS `numb`,
                             `view_pplan`.`data_postav`,
                             `view_pplan`.`ispolnit_nazv_krat`,
                             `view_pplan`.`kod_ispolnit`,
                             `view_pplan`.`name`
                    FROM     `view_pplan`
                    WHERE    ( `view_pplan`.`numb_postup` > 0  ) AND kod_elem = $kod_elem
                    GROUP BY `view_pplan`.`kod_ispolnit`
                    ORDER BY numb DESC;");

        if ($db->cnt == 0)
            return "";

        $res = '<table border=1 cellspacing=0 cellpadding=0 width="100%">
		            <tr bgcolor="#CCCCCC">
		                <td>Название</td>
		                <td>Количество</td>
		            </tr>';

        $sum = 0;
        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];

            $nazv_krat = $row['ispolnit_nazv_krat'];
            $numb = func::Rub($row['numb'], 0);
            $kod_org = (int)$row['kod_ispolnit'];

            $org_link = "form_elem.php?kod_elem=$this->kod_elem&kod_org=" . $kod_org;

            $res .= "<tr>
                        <td><a href='$org_link'> $nazv_krat </a></td>
                        <td align='right'> $numb </td>
        		  	</tr>";
            $sum += (int)$row['numb'];
        }
        $res .= '<tr bgcolor="#CCCCCC">
                    <td align="right">Сумма</td>
                    <td align="right">' . func::Rub($sum, 0) . '</td>
                 </tr>';

        $res .= '</table>';
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Последняя цена по организации
     * @param $kod_elem
     * @param $kod_org
     * @return float
     */
    public static function getLastPriceByOrg($kod_elem, $kod_org)
    {
        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT price_it FROM view_rplan WHERE kod_elem=$kod_elem AND kod_org=$kod_org ORDER BY data_postav DESC LIMIT 1;");

        if ($db->cnt < 1)
            return 0.;

        return func::rnd($rows[0]['price_it']);
    }
//------------------------------------------------------------------------
//
    public static function formElemRating()
    {
        $db = new Db();
        $kod_org_main = config::$kod_org_main;
        $y = date("Y");
        if (isset($_GET['y']))
            $y = (int)$_GET['y'];
        $y_next = $y + 1;
        $rows = $db->rows(/** @lang MySQL */
            "SELECT   `view_rplan`.`kod_elem`,
                             `view_rplan`.`name`,
                             SUM( `view_raschety_plat`.`summa_raspred` )  AS `summ`
                    FROM     `view_rplan` 
                    INNER JOIN `view_raschety_plat`  ON `view_rplan`.`kod_part` = `view_raschety_plat`.`kod_part` 
                    WHERE    ( `view_raschety_plat`.`data_plat` BETWEEN '$y-01-01' AND '$y_next-01-01') AND kod_ispolnit = $kod_org_main
                    GROUP BY `view_rplan`.`kod_elem`
                    ORDER BY summ DESC;");
        if ($db->cnt == 0)
            return "";

        $year_p = $y - 1;
        $res = /** @lang HTML */
            "<a href='form_nomen.php?rating&y=$year_p'>$year_p</a>
            $y
            <a href='form_nomen.php?rating&y=$y_next'>$y_next</a>
            <table><tr><td>Наименование</td><td>Сумма</td><td>Процент</td></tr>";
        $total_summ = 0;
        for ($i = 0; $i < $db->cnt; $i++) { // Вычисляем сумму для расчета процента
            $row = $rows[$i];
            $total_summ += $row['summ'];
        }

        if($total_summ < config::$min_price)
            return "</table>Нет данных";

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = $rows[$i];
            $name = $row['name'];
            $kod_elem = $row['kod_elem'];
            $summ = func::Rub($row['summ']);
            $prc = func::Proc($row['summ']/$total_summ);
            $res .= /** @lang HTML */
                "<tr>
                    <td><a href='form_elem.php?kod_elem=$kod_elem&y=$y'>$name</td>
                    <td align='right'>$summ</td>
                    <td align='right'>$prc</td>
                </tr>";
        }
        $res .= "</table>";
        $res .= func::Rub($total_summ);
        return $res;
    }
//------------------------------------------------------------------------
//
    /**
     * Ссылка на сайт
     * @param $row
     * @return mixed|string
     */
    public static function formWEBLink($row)
    {
        if(!isset($row['web_link']))
            return "";
        $web_link = $row['web_link'];
        if ($web_link != "")
            $web_link = "<a href='$web_link' target='_blank'>www</a>";

        return $web_link;
    }
}