<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
<title>КСЭ</title>
<head>
</head>
<body>
<script src="https://lk.cse.ru/js/build/8b0efa49b621cc1c191d7a3f25bf507c1416906925.js"></script>
<link media="all" type="text/css" rel="stylesheet" href="https://lk.cse.ru/css/cssar_de99b7620b0bbd8ab9d866828e446e3f1515058643.css">
<div class="search-shipment-box">
    <form method="GET" action="https://lk.cse.ru/api/track/47cd36872d1e3eb008f80d874c70456f" accept-charset="UTF-8" id="cse-track">
        <p>
            <label><i>Введите номер:</i></label>
            <input class="inp-r" id="order-type-opt-0" name="type" type="radio" value="waybill">
            <label for="order-type-opt-0" class="current">накладной</label>
            <input class="inp-r" id="order-type-opt-1" checked="checked" name="type" type="radio" value="order">
            <label for="order-type-opt-1">заказа</label>
            <input name="lang" type="hidden" value="ru">
        </p>
        <div class="shipment-inputs">
            <label>
                <input class="shipment-input" name="number" type="text" value="<?php echo $_POST['cse_number'];?>">
            </label>
            <input name="token" type="hidden" value="47cd36872d1e3eb008f80d874c70456f">
            <button class="button">Отследить</button>
        </div>
        <div class="dfn">
            <div class="radio-desc waybill">
                Введите номер накладной. <br /> Номер указан на накладной под штрихкодом.<br />&nbsp;        </div>
            <div class="radio-desc order hidden">
                Введите номер заказа в формате XXX-XXXXXXXXX.<br />&nbsp;<br />&nbsp;        </div>
        </div>
    </form>
</div>
<script src="https://lk.cse.ru/js/cse/track.js"></script>
</body>
</html>