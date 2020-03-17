<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

use MBtec\LaminasEmail\Service;

return [
    'mbtec' => [
        'laminas-email' => [
            'transport' => [
                'type' => 'sendmail',
            ],
            'renderer' => [
                'subject_prefix' => '',
                'mail_from_name' => '',
                'mail_from_email' => '',
                'mail_bcc' => [

                ],
                'email_receiver_override' => '',
            ],
            'error_subject_prepend_text' => '',
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\Email::class => Service\EmailFactory::class,
            Service\Message::class => Service\MessageFactory::class,
            Service\Renderer::class => Service\RendererFactory::class,
            Service\Transport::class => Service\TransportFactory::class,
        ],
        'aliases' => [
            'mbtec-email-service' => Service\Email::class,
            'mbtec-email-message' => Service\Message::class,
            'mbtec-email-renderer' => Service\Renderer::class,
            'mbtec-email-transport' => Service\Transport::class,
        ],
    ],
];
