<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons;

use Masilia\Bundle\IbexaIcons\Security\IconsPolicyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** The icons of the content types and groups, chosen at run time: the registry, and what lets Ibexa read it. */
final class MasiliaIbexaIconsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // The "masilia_icon" permissions: who may see the icons, who may change them.
        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $core */
        $core = $container->getExtension('ibexa');
        $core->addPolicyProvider(new IconsPolicyProvider());
    }
}
