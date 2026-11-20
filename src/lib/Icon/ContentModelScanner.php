<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Icon;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\IbexaIcons\Resolver\RegistryIconConfigResolver;

/**
 * Every content type and every content type group of the repository, with the icon each one has and where it comes from:
 * the registry (admin, ai, import, default), the YAML of the project ("config"), or nothing ("none": Ibexa shows its default icon).
 */
class ContentModelScanner
{
    public function __construct(
        private readonly Repository $repository,
        private readonly IconRegistry $registry,
        private readonly ConfigResolverInterface $configResolver,
        private readonly RegistryIconConfigResolver $registryResolver,
    ) {
    }

    /**
     * @return list<array{kind: IconKind, identifier: string, name: string, group: string, icon: ?string, source: string}>
     */
    public function scan(): array
    {
        $items = [];
        $this->repository->sudo(function (Repository $repository) use (&$items): void {
            $service = $repository->getContentTypeService();
            $seen = [];
            foreach ($service->loadContentTypeGroups() as $group) {
                $items[] = $this->item(IconKind::Group, $group->identifier, $group->identifier, '');
                foreach ($service->loadContentTypes($group) as $type) {
                    if (isset($seen[$type->identifier])) {
                        continue;
                    }
                    $seen[$type->identifier] = true;
                    $items[] = $this->item(IconKind::Type, $type->identifier, $type->getName() ?? $type->identifier, $group->identifier);
                }
            }
        });

        return $items;
    }

    /** @return list<string> the identifiers of the groups of the repository */
    public function groups(): array
    {
        return array_values(array_map(
            static fn (array $item): string => $item['identifier'],
            array_filter($this->scan(), static fn (array $item): bool => $item['kind'] === IconKind::Group),
        ));
    }

    public function exists(IconKind $kind, string $identifier): bool
    {
        foreach ($this->scan() as $item) {
            if ($item['kind'] === $kind && $item['identifier'] === $identifier) {
                return true;
            }
        }

        return false;
    }

    /** @return array{kind: IconKind, identifier: string, name: string, group: string, icon: ?string, source: string} */
    private function item(IconKind $kind, string $identifier, string $name, string $group): array
    {
        $entry = $this->registry->find($kind, $identifier);
        if ($entry !== null) {
            return ['kind' => $kind, 'identifier' => $identifier, 'name' => $name, 'group' => $group, 'icon' => $entry->icon, 'source' => $entry->source->value];
        }
        $configured = $this->registryResolver->withoutRegistry(fn (): ?string => $this->fromConfig($kind, $identifier));

        return ['kind' => $kind, 'identifier' => $identifier, 'name' => $name, 'group' => $group, 'icon' => $configured, 'source' => $configured === null ? 'none' : 'config'];
    }

    private function fromConfig(IconKind $kind, string $identifier): ?string
    {
        $parameter = $kind->parameterPrefix() . ($kind === IconKind::Group ? GroupSlug::of($identifier) : $identifier);
        if (!$this->configResolver->hasParameter($parameter)) {
            return null;
        }
        $thumbnail = ((array) $this->configResolver->getParameter($parameter))['thumbnail'] ?? null;

        return is_string($thumbnail) && $thumbnail !== '' ? (Sprite::iconOf($thumbnail) ?? $thumbnail) : null;
    }
}
