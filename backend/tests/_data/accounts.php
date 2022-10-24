<?php

return [
    'account1' => [
        'id' => '39758297-9c4f-4368-af06-ce52e61c3db2',
        'status_cid' => '1',
        'username' => 'account1',
        'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
        'auth_key' => Yii::$app->security->generateRandomString(),
    ],
    'agent_account' => [
        'id' => '39758297-9c4f-4368-af06-ce52e61c3db3',
        'status_cid' => '1',
        'username' => 'agent',
        'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
        'auth_key' => Yii::$app->security->generateRandomString(),
    ],
];
