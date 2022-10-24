<?php

return [
    'frontendUrl' => env('FRONTEND_URL'),

    'smtpFrom' => env('SMTP_USERNAME'),
    'smtpHost' => env('SMTP_HOST'),
    'smtpPort' => env('SMTP_PORT'),
    'smtpUsername' => env('SMTP_USERNAME'),
    'smtpPassword' => env('SMTP_PASSWORD'),

    'smscLogin' => env('SMSC_LOGIN'),
    'smscPassword' => env('SMSC_PASSWORD'),

    'telegramToken' => env('TELEGRAM_TOKEN'),

    'firebaseSenderId' => env('FIREBASE_SENDER_ID'),
    'firebaseServerKey' => env('FIREBASE_SERVER_KEY'),

    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
];
