<?php

//include_once('Thumbnail.php');

class Func
{

    public $green = '#ADFAC2'; // Зеленый
    public $orange = '#FFD222'; // Оранж
    public $red = '#F18585'; // Красный
    public $grey = '#CCCCCC'; // Серый
    public $yellow = '#FFFF99'; // Желтый
//----------------------------------------------------------------------------------------------------------------------
    /**
     * Сколько дней осталось до указанной даты
     * @param string $Date - 17.07.2017
     * @return int
     */
    public static function DaysRem($Date)
    {
        if (!isset($Date)) return 0;
        $now = new DateTime("now");
        $date = new DateTime($Date);
        $res = $now->diff($date)->format("%r%a");
        return $res;
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * Формат денег
     * @param double $R
     * @return string
     */
    public static function Rub($R)
    {
        $R = round((double)$R, 2);
        return number_format($R, 2, '.', ' ');
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

        $date = date_create_from_format("d.m.Y",$Date);
        $date_to_MySQL = $date->format("Y-m-d");
        return $date_to_MySQL;
    }

//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Преобразует дату из MySQL yyyy-mm-dd в формат dd.mm.yyyy
     * @param string $MySQL_Date
     * @return string
     */
    public static function Date_from_MySQL($MySQL_Date = "")
    {
        $date = strtotime($MySQL_Date);
        $res = date('d.m.Y',$date);
        return $res;
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
     * Создает Форму с одной кнопкой
     * @param string $Act
     * @param string $ButtValue
     * @param string $FlagVal
     * @return string
     */
    public static function ActButton($Act = '', $ButtValue = 'OK', $FlagVal = 'Act')
    {
        $res = "<form id='FID' name='FNAME' method='POST' action='$Act '>
                    <input type='hidden' name='Flag' value='$FlagVal' />
                    <input type='submit' name='Button' value='$ButtValue' />
                </form>";
        return $res;
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
     * @return string
     */
    public static function ActButton2($Act = '', $ButtValue = 'OK', $FlagVal = "Act", $hidden_name = "Name", $hidden_val="1")
    {
        if($ButtValue=="Удалить")
            $res = "<form name='FNAME' method='POST' action='$Act' onsubmit='return confirm(\"Вы уверены, что хотите удалить запись?\");' >
                    <input type='hidden' name='Flag' value='$FlagVal' />
                    <input type='hidden' name='$hidden_name' value='$hidden_val' />
                    <input type='submit' name='Button' value='$ButtValue' />
                </form>";
        else
        $res = "<form name='FNAME' method='POST' action='$Act'>
                    <input type='hidden' name='Flag' value='$FlagVal' />
                    <input type='hidden' name='$hidden_name' value='$hidden_val' />
                    <input type='submit' name='Button' value='$ButtValue' />
                </form>";
        return $res;
    }
//----------------------------------------------------------------------------------------------------------------------
//
    /**
     * Построение Формы с одной кнопкой
     * + скрытое поле Flag со значением
     * @param string $Act
     * @param string $Body
     * @param string $ButtValue
     * @param string $FlagVal
     * @return string
     */
    public static function ActForm($Act = '', $Body = '', $ButtValue = 'OK', $FlagVal = 'OK')
    {
        $res = "<form id='FID' name='FNAME' method='POST' action=' $Act '>
                    $Body
                    <input type='submit' name='Button' value=' $ButtValue ' />
                    <input type='hidden' name='Flag' value='$FlagVal' />
               </form>";

        return $res;
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

        return '<a href="' . $Link . '" target="_blank">' . $Text . '</a>';
    }

//----------------------------------------------------------------------------------------------------------------------
// Disclaimer: Скрипт принципиально не сохраняет регистр! Кириллица принудительно переводится в нижний, латиница - в верхний.

// Это связано с необходимостью корректной транслитерации двуязычных названий страниц. 

// Использованная локале-независимая функция UpLow($s)

    static public function UpLow(&$string, $registr = 'up')
    {
        $upper = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $lower = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяabcdefghijklmnopqrstuvwxyz';

        if ($registr == 'up') $string = strtr($string, $lower, $upper);
        else $string = strtr($string, $upper, $lower);

    }
//----------------------------------------------------------------------------------------------------------------------
// Функция обратимой перекодировки кириллицы в транслит.
    static public function rus2lat($s)
    {
// Сначала всё переводим в верхний регистр, причём не с помощью глючной strtoupper
        Func::UpLow($s);
//а потом только кириллицу в нижний

        $s = str_replace("ЫА", "yha", $s);
        $s = str_replace("ЫО", "yho", $s);
        $s = str_replace("ЫУ", "yhu", $s);
        $s = str_replace("Ё", "yo", $s);
        $s = str_replace("Ж", "zh", $s);
        $rus = "АБВГДЕЗИЙКЛМНОПРСТУФХЦ";
        $lat = "abvgdezijklmnoprstufxc";
        $s = strtr($s, $rus, $lat);
        $s = str_replace("Ч", "ch", $s);
        $s = str_replace("Ш", "sh", $s);
        $s = str_replace("Щ", "shh", $s);
        $s = str_replace("Ъ", "qh", $s);
        $s = str_replace("Ы", "y", $s);
        $s = str_replace("Ь", "q", $s);
        $s = str_replace("Э", "eh", $s);
        $s = str_replace("Ю", "yu", $s);
        $s = str_replace("Я", "ya", $s);
        $s = str_replace(" ", "_", $s); // сохраняем пробел от перехода в %20
        $s = str_replace(",", ".h", $s); // сохраняем запятую
        $s = str_replace("№", "N", $s); // сохраняем N
        $s = str_replace("(", "_", $s); // сохраняем (
        $s = str_replace(")", "_", $s); // сохраняем )
//$s=str_replace(""","&quot;",$s); // сохраняем кавычки
        $s = rawurlencode($s); // Разрешённые символы URL - латинские буквы, точка, минус и подчёркивание
        return $s;
    }
//----------------------------------------------------------------------------------------------------------------------
    static public function rus2lat2($string) {

        $rus    = array('ё', 'ж', 'ц', 'ч', 'ш', 'щ', 'ю', 'я', 'Ё', 'Ж', 'Ц', 'Ч', 'Ш', 'Щ', 'Ю', 'Я');
        $lat    = array('yo', 'zh', 'tc', 'ch', 'sh', 'sh', 'yu', 'ya', 'YO', 'ZH', 'TC', 'CH', 'SH', 'SH', 'YU', 'YA');
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
        $string = preg_replace("/(-){2,}/","-",$string);
        // убираем "-" дефисы в начале и конце строки
        $string = preg_replace("/(^-)|(-$)/","",$string);

        return ($string);
    }
//----------------------------------------------------------------------------------------------------------------------
    static public function mb_strtr($str, $from, $to) {
        return str_replace(func::mb_str_split($from), func::mb_str_split($to), $str);
    }
//----------------------------------------------------------------------------------------------------------------------
    static public function mb_str_split($str) {
        return preg_split('~~u', $str, null, PREG_SPLIT_NO_EMPTY);
    }

//----------------------------------------------------------------------------------------------------------------------
    static public function lat2rus($s)
    { // Функция обратной перекодировки транслита в кириллицу.
        $s = rawurldecode($s);
        $s = str_replace("_", ",", $s);// возвращаем запятую
        $s = str_replace("_", " ", $s);// возвращаем пробел
        $s = str_replace("_", "(", $s);// возвращаем пробел
        $s = str_replace("_", ")", $s);// возвращаем пробел
        $s = str_replace("yh", "Ы", $s);
        $s = str_replace("yu", "Ю", $s);
        $s = str_replace("ya", "Я", $s);
        $s = str_replace("yo", "Ё", $s);
        $s = str_replace("shh", "Щ", $s);
        $s = str_replace("eh", "Э", $s);
        $s = str_replace("sh", "Ш", $s);
        $s = str_replace("ch", "Ч", $s);
        $s = str_replace("qh", "Ъ", $s);
        $s = str_replace("zh", "Ж", $s);
        $lat = "abvgdezijklmnoprstufxcyq";
        $rus = "АБВГДЕЗИЙКЛМНОПРСТУФХЦЫЬ";
        $s = strtr($s, $lat, $rus);
        return $s;
    } // function lat2rus($s)

//----------------------------------------------------------------------------------------------------------------------
    static public function _strip($s)
    {
        $s = str_replace(" ", " ", $s);
        return $s;
    }
}
