<?php
/**
 * @var array $methods
 */
?>
<table class="table" style="font-size: 14px;">
<?php foreach ($methods as $key => $method) { ?>
    <tr class="json_rpc_method card mb-1" style="cursor: pointer; background-color: rgb(33, 37, 41); color: white;">
        <td class="card-head" style="position: relative">
            <div style="position: absolute; bottom: 0; top: 0;">
                <div style="width: 24px; height: 24px; margin-top: -12px; top: 50%; position: absolute;">
                    <img src="/doc/assets/swagger/jrpc.svg"/>
                </div>
            </div>
            <div style="float: left; margin-left: 32px;">
                <?php echo $method['signature']; ?><br>
                <span class="hljs-comment"><?php echo $method['description'] ?? 'Описание метода'; ?></span>
            </div>
        </td>
        <td class="card-body" style="display: none;">
            <table style="background-color: rgb(33, 37, 41); color: white; width: 100%; border:none;">
                <tbody>
                <?php foreach ($method['params'] ?? [] as $param) { ?>
                    <tr>
                        <td class="p-1" width="40" style="min-width: 20px;"><img src="/doc/assets/swagger/param.svg"/></td>
                        <td class="p-1 hljs-keyword" style="width: 120px"><?php echo $param['type']; ?></td>
                        <td class="p-1 hljs-variable">$<?php echo $param['name']; ?></td>
                        <td class="p-1 hljs-title"><?php echo $param['description']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </td>
    </tr>
<?php } ?>
</table>

