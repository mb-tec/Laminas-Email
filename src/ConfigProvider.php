<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     Commercial
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                Service\Email::class => Service\EmailFactory::class,
                Service\Message::class => Service\MessageFactory::class,
                Service\Renderer::class => Service\RendererFactory::class,
                Service\Transport::class => Service\TransportFactory::class,
            ],
        ];
    }
}
