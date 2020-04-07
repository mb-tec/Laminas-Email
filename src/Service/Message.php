<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail\Service;

use Laminas\Mail;
use Laminas\Mime;

/**
 * Class Message
 * @package MBtec\LaminasEmail\Service
 */
class Message
{
    /**
     * @var array
     */
    private $config;

    /**
     * Message constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $mailData
     * @param array $options
     * @param array $receivers
     * @param array $attachments
     * @return Mail\Message
     */
    public function createMessage(
        array $mailData,
        array $options,
        array $receivers,
        array $attachments = []
    ): Mail\Message {
        $message = new Mail\Message();
        $message->setEncoding('UTF-8');

        $this
            ->setFrom($message, $options)
            ->setTo($message, $receivers)
            ->addBcc($message)
            ->setSubject($message, $mailData['subject'])
            ->setBody($message, $attachments, $mailData['html'], $mailData['plain']);

        return $message;
    }

    /**
     * @param Mail\Message $message
     * @param array $options
     * @return Message
     */
    protected function setFrom(Mail\Message $message, array $options): Message
    {
        $senderData = $this->getSenderData($options);

        $addressList = new Mail\AddressList();
        $addressList->add(
            new Mail\Address($senderData['email'], ($senderData['name'] ?? null))
        );

        $message->setFrom($addressList);

        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getSenderData(array $options): array
    {
        if (isset($options['use_default_sender']) && $options['use_default_sender']) {
            $fromEmail = (isset($this->config['mail_from_email']) && !empty($this->config['mail_from_email']))
                ? $this->config['mail_from_email']
                : null;

            $fromName = (isset($this->config['mail_from_name']) && !empty($this->config['mail_from_name']))
                ? $this->config['mail_from_name']
                : null;

            return [
                'email' => $fromEmail,
                'name' => $fromName,
            ];
        }

        $fromEmail = $options['sender_mail'] ?? null;
        $fromName = $options['sender_name'] ?? null;

        return [
            'email' => $fromEmail,
            'name' => $fromName,
        ];
    }

    /**
     * @param Mail\Message $message
     * @param array $receivers
     * @return Message
     */
    protected function setTo(Mail\Message $message, array $receivers): Message
    {
        $addressList = new Mail\AddressList();

        foreach ($receivers as $receiver) {
            $recEmail = (isset($this->config['email_receiver_override'])
                && !empty($this->config['email_receiver_override']))
                ? $this->config['email_receiver_override']
                : $receiver[0];

            $recName = $receiver[1] ?? null;

            $addressList->add(
                new Mail\Address($recEmail, $recName)
            );
        }

        $message->setTo($addressList);

        return $this;
    }

    /**
     * @param Mail\Message $message
     * @return Message
     */
    protected function addBcc(Mail\Message $message): Message
    {
        if (isset($this->config['mail_bcc']) && is_array($this->config['mail_bcc'])) {
            foreach ($this->config['mail_bcc'] as $bcc) {
                // Avoid sending email copy to "to" AND bcc
                $found = false;
                foreach ($message->getTo() as $to) {
                    if ($to->getEmail() == $bcc) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $addressList = new Mail\AddressList();
                    $addressList->add(new Mail\Address($bcc));
                    $message->addBcc($addressList);
                }
            }
        }

        return $this;
    }

    /**
     * @param Mail\Message $message
     * @param string $subject
     * @return Message
     */
    protected function setSubject(Mail\Message $message, string $subject): Message
    {
        if (isset($this->config['subject_prefix']) && $this->config['subject_prefix'] != '') {
            $subject = $this->config['subject_prefix'] . $subject;
        }

        $message->setSubject($subject);

        return $this;
    }

    /**
     * @param Mail\Message $message
     * @param array $attachments
     * @param string $html
     * @param string $plain
     * @return Message
     */
    protected function setBody(Mail\Message $message, array $attachments, string $html, string $plain): Message
    {
        if (empty($attachments)) {
            $this->setBodyWithoutAttachments($message, $html, $plain);

            return $this;
        }

        $this->setBodyWithAttachments($message, $attachments, $html, $plain);

        return $this;
    }

    /**
     * @param Mail\Message $message
     * @param string $html
     * @param string $plain
     * @return Message
     */
    protected function setBodyWithoutAttachments(Mail\Message $message, string $html, string $plain): Message
    {
        $content = $this->getContentMessage($html, $plain);
        $message->setBody($content);

        $headers = $message->getHeaders();
        $headers->get('Content-Type')->setType(Mime\Mime::MULTIPART_ALTERNATIVE);

        return $this;
    }

    /**
     * @param Mail\Message $message
     * @param array $attachments
     * @param string $html
     * @param string $plain
     * @return $this
     */
    protected function setBodyWithAttachments(
        Mail\Message $message,
        array $attachments,
        string $html,
        string $plain
    ): Message {
        $content = $this->getContentMessage($html, $plain);

        $contentPart = new Mime\Part($content->generateMessage());
        $contentPart->setType(Mime\Mime::MULTIPART_ALTERNATIVE);
        $contentPart->setBoundary($content->getMime()->boundary());

        $body = new Mime\Message();
        $body->addPart($contentPart);

        foreach ($attachments as $attachmentData) {
            if (isset($attachmentData['file_path']) && is_readable($attachmentData['file_path'])) {
                $attachmentData['file_data'] = file_get_contents($attachmentData['file_path']);
            }

            if (isset($attachmentData['file_data']) && $attachmentData['file_data'] != '') {
                $attachment = new Mime\Part();
                $attachment
                    ->setContent($attachmentData['file_data'])
                    ->setType(Mime\Mime::TYPE_OCTETSTREAM)
                    ->setFileName($attachmentData['name'])
                    ->setDisposition(Mime\Mime::DISPOSITION_ATTACHMENT)
                    ->setEncoding(Mime\Mime::ENCODING_BASE64);

                $body->addPart($attachment);
            }
        }

        $message->setBody($body);

        return $this;
    }

    /**
     * @param string $html
     * @param string $plain
     * @return Mime\Message
     */
    protected function getContentMessage(string $html, string $plain): Mime\Message
    {
        $message = new Mime\Message();

        if ($plain != '') {
            $plainPart = new Mime\Part();
            $plainPart
                ->setContent($plain)
                ->setType(Mime\Mime::TYPE_TEXT)
                ->setCharset('UTF-8')
                ->setEncoding(Mime\Mime::ENCODING_QUOTEDPRINTABLE);

            $message->addPart($plainPart);
        }

        if ($html != '') {
            $htmlPart = new Mime\Part();
            $htmlPart
                ->setContent($html)
                ->setType(Mime\Mime::TYPE_HTML)
                ->setCharset('UTF-8')
                ->setEncoding(Mime\Mime::ENCODING_QUOTEDPRINTABLE);

            $message->addPart($htmlPart);
        }

        return $message;
    }
}
