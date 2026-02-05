<?php

return [
    'recipients' => array_filter(array_map('trim', explode(',', env('REPORT_EMAIL_RECIPIENTS', '')))),
    'subject'    => env('REPORT_MAIL_SUBJECT', 'Daily Export Report'),
];