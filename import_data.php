<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 2018-12-25
 * Time: 12:45
 */
include_once "security.php";

// todo - Придумать глобальную политику прав
if ($_SESSION['MM_UserGroup'] != "admin")
    exit("Access denied");
?>

    <form enctype="multipart/form-data" method="post" role="form">
        <div class="form-group">
            <label for="exampleInputFile">File Upload</label>
            <input type="file" name="file" id="file" size="150">
            <p class="help-block">Only Excel/CSV File Import.</p>
        </div>
        <button type="submit" class="btn btn-default" name="Import" value="Import">Upload</button>
    </form>

<?php
include "class_db.php";

if (isset($_POST["Import"])) {

    echo $filename = $_FILES["file"]["tmp_name"];

    if ($_FILES["file"]["size"] > 0) {
        $file = fopen($filename, "r");

        $db = new Db();
        $sql = /** @lang MySQL */
            "TRUNCATE TABLE sklad_1c;"; // Очищаем таблицу
        $db->query($sql);

        $cnt = 0; // Количество вставленных строк
        while (($emapData = fgetcsv($file, 10000, ";")) !== FALSE) {

            if (count($emapData) < 5)
                continue;

            $name = func::clearString($emapData[0]); // Удаляем лишние пробелы
            $kod_1c = (int)$emapData[1];
            $price = func::clearNum($emapData[2]);
            $numb = func::clearNum($emapData[3]);
            $sum = func::clearNum($emapData[4]);

            // Поверка данных
            if ($name == "" or $kod_1c <= 0 or $price <= 0 or $numb <= 0 or $sum <= 0)
                continue;

            $cnt++;
            $sql = /** @lang MySQL */
                "INSERT INTO sklad_1c(name, kod_1c, price, numb, sum) VALUES ('$name',$kod_1c,$price,$numb,$sum);";
            $db->query($sql);
        }

        fclose($file);

        if ($cnt == 0)
            echo "Error: 0 rows inserted. Check file format or content (name, kod_1c, price, numb, sum).";
        else
            echo "CSV File has been successfully Imported. Rows inserted: $cnt";

        header('Location: import_data.php');
    } else
        echo 'Invalid File:Please Upload CSV File';
}

?>