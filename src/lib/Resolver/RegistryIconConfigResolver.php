<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Resolver;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Exception\ParameterNotFoundException;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\IbexaIcons\Icon\GroupSlug;
use Masilia\IbexaIcons\Icon\Sprite;

/**
 * Answers the icon settings of Ibexa (`content_type.<id>` and `content_type_group.<id>`, with their `thumbnail`) from the registry,
 * ahead of the YAML. Ibexa asks its configuration resolvers in turn: this one is asked first, and says nothing about a type or a
 * group that has no icon in the registry, so the YAML and `default-config` still answer for it.
 *
 * It replaces nothing: the resolvers of Ibexa that read icons are final, but they ask this chain.
 */
final class RegistryIconConfigResolver implements ConfigResolverInterface
{
    private const DEFAULT_NAMESPACE = 'ibexa.site_access.config';

    private string $defaultNamespace = self::DEFAULT_NAMESPACE;

    private bool $bypassed = false;

    public function __construct(private readonly IconRegistry $registry)
    {
    }

    /**
     * Runs a function with the registry silent, to read what the YAML of the project says on its own.
     *
     * @template T
     *
     * @param callable(): T $read
     *
     * @return T
     */
    public function withoutRegistry(callable $read): mixed
    {
        $this->bypassed = true;
        try {
            return $read();
        } finally {
            $this->bypassed = false;
        }
    }

    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        return $this->thumbnail($paramName, $namespace) !== null;
    }

    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null)
    {
        $thumbnail = $this->thumbnail($paramName, $namespace);
        if ($thumbnail === null) {
            throw new ParameterNotFoundException($paramName, $namespace ?? $this->defaultNamespace, [$scope]);
        }

        return ['thumbnail' => $thumbnail];
    }

    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    private function thumbnail(string $paramName, ?string $namespace): ?string
    {
        if ($this->bypassed || ($namespace !== null && $namespace !== self::DEFAULT_NAMESPACE)) {
            return null;
        }
        if (str_starts_with($paramName, IconKind::Group->parameterPrefix())) {
            // Ibexa asks for the slug of the name of the group ("block_items" for "Block items").
            $slug = substr($paramName, strlen(IconKind::Group->parameterPrefix()));
            foreach ($this->registry->all() as $entry) {
                if ($entry->kind === IconKind::Group && GroupSlug::of($entry->identifier) === $slug) {
                    return Sprite::thumbnail($entry->icon);
                }
            }

            return null;
        }
        if (str_starts_with($paramName, IconKind::Type->parameterPrefix())) {
            $entry = $this->registry->find(IconKind::Type, substr($paramName, strlen(IconKind::Type->parameterPrefix())));

            return $entry === null ? null : Sprite::thumbnail($entry->icon);
        }

        return null;
    }
}
