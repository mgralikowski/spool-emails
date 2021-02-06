<?php

use Foo\Mailer;

require_once 'vendor/autoload.php';
require_once 'src/config.php';

$mailer = new Mailer($smtp, $slots);

//$mailer->send('foo@example.com', 'receiver@samle.com', 'Message test normal', 'Message text (default)'); // default
$mailer->send('foo@example.com', 'receiver@samle.com', 'Message test normal', 'Message text (force normal)', Mailer::MESSAGE_NORMAL); // normal
$mailer->send('foo@example.com', 'receiver@samle.com', 'Message test immediate', 'Message text (force immediate)#' . random_int(0, 100), Mailer::MESSAGE_IMMEDIATE); // immediate
