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
 * Class EmailFactory
 * @package MBtec\LaminasEmail\Service
 */
class EmailFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Email
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Email
    {
        /** @var Renderer $renderer */
        $renderer = $container->get(Renderer::class);

        /** @var Message $message */
        $message = $container->get(Message::class);

        /** @var Transport $transport */
        $transport = $container->get(Transport::class);

        return new Email($renderer, $message, $transport);
    }
}
