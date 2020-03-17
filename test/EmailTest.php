<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

namespace MBtec\LaminasEmail\Test;

use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplateMapResolver;
use MBtec\LaminasEmail\Service;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class EmailTest
 * @package MBtec\LaminasEmail\Test
 */
class EmailTest extends TestCase
{
    /**
     *
     */
    protected function setUp()
    {
        if (!isset($GLOBALS['receiver'])) {
            $GLOBALS['receiver'] = 'info@mb-tec.eu';
        }

        if (!isset($GLOBALS['receiver_override'])) {
            $GLOBALS['receiver_override'] = 'nite74@web.de';
        }

        if (!isset($GLOBALS['receiver_bcc'])) {
            $GLOBALS['receiver_bcc'] = null;
        }
    }

    /**
     *
     */
    public function testSimpleMail()
    {
        $config = [
            'mbtec' => [
                'laminas-email' => [
                    'transport' => [
                        'type' => 'sendmail',
                    ],
                    'renderer' => [
                        'subject_prefix' => 'Test: ',
                        'mail_from_name' => 'Test',
                        'mail_from_email' => 'info@mb-tec.eu',
                        'mail_bcc' => [
                            $GLOBALS['receiver_bcc'],
                        ],
                    ],
                ],
            ],
        ];

        $success = false;
        try {
            $this->getEmailService($config)
                ->addReceiver($GLOBALS['receiver'])
                ->setTemplate('test/email/simpleMail')
                ->send();

            $success = true;
        } catch (\Exception $ex) {
        }

        $this->assertEquals($success, true);
    }

    /**
     *
     */
    public function testMailOverride()
    {
        $config = [
            'mbtec' => [
                'laminas-email' => [
                    'transport' => [
                        'type' => 'sendmail',
                    ],
                    'renderer' => [
                        'subject_prefix' => 'Test: ',
                        'mail_from_name' => 'Test',
                        'mail_from_email' => 'info@mb-tec.eu',
                        'mail_bcc' => [
                            $GLOBALS['receiver_bcc'],
                        ],
                        'email_receiver_override' => $GLOBALS['receiver_override'],
                    ],
                ],
            ],
        ];

        $success = false;
        try {
            $this->getEmailService($config)
                ->addReceiver($GLOBALS['receiver'])
                ->setTemplate('test/email/simpleMailOverride')
                ->send();

            $success = true;
        } catch (\Exception $ex) {
        }

        $this->assertEquals($success, true);
    }

    /**
     *
     */
    public function testMailAttachments()
    {
        $config = [
            'mbtec' => [
                'laminas-email' => [
                    'transport' => [
                        'type' => 'sendmail',
                    ],
                    'renderer' => [
                        'subject_prefix' => 'Test: ',
                        'mail_from_name' => 'Test',
                        'mail_from_email' => 'info@mb-tec.eu',
                    ],
                ],
            ],
        ];

        $success = false;
        try {
            $this->getEmailService($config)
                ->addReceiver($GLOBALS['receiver'])
                ->setTemplate('test/email/simpleMailAttachments')
                ->addAttachmentData('HELLOWORLD.txt', file_get_contents(__DIR__ . '/attachments/HELLOWORLD.txt'))
                ->addAttachmentFile('HELLOWORLD.pdf', __DIR__ . '/attachments/HELLOWORLD.pdf')
                ->addAttachmentFile('HELLOWORLD.odt', __DIR__ . '/attachments/HELLOWORLD.odt')
                ->send();

            $success = true;
        } catch (\Exception $ex) {
        }

        $this->assertEquals($success, true);
    }

    /**
     * @param array $config
     * @return Service\Email
     */
    protected function getEmailService(array $config): Service\Email
    {
        $rendererConfig = $config['mbtec']['laminas-email']['renderer'];
        $transportConfig = $config['mbtec']['laminas-email']['transport'];

        $tplMap = [
            'test/email/simpleMail' => __DIR__ . '/email/simpleMail.phtml',
            'test/email/simpleMailOverride' => __DIR__ . '/email/simpleMailOverride.phtml',
            'test/email/simpleMailAttachments' => __DIR__ . '/email/simpleMailAttachments.phtml',
            'test/email/_footer' => __DIR__ . '/email/_footer.phtml',
        ];

        $resolver = new TemplateMapResolver($tplMap);

        $viewRenderer = new PhpRenderer();
        $viewRenderer->setResolver($resolver);

        $renderer = new Service\Renderer($viewRenderer);
        $message = new Service\Message($rendererConfig);
        $transport = new Service\Transport($transportConfig);

        return new Service\Email($renderer, $message, $transport);
    }
}
