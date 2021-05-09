<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'freeAmount' => 1000,
    'freeAmountCurrency' => 'EUR',
    'freeOperationCount' => 3,
    'inputPath' => DIRECTORY_SEPARATOR . 'input' . DIRECTORY_SEPARATOR,
    'userTypes' => ['type1' => 'private', 'type2' => 'business'],
    'operationTypes' => ['type1' => 'deposit', 'type2' => 'withdraw'],
    'commissions' => [
        'private' => ['deposit' => 0.03, 'withdraw' => 0.3],
        'business' => ['deposit' => 0.03, 'withdraw' => 0.5]
    ],
    'currencies' => ['EUR', 'USD', 'JPY', 'UAH']
];
