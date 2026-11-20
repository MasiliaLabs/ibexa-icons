<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Icon;

use Masilia\Contracts\IbexaIcons\IconKind;

/**
 * The icons of the content types and groups that the Masilia packages create (the socle of masilia/ibexa-blocks and the groups of
 * its blocks). They are given by `masilia:icons:defaults` to what has no icon yet, as a choice of the lowest rank.
 */
final class DefaultIcons
{
    public const TYPES = [
        'home_page' => 'home',
        'page' => 'layout',
        'site' => 'sites',
        'layout_config' => 'settings',
        'hero_banner' => 'banner',
        'text_section' => 'text-paragraph',
        'info_cards' => 'view-grid',
        'info_card' => 'info-circle',
        'call_to_action' => 'cursor-clicked',
    ];

    public const GROUPS = [
        'Blocks' => 'folder',
        'Block items' => 'box-component',
        'Configurations' => 'settings',
        'Taxonomies' => 'tags',
    ];

    public static function for(IconKind $kind, string $identifier): ?string
    {
        return match ($kind) {
            IconKind::Type => self::TYPES[$identifier] ?? null,
            IconKind::Group => self::GROUPS[$identifier] ?? null,
        };
    }
}
