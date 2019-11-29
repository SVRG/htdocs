<?php

//include_once('Thumbnail.php');

class Func
{

    public static $green = '#ADFAC2'; // Зеленый
    //public static $orange = '#FFD222'; // Оранж
    public static $red = '#F18585'; // Красный
    //public static $grey = '#CCCCCC'; // Серый
    public static $yellow = '#FFFF99'; // Желтый
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Сколько дней осталось до указанной даты
     * @param string $Date - 17.07.2017
     * @param string $format - формат даты
     * @return int
     * @throws Exception
     */
    public static function DaysRem($Date, $format = "Y-m-d")
    {
        if (!isset($Date) or !self::validateDate($Date, $format))
            return 0;

        $now = new DateTime("now");
        $date = new DateTime($Date);

        return $now->diff($date)->format("%r%a");
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param $FlagName
     * @return bool
     */
    public static function issetFlag($FlagName)
    {
        if (isset($_POST['Flag']))
            if ($_POST['Flag'] === $FlagName)
                return true;
        return false;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Формат денег
     * @param double $R
     * @param int $decimals
     * @return string
     */
    public static function Rub($R, $decimals = 2)
    {
        $R = round((double)$R, 2);
        return number_format($R, $decimals, '.', ' ');
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Готовим число для вставки в MySQL
     * @param $num_str
     * @param int $precision
     * @return float|mixed|string|string[]|null
     */
    public static function clearNum($num_str, $precision = 0)
    {
        $res = str_replace(",", ".", $num_str);
        $res = preg_replace("/[^0-9.-]/", "", $res);

        if ($precision > 0)
            $res = round((double)$res, (int)$precision);

        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Удаляем лишние пробелы и чистим строку
     * @param $string
     * @return string
     */
    public static function clearString($string)
    {
        // todo - надо проработать пробелы с точкой и запятой, это могут быть цифры или обозначения с децимал. номером
        //$string = str_replace(".",". ",$string); // Добавляем пробел после точки
        //$string = str_replace(",",", ",$string); // Добавляем пробел после запятой
        $string = str_replace("(", " (", $string); // Добавляем пробел перед откр скобкой
        $string = preg_replace('/\s\s+/', ' ', $string); // Удаляем лишние пробелы
        //$string = preg_replace('(^A-Za-z0-9.,-+)', '', $string); // todo - Удаляем лишние символы
        $string = ltrim($string); // Удаляем пробел в начале строки
        $string = rtrim($string); // Удаляем пробел в конце строки
        $string = str_replace("( ", "(", $string); // Удаляем пробел после откр скобки
        $string = str_replace(" )", ")", $string); // Удаляем пробел перед закр скобкой
        $string = str_replace(" ,", ",", $string); // Удаляем пробел перед запятой
        $string = str_replace(" .", ".", $string); // Удаляем пробел перед точкой

        return $string;
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Преобразуем дату из dd.mm.yyyy в формат yyyy-mm-dd для MySQL
     * @param string $Date
     * @return string
     */
    public static function Date_to_MySQL($Date = "")
    {
        if ($Date == "" or !self::validateDate($Date))
            return date('Y-m-d');

        $date = date_create_from_format("d.m.Y", $Date);
        return $date->format("Y-m-d");
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Преобразует дату из MySQL yyyy-mm-dd в формат dd.mm.yyyy
     * @param string $MySQL_Date
     * @return string
     */
    public static function Date_from_MySQL($MySQL_Date)
    {
        if (!self::validateDate($MySQL_Date, "Y-m-d")) {
            if (!self::validateTimeStamp($MySQL_Date))
                return "";
        }

        $date = strtotime($MySQL_Date);
        return date('d.m.Y', $date);
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * @param $numb
     * @param int $precision
     * @return float
     */
    public static function rnd($numb, $precision = 2)
    {
        return round((double)$numb, (int)$precision);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Проверка корректности даты
     * @param $date
     * @param string $format
     * @return bool
     */
    public static function validateDate($date, $format = 'd.m.Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Проверка корректности даты
     * @param $timestamp
     * @return bool
     */
    public static function validateTimeStamp($timestamp)
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $timestamp, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }
        return false;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Процент
     * @param $R
     * @return float
     */
    public static function Proc($R)
    {
        $R = round($R * 100, 0);
        return $R;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Если строка пустая то возвращает тире
     * @param $Str
     * @param string $Delim
     * @return string
     */
    public static function Mstr($Str, $Delim = '-')
    {
        if (isset($Str)) {
            if ($Str == '')
                return $Delim;
            else
                return $Str;
        }
        return $Delim;
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Кнопка в виде картинки
     * @param $ButtValue
     * @return string
     */
    public static function btnImage($ButtValue)
    {
        $btn = " <input title='$ButtValue' type='submit' name='Button' value='$ButtValue' />";

        if ($ButtValue == "Копировать")
            $btn = "<input alt='Copy' title='$ButtValue' type='image' src='img/copy.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Редактировать" or $ButtValue == "Изменить")
            $btn = "<input alt='Edit' title='$ButtValue' type='image' src='img/edit.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Добавить")
            $btn = "<input alt='Add' title='$ButtValue' type='image' src='img/add.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Добавить Примечание")
            $btn = "<input alt='AddCaption' title='$ButtValue' type='image' src='img/add_note.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Удалить")
            $btn = "<input alt='Delete' title='$ButtValue' type='image' src='img/delete.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Выбрать")
            $btn = "<input alt='Select' title='$ButtValue' type='image' src='img/view_properties.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "Комплектация")
            $btn = "<input alt='Select' title='$ButtValue' type='image' src='img/view_properties.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "PO")
            $btn = "<input alt='Select' title='$ButtValue' type='image' src='img/po.png' name='Button' value='$ButtValue'/>";
        elseif ($ButtValue == "QT")
            $btn = "<input alt='Select' title='$ButtValue' type='image' src='img/qt.png' name='Button' value='$ButtValue'/>";
        return $btn;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Создает Форму с одной кнопкой
     * @param string $Act
     * @param string $ButtValue
     * @param string $FlagVal
     * @param string $form_option например target='_blank'
     * @return string
     */
    public static function ActButton($Act = '', $ButtValue = 'OK', $FlagVal = 'Act', $form_option = '')
    {
        $btn = self::btnImage($ButtValue);

        return "<form name='FNAME' method='POST' action='$Act ' $form_option>
                    <input type='hidden' name='Flag' value='$FlagVal' />
                   $btn
                </form>";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Кнопка подтверждения действия
     * @param string $ButtValue
     * @param string $FlagVal
     * @param string $Message
     * @return string
     */
    public static function ActButtonConfirm($ButtValue = 'Подтвердить', $FlagVal = "Flag", $Message = "Просьба подтвердить действие")
    {

        $btn = self::btnImage($ButtValue);

        return "<form name='FNAME' method='POST' action='' onsubmit='return confirm(\"$Message\");' >
                    <input type='hidden' name='Flag' value='$FlagVal' />
                    $btn
                </form>";
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Создает Форму с одной кнопкой
     * + скрытое поле Flag со значением
     * + скрытое поле с именем $hidden_name и значением $hidden_val
     * + если название кнопки "Удалить" то добавляется подтверждение
     * @param string $Act
     * @param string $ButtValue
     * @param string $FlagVal
     * @param string $hidden_name
     * @param string $hidden_val
     * @param string $confirm_text - текст в форме
     * @return string
     */
    public static function ActButton2($Act = '', $ButtValue = 'OK', $FlagVal = "Act", $hidden_name = "Name", $hidden_val = "1", $confirm_text = "")
    {
        $btn = self::btnImage($ButtValue);

        if (strpos($ButtValue, "Удалить") !== false and $confirm_text == "")
            $confirm_text = "Вы уверены, что хотите удалить запись?";

        $confirm_code = "";
        if ($confirm_text != "")
            $confirm_code = "onsubmit='return confirm(\"$confirm_text\");'";

        return "<form name='FNAME' method='POST' action='$Act' $confirm_code>
                    <input type='hidden' name='Flag' value='$FlagVal' />
                    <input type='hidden' name='$hidden_name' value='$hidden_val' />
                    $btn
                </form>";
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Текущее число
     * @param string $Delim
     * @return string
     */
    public static function NowE($Delim = '.')
    {
        return date('d') . $Delim . date('m') . $Delim . date('Y');
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Текущее число для названия файла
     * @param string $Delim
     * @return string
     */
    public static function NowDoc($Delim = '_')
    {
        return date('Y') . $Delim . date('m') . $Delim . date('d');
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Кнопка - отменв
     * @param int $Echo
     * @return string
     */
    public static function Cansel($Echo = 0)
    {
        $res = Func::ActButton($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'Отмена', '');

        if ($Echo == 1) {
            echo $res;
            return "";
        }

        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Ссылка
     * @param string $Link
     * @param string $Text
     * @return string
     */
    static public function Link($Link = '', $Text = '')
    {
        if ($Link == '') return '';

        if ($Text == '')
            $Text = $Link;

        if (strpos($Link, 'http') === false) {
            $Link = "http://$Link";
        }
        return "<a href=\"$Link\" target=\"_blank\">$Text</a>";
    }

//----------------------------------------------------------------------------------------------------------------------
    static public function rus2lat2($string)
    {

        $rus = array('ё', 'ж', 'ц', 'ч', 'ш', 'щ', 'ю', 'я', 'Ё', 'Ж', 'Ц', 'Ч', 'Ш', 'Щ', 'Ю', 'Я');
        $lat = array('yo', 'zh', 'tc', 'ch', 'sh', 'sh', 'yu', 'ya', 'YO', 'ZH', 'TC', 'CH', 'SH', 'SH', 'YU', 'YA');
        $string = str_replace($rus, $lat, $string);
        $string = func::mb_strtr($string,
            "АБВГДЕЗИЙКЛМНОПРСТУФХЪЫЬЭабвгдезийклмнопрстуфхъыьэ",
            "ABVGDEZIJKLMNOPRSTUFH_I_Eabvgdezijklmnoprstufh_i_e");

        // удаляем лишние пробелы
        $string = preg_replace('/\s\s+/', ' ', $string);
        // удаляем пробелы
        $string = str_replace(" ", "_", $string); // сохраняем пробел от перехода в %20
        // $string = str_replace("№", "N", $string); // сохраняем пробел от перехода в %20
        // удаляем мусор
        $string = preg_replace("/[^A-Za-z0-9_\-]/", '-', $string);
        // приводим к нижнему регистру
        $string = strtolower($string);
        // убираем "-" дефисы, который больше двух
        $string = preg_replace("/(-){2,}/", "-", $string);
        // убираем "-" дефисы в начале и конце строки
        $string = preg_replace("/(^-)|(-$)/", "", $string);

        return ($string);
    }

//----------------------------------------------------------------------------------------------------------------------
    static public function mb_strtr($str, $from, $to)
    {
        return str_replace(func::mb_str_split($from), func::mb_str_split($to), $str);
    }

//----------------------------------------------------------------------------------------------------------------------
    static public function mb_str_split($str)
    {
        return preg_split('~~u', $str, null, PREG_SPLIT_NO_EMPTY);
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param $s
     * @return mixed
     */
    static public function _strip($s)
    {
        $s = str_replace(" ", " ", $s);
        return $s;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Получение имени пользователя из SESSION
     */
    static public function user()
    {
        $user = "unknown";
        if (isset($_SESSION['MM_Username']))
            $user = $_SESSION['MM_Username'];

        return $user;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Получение кода пользователя из SESSION
     */
    static public function kod_user()
    {
        $kod_user = 0;
        if (isset($_SESSION['MM_kod_user']))
            $kod_user = $_SESSION['MM_kod_user'];

        return $kod_user;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Получение группы пользователя из SESSION
     */
    static public function user_group()
    {
        $user_group = 0;
        if (isset($_SESSION['MM_UserGroup']))
            $user_group = $_SESSION['MM_UserGroup'];

        return $user_group;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Возвращает знак валюты
     * @param $kod_val = 1-руб;2-доллар;3-евро
     * @return string
     */
    static public function val_sign($kod_val = 1)
    {
        switch ($kod_val) {
            case 2:
                return "$";
            case 3:
                return "€";
        }
        return "";
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Возвращает сумму прописью
     * @param $num
     * @return string
     * @author runcore
     * @uses morph(...)
     */
    public static function num2str($num)
    {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array( // Units
            array('копейка', 'копейки', 'копеек', 1),
            array('рубль', 'рубля', 'рублей', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) $out[] = self::morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            } //foreach
        } else $out[] = $nul;
        $out[] = self::morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . self::morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        mb_internal_encoding("UTF-8");
        $res = trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
        $res = mb_strtoupper(mb_substr($res, 0, 1)) . mb_substr($res, 1);
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Склоняем словоформу
     * @ author runcore
     * @param $n
     * @param $f1
     * @param $f2
     * @param $f5
     * @return mixed
     */
    public static function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) return $f5;
        $n = $n % 10;
        if ($n > 1 && $n < 5) return $f2;
        if ($n == 1) return $f1;
        return $f5;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Возвращает историю по коду записи
     * @param $table_name
     * @param $key_field_value
     * @return array
     */
    public static function getHistory($table_name, $key_field_value)
    {
        $key_field_value = (int)$key_field_value;

        $db = new Db();
        $rows = $db->rows(/** @lang MySQL */
            "SELECT * FROM history WHERE table_name='$table_name' AND key_field_value=$key_field_value ORDER BY time_stamp DESC;");

        if ($db->cnt == 0)
            return [];

        $res = array();

        for ($i = 0; $i < $db->cnt; $i++) {
            $row = unserialize($rows[$i]['ser_array']);
            array_push($res, $row);
        }
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Очистка текста от лишних пробелов и переводов
     * @param $text
     * @return string|string[]|null
     */
    public static function clearText($text)
    {
        $text = strip_tags($text); // Удаляем HTML тэги
        $text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text); // Удаляем лишние переводы строк
        $text = rtrim($text); // Пробелы справа
        $text = ltrim($text); // Пробелы слева

        return $text;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Выбирает все e-mail из текста
     * @param $text
     * @return string
     */
    public static function extract_email_from_text($text)
    {
        preg_match_all("/[._a-zA-Z0-9-]+@[._a-zA-Z0-9-]+/i", $text, $matches);
        if(isset($matches[0][0]))
            return $matches[0][0];
        else
            return "";
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Прибавляет к текущей дате $week недель
     * @param int $week
     * @return false|string
     */
    public static function datePlusWeek($week)
    {
        $week = (int)$week;
        return date("Y-m-d", strtotime("+$week week",strtotime("now")));
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Прибавляет к текущей дате $day дней
     * @param $day
     * @return false|string
     */
    public static function datePlusDay($day)
    {
        $day = (int)$day;
        return date("Y-m-d", strtotime("+$day day",strtotime("now")));
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Проверяет наличие полей в массиве
     * @param array $rows
     * @param array $fields
     * @return bool
     */
    public static function checkArrayFields(array $rows, array $fields)
    {
        return !array_diff_key(array_flip($fields), $rows[0]);
    }
}
