<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class MessageFactory
 * @package MBtec\LaminasEmail\Service
 */
class MessageFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Message
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Message
    {
        $config = $container->get('config')['mbtec']['laminas-email']['renderer'];

        return new Message($config);
    }
}
