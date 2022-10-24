<?php
?>
Заказ из системы: <?=$orderNumber?><br>
Заказ в системе Acquiring: <?=$mdOrder?><br>
Стоимость: <?=$amount?><br>
Успешная ссылка: <b><?=$callbackSuccess?></b><br>
Провальная ссылка: <b><?=$callbackFail?></b><br>
Ссылка для оповещений: <b><?=$callbackNotify?></b><br>
<br>


<form method="post">
    <input type="hidden" name="state" value="deposited">
    <input type="hidden" name="message" value="Успех оплаты заказа">
    <input type="hidden" name="orderNumber" value="<?=$orderNumber?>">
    <input type="hidden" name="mdOrder" value="<?=$mdOrder?>">
    <input type="hidden" name="amount" value="<?=$amount?>">
    <input type="hidden" name="success" value="<?=$callbackSuccess?>">
    <input type="hidden" name="fail" value="<?=$callbackFail?>">
    <input type="hidden" name="notify" value="<?=$callbackNotify?>">
    <button type="submit">Оплатить успешно</button>
</form>


<form method="post">
    <input type="hidden" name="state" value="pre_authorized">
    <input type="hidden" name="message" value="Сумма до списания заморожена">
    <input type="hidden" name="orderNumber" value="<?=$orderNumber?>">
    <input type="hidden" name="mdOrder" value="<?=$mdOrder?>">
    <input type="hidden" name="amount" value="<?=$amount?>">
    <input type="hidden" name="success" value="<?=$callbackSuccess?>">
    <input type="hidden" name="fail" value="<?=$callbackFail?>">
    <input type="hidden" name="notify" value="<?=$callbackNotify?>">
    <button type="submit">Заморозить сумму до списания (Оплатить с предавторизацией)</button>
</form>

<form method="post">
    <input type="hidden" name="state" value="denied">
    <input type="hidden" name="message" value="Отказ в авторизации оплаты">
    <input type="hidden" name="orderNumber" value="<?=$orderNumber?>">
    <input type="hidden" name="mdOrder" value="<?=$mdOrder?>">
    <input type="hidden" name="amount" value="<?=$amount?>">
    <input type="hidden" name="success" value="<?=$callbackSuccess?>">
    <input type="hidden" name="fail" value="<?=$callbackFail?>">
    <input type="hidden" name="notify" value="<?=$callbackNotify?>">
    <button type="submit">Отказ в авторизации оплаты</button>
</form>

<form method="post">
    <input type="hidden" name="state" value="cancel">
    <input type="hidden" name="message" value="Отмена авторизации оплаты (клиент передумал)">
    <input type="hidden" name="orderNumber" value="<?=$orderNumber?>">
    <input type="hidden" name="mdOrder" value="<?=$mdOrder?>">
    <input type="hidden" name="amount" value="<?=$amount?>">
    <input type="hidden" name="success" value="<?=$callbackSuccess?>">
    <input type="hidden" name="fail" value="<?=$callbackFail?>">
    <input type="hidden" name="notify" value="<?=$callbackNotify?>">
    <button type="submit">Отмена авторизации оплаты (клиент передумал)</button>
</form>
