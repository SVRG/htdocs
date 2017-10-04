<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Test</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <script src="js/jquery.min.js"></script>
    <script src="../dist/js/standalone/selectize.js"></script>
    <link rel="stylesheet" href="../dist/css/selectize.default.css">
    <script src="js/index.js"></script>
</head>
<body>
                <?php
                include_once "../../../class_elem.php";
                $E = new Elem();
                echo $E->formSelList2();
                ?>
</body>
</html>
