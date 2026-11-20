<?php

declare(strict_types=1);

namespace Masilia\Bundle\IbexaIcons\Security;

use Ibexa\Bundle\Core\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

/** Who may see the icons and who may change them, as Ibexa policies of the module "masilia_icon": given by role in the admin. */
final class IconsPolicyProvider extends YamlPolicyProvider
{
    protected function getFiles(): array
    {
        return [__DIR__ . '/../Resources/config/policies.yaml'];
    }
}
