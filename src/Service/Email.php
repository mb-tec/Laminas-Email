<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail\Service;

/**
 * Class Email
 * @package MBtec\LaminasEmail\Service
 */
class Email
{
    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var array
     */
    private $receivers = [];

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * @var string
     */
    private $tpl;

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @var array
     */
    private $options = [
        'use_default_sender' => true,
        'add_footer' => true,
    ];

    /**
     * Email constructor.
     * @param Renderer $renderer
     * @param Message $message
     * @param Transport $transport
     */
    public function __construct(Renderer $renderer, Message $message, Transport $transport)
    {
        $this->renderer = $renderer;
        $this->message = $message;
        $this->transport = $transport;
    }

    /**
     * @param string $email
     * @param string|null $name
     * @return Email
     */
    public function addReceiver(string $email, string $name = null): Email
    {
        $receiver = [$email];
        if (is_string($name)) {
            $receiver[] = $name;
        }

        $this->receivers[] = $receiver;

        return $this;
    }

    /**
     * @param bool $bool
     * @return Email
     */
    public function setDefaultSender(bool $bool): Email
    {
        $this->options['use_default_sender'] = $bool;

        return $this;
    }

    /**
     * @param string $senderMail
     * @param string|null $senderName
     * @return Email
     */
    public function setSender(string $senderMail, string $senderName = null): Email
    {
        $this->options['sender_mail'] = (string)$senderMail;

        if ($senderName) {
            $this->options['sender_name'] = (string)$senderName;
        }

        return $this;
    }

    /**
     * @param string $tpl
     * @return Email
     */
    public function setTemplate(string $tpl): Email
    {
        $this->tpl = $tpl;

        return $this;
    }

    /**
     * @return Email
     */
    public function addDefaultFooter(): Email
    {
        $this->setOption('add_footer', true);

        return $this;
    }

    /**
     * @param $var
     * @param $val
     * @return Email
     */
    public function setVariable($var, $val): Email
    {
        $this->variables[$var] = $val;

        return $this;
    }

    /**
     * @param $var
     * @param $val
     * @return Email
     */
    public function setOption($var, $val): Email
    {
        $this->options[$var] = $val;

        return $this;
    }

    /**
     * @param string $fileName
     * @param string $filePath
     * @return Email
     */
    public function addAttachmentFile(string $fileName, string $filePath): Email
    {
        $this->attachments[] = [
            'file_path' => $filePath,
            'name' => $fileName,
        ];

        return $this;
    }

    /**
     * @param string $fileName
     * @param string $fileData
     * @return Email
     */
    public function addAttachmentData(string $fileName, string $fileData): Email
    {
        $this->attachments[] = [
            'file_data' => $fileData,
            'name' => $fileName,
        ];

        return $this;
    }

    /**
     * @return Email
     */
    public function send(): Email
    {
        $mailData = $this->renderer->renderTemplate($this->tpl, $this->variables, $this->options);
        $message = $this->message->createMessage($mailData, $this->options, $this->receivers, $this->attachments);

        $this->transport->send($message);
        $this->reset();

        return $this;
    }

    /**
     * @return Email
     */
    public function reset(): Email
    {
        $this->tpl = '';
        $this->variables = [];
        $this->options = [
            'use_default_sender' => true,
            'add_footer' => true,
        ];
        $this->receivers = [];
        $this->attachments = [];

        return $this;
    }
}
