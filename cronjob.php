<?php

use Foo\Mailer;

require_once 'vendor/autoload.php';
require_once 'src/config.php';

$mailer = new Mailer($smtp, $slots);
$mailer->sendPendingEmails();
