<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail\Service;

use Laminas\Mail\Message as LaminasMessage;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\TransportInterface;
use Exception;

/**
 * Class Transport
 * @package MBtec\LaminasEmail\Service
 */
class Transport
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * Transport constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param LaminasMessage $message
     * @return mixed
     */
    public function send(LaminasMessage $message)
    {
        return $this->getTransport()->send($message);
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        if (!is_object($this->transport)) {
            try {
                $typeClass = sprintf('\Laminas\Mail\Transport\%s', ucfirst($this->config['type']));

                if (!class_exists($typeClass)) {
                    throw new Exception('E-Mail transport class does not exist');
                }

                $this->transport = new $typeClass();

                if (isset($this->config['options'])) {
                    $optionsClass = $typeClass . 'Options';
                    if (class_exists($optionsClass) && method_exists($this->transport, 'setOptions')) {
                        $this->transport->setOptions(
                            new $optionsClass($this->config['options'])
                        );
                    }
                }
            } catch (\Exception $ex) {
                $this->transport = new Sendmail();
            }
        }

        return $this->transport;
    }
}
