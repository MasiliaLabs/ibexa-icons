<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\DependencyInjection;

use Masilia\Contracts\IbexaAi\Os\Capability;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class MasiliaIbexaIconsExtension extends Extension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'masilia_ibexa_icons';
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'MasiliaIbexaIcons' => [
                        'type' => 'attribute',
                        'is_bundle' => false,
                        'dir' => __DIR__ . '/../../lib/Persistence/Entity',
                        'prefix' => 'Masilia\IbexaIcons\Persistence\Entity',
                        'alias' => 'MasiliaIbexaIcons',
                    ],
                ],
            ],
        ]);
        // The texts of the icons page (React) come from the translation domain of this bundle, published to the scripts of the admin.
        $container->prependExtensionConfig('bazinga_js_translation', ['active_domains' => ['masilia_ibexa_icons']]);
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Masilia\\Bundle\\IbexaIcons\\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        // The capabilities of the assistant only when masilia/ibexa-ai is there to run them.
        if (interface_exists(Capability::class)) {
            $loader->load('capabilities.yaml');
        }
    }
}
