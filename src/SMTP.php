<?php

declare(strict_types=1);

namespace Pebble;

use PHPMailer\PHPMailer\PHPMailer;
use Parsedown;

class SMTP
{
    /**
     * Default SMTP from (email)
     */
    private string $from = '';

    /**
     * Default fromName (name)
     */
    private string $fromName = '';

    /**
     * Markdown safemode enabled
     */
    private bool $safeMode = true;

    /**
     * Settings
     * @var array<mixed> $settings
     */
    private array $settings = [];

    /**
     * Set safemode if sending markdown emails
     */
    public function setSafeMode(bool $bool): void
    {
        $this->safeMode = $bool;
    }

    /**
     * Set from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * Set from name
     */
    public function setFromName(string $from_name): void
    {
        $this->fromName = $from_name;
    }

    /**
     * Constructor take an settings array
     *
     * [
     * 'DefaultFrom' => 'mail@10kilobyte.com',
     * 'DefaultFromName' => 'Time Manager',
     * 'Host' => 'smtp-relay.sendinblue.com',
     * 'Port' => 587,
     * 'SMTPAuth' => true,
     * 'SMTPSecure' => 'tls',
     * 'Username' => 'username',
     * 'Password' => 'password',
     * 'SMTPDebug' => 0
     * ]
     * @param array <mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        if (!$settings['DefaultFrom'] || !$settings['DefaultFromName']) {
            throw new \Exception('Set DefaultFrom and DefaultFromName in config/SMTP.php');
        }

        $this->from = $settings['DefaultFrom'];
        $this->fromName = $settings['DefaultFromName'];
    }

    /**
     * Get PHPMailer object
     * Initialized from SMTP in Config folder
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    private function getPHPMailer()
    {
        $mail = new PHPMailer(true);

        // You don't need to catch configuration settings
        $mail->SMTPDebug = $this->settings['SMTPDebug'];
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $this->settings['Host'];
        $mail->SMTPAuth = $this->settings['SMTPAuth'];
        $mail->Username = $this->settings['Username'];
        $mail->Password = $this->settings['Password'];
        $mail->SMTPSecure = $this->settings['SMTPSecure'];
        $mail->Port = $this->settings['Port'];

        return $mail;
    }

    /**
     * This method sends a mail but catches the exception and return a boolean
     * @throws \Exception
     * @param array<string> $attachments
     */
    public function send(string $to, string $subject, string $text, string $html, array $attachments = []): void
    {
        $mail = $this->getPHPMailer();
        $mail->setFrom($this->from, $this->fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($this->from);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;

        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $mail->addAttachment($file);
            }
        }

        $mail->send();
    }

    private function getMarkdown(string $text): string
    {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode($this->safeMode);
        $html = $parsedown->text($text);
        return $html;
    }

    /**
     * Send mail as markdown
     * @param array<string> $attachments
     * @throws \Exception
     */
    public function sendMarkdown(string $to, string $subject, string $text, array $attachments = []): void
    {
        $html = $this->getMarkdown($text);
        $this->send($to, $subject, $text, $html, $attachments);
    }

    /**
     * Send both text and markdown
     * @param array<string> $attachments
     * @throws \Exception
     */
    public function sendTextMarkdown(string $to, string $subject, string $text, string $markdown, array $attachments = []): void
    {
        $html = $this->getMarkdown($markdown);
        $this->send($to, $subject, $text, $html, $attachments);
    }
}
