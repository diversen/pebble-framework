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
    private $from = '';

    /**
     * Default fromName (name)
     */
    private $fromName = '';

    /**
     * Markdown safemode enabled
     */
    private $safeMode = true;


    /**
     * Set safemode if sending markdown emails
     */
    public function setSafeMode(bool $bool)
    {
        $this->safeMode = $bool;
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
     * @return PHPMailer\PHPMailer\PHPMailer
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
     */
    public function send(string $to, string $subject, string $text, string $html, array $attachments = [])
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

    private function getMarkdown(string $text)
    {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode($this->safeMode);
        $html = $parsedown->text($text);
        return $html;
    }

    /**
     * Send mail as markdown
     */
    public function sendMarkdown(string $to, string $subject, string $text, array $attachments = [])
    {
        $html = $this->getMarkdown($text);
        return $this->send($to, $subject, $text, $html, $attachments);
    }
}
