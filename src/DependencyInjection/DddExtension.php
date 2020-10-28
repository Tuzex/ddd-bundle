<?php

declare(strict_types=1);

namespace Tuzex\Bundle\Ddd\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Tuzex\Ddd\Infrastructure\Domain\DomainEventHandler;

final class DddExtension extends Extension implements PrependExtensionInterface
{
    private FileLocator $fileLocator;

    public function __construct()
    {
        $this->fileLocator = new FileLocator(__DIR__.'/../Resources/config');
    }

    public function prepend(ContainerBuilder $containerBuilder): void
    {
        $configuration = new Configuration(false);
        $configs = $this->processConfiguration($configuration, $containerBuilder->getExtensionConfig('framework'));

        $containerBuilder->prependExtensionConfig('framework', [
            'messenger' => [
                'default_bus' => $configs['messenger']['default_bus'] ?? 'tuzex.domain.event_bus',
                'buses' => [
                    'tuzex.domain.event_bus' => [],
                ],
            ],
        ]);

        $containerBuilder->registerForAutoconfiguration(DomainEventHandler::class)
            ->addTag('tuzex.domain.event_handler')
            ->addTag('messenger.message_handler', [
                'bus' => 'tuzex.domain.event_bus',
            ]);
    }

    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $loader = new XmlFileLoader($containerBuilder, $this->fileLocator);
        $loader->load('services.xml');
    }
}
