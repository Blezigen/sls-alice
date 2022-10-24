<form id='payment_form' action='<?= $psb_url ?>' method='POST'>
    <?php foreach ($data as $param => $value) : ?>
        <input type='hidden' name='<?= strtoupper($param); ?>' value='<?=$value ?>'/>
    <?php endforeach; ?>
    <input type='submit' name='SUBMIT' value='Перейти к оплате'/>
</form>
Если не произошло автоматического перенаправления, нажмите на кнопку 'Перейти к оплате'
<script type='text/javascript'>
    document.getElementById('payment_form').submit();
</script>