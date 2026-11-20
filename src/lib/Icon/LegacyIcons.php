<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Icon;

use Masilia\Contracts\IbexaIcons\IconKind;

/**
 * Reads the icons out of the YAML that Ibexa uses (`ibexa.system.<scope>.content_type.<id>.thumbnail` and
 * `content_type_group.<id>.thumbnail`), to bring a project that has them into the registry.
 */
final class LegacyIcons
{
    /**
     * @param array<string, mixed> $config the parsed file
     *
     * @return list<array{kind: IconKind, identifier: string, icon: string}>
     */
    public static function read(array $config): array
    {
        $found = [];
        foreach ((array) ($config['ibexa']['system'] ?? []) as $scope) {
            foreach ([[IconKind::Type, 'content_type'], [IconKind::Group, 'content_type_group']] as [$kind, $key]) {
                foreach ((array) (($scope[$key] ?? null) ?: []) as $identifier => $settings) {
                    $icon = is_array($settings) && is_string($settings['thumbnail'] ?? null) ? Sprite::iconOf($settings['thumbnail']) : null;
                    if ($icon !== null) {
                        $found[] = ['kind' => $kind, 'identifier' => (string) $identifier, 'icon' => $icon];
                    }
                }
            }
        }

        return $found;
    }

    /**
     * The group of the repository that a YAML key stands for: Ibexa writes the name of a group slugged in its settings, so the key
     * "block_items" is the group "Block items". The name itself is accepted too.
     *
     * @param list<string> $groups the identifiers of the groups of the repository
     */
    public static function matchGroup(string $key, array $groups): ?string
    {
        if (in_array($key, $groups, true)) {
            return $key;
        }
        foreach ($groups as $group) {
            if (GroupSlug::of($group) === GroupSlug::of($key)) {
                return $group;
            }
        }

        return null;
    }
}
