<?php
?>
<h1><?= $message ?></h1>
Заказ из системы: <?=$orderNumber?><br>
Заказ в системе Acquiring: <?=$mdOrder?><br>
Состояние: <?=$state?><br>
Стоимость: <?=$amount?><br>
Успешная ссылка: <b><?=$callbackSuccess?></b><br>
Провальная ссылка: <b><?=$callbackFail?></b><br>
Ссылка для оповещений: <b><?=$callbackNotify?></b><br>
<br>

<form action="<?=$callbackSuccess?>" method="get">
    <input type="hidden" name="state" value="<?=$state?>">
    <input type="hidden" name="orderNumber" value="<?=$orderNumber?>">
    <input type="hidden" name="mdOrder" value="<?=$mdOrder?>">
    <button type="submit">Обратно в магазин</button>
</form>
