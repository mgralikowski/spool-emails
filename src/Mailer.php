<?php

declare(strict_types = 1);

namespace Foo;

use DateTime;
use Swift_Mailer;
use Swift_Message;
use Swift_FileSpool;
use Swift_Transport;
use Swift_IoException;
use Swift_SmtpTransport;
use Swift_SpoolTransport;

class Mailer
{
    public const MESSAGE_NORMAL = 'normal';

    public const MESSAGE_IMMEDIATE = 'immediate';

    private array $config;

    private array $slots;

    private string $storagePath = './../storage/spool';

    private Swift_Transport $transport;

    private Swift_Mailer $mailer;

    /**
     * Mailer constructor.
     *
     * @param array $config SMTP configuration. @see https://swiftmailer.symfony.com/docs/sending.html#using-the-smtp-transport
     * @param array $slots  Time slots to send emails in format HH:MM-HH:MM (range).
     */
    public function __construct($config = [], $slots = [])
    {
        $this->config = array_merge([
            'host' => 'smtp.mailtrap.io',
            'port' => 25,
        ], $config);

        $this->storagePath = __DIR__ . $this->storagePath;

        $this->slots = $slots;

        $this->initMailer();
    }

    /**
     * Setup swiftmailer config and base smtp transport.
     */
    private function initMailer(): void
    {
        $this->transport = (new Swift_SmtpTransport($this->config['host'], $this->config['port']))
            ->setUsername($this->config['username'])
            ->setPassword($this->config['password']);

        $this->mailer = new Swift_Mailer($this->transport);
    }

    /**
     * Send normal (default) or immediate type email.
     *
     * @param string|array $from
     * @param mixed        $to
     * @param string       $title
     * @param string       $text
     * @param string|null  $messageType
     * @return int
     * @throws Swift_IoException
     */
    public function send(array|string $from, mixed $to, string $title, string $text, string $messageType = null): int
    {
        $messageType ??= self::MESSAGE_NORMAL;

        $message = (new Swift_Message($title))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($text);

        if ($messageType === self::MESSAGE_IMMEDIATE) {
            return $this->sendImmediate($message);
        }

        return $this->sendNormal($message);
    }

    /**
     * Standard method to send email by smtp.
     *
     * @param  Swift_Message $message
     * @return int
     */
    private function sendNormal(Swift_Message $message): int
    {
        return $this->mailer->send($message);
    }

    /**
     * Send message with delay but do not wait if we are now in time slot.
     *
     * @param  Swift_Message     $message
     * @throws Swift_IoException
     * @return int
     */
    private function sendImmediate(Swift_Message $message): int
    {
        if ($this->isRightMomentToSendEmails()) {
            return $this->sendNormal($message);
        }

        $spool     = new Swift_FileSpool($this->storagePath);
        $transport = new Swift_SpoolTransport($spool);
        $mailer    = new Swift_Mailer($transport);

        return $mailer->send($message);
    }

    /**
     * Base method to send pending email.
     *
     * @throws Swift_IoException
     */
    public function sendPendingEmails(): void
    {
        if ($this->isRightMomentToSendEmails() === false) {
            // not yet!
            return;
        }

        $spool          = new Swift_FileSpool($this->storagePath);
        $spoolTransport = new Swift_SpoolTransport($spool);
        $spool          = $spoolTransport->getSpool();

        // @todo use setDate if is important to use real time instead of date of add to a que

        $spool->flushQueue($this->transport);
    }

    /**
     * Determine whether is right moment to send spooled/waiting messages.
     *
     * @param  null|DateTime $now
     * @return bool
     */
    private function isRightMomentToSendEmails($now = null): bool
    {
        $now ??= new DateTime();
        $nowTime = $now->format('H:i');

        foreach ($this->slots as $slot) {
            [$fromTime, $toTime] = explode('-', $slot);

            if ($nowTime >= $fromTime && $nowTime <= $toTime) {
                return true;
            }
        }

        return false;
    }
}
