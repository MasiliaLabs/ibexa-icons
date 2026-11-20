<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\IbexaIcons\Icon\LegacyIcons;
use PHPUnit\Framework\TestCase;

final class LegacyIconsTest extends TestCase
{
    public function testItReadsTheThumbnailsOfTypesAndGroupsInEveryScope(): void
    {
        $found = LegacyIcons::read(['ibexa' => ['system' => [
            'admin_group' => [
                'content_type_group' => ['blocks' => ['thumbnail' => '/x/all-icons.svg#folder']],
                'content_type' => [
                    'home_page' => ['thumbnail' => '/x/all-icons.svg#home'],
                    'poll' => ['thumbnail' => '/assets/poll.svg'],
                ],
            ],
            'default' => ['content_type' => ['article' => ['thumbnail' => '/x/all-icons.svg#news']]],
        ]]]);

        self::assertSame([
            ['kind' => IconKind::Type, 'identifier' => 'home_page', 'icon' => 'home'],
            ['kind' => IconKind::Group, 'identifier' => 'blocks', 'icon' => 'folder'],
            ['kind' => IconKind::Type, 'identifier' => 'article', 'icon' => 'news'],
        ], $found);
    }

    public function testAFileWithoutIconsGivesNothing(): void
    {
        self::assertSame([], LegacyIcons::read([]));
        self::assertSame([], LegacyIcons::read(['ibexa' => ['system' => ['default' => ['content_type' => null]]]]));
    }

    public function testAGroupKeyMatchesTheNameOfTheGroupEvenWrittenWithUnderscores(): void
    {
        $groups = ['Content', 'Block items', 'Blocks'];

        self::assertSame('Block items', LegacyIcons::matchGroup('block_items', $groups));
        self::assertSame('Blocks', LegacyIcons::matchGroup('blocks', $groups));
        self::assertSame('Block items', LegacyIcons::matchGroup('Block items', $groups));
        self::assertNull(LegacyIcons::matchGroup('taxonomies', $groups));
    }

    public function testAGroupNameIsSluggedTheWayIbexaDoesIt(): void
    {
        self::assertSame('block_items', \Masilia\IbexaIcons\Icon\GroupSlug::of('Block items'));
        self::assertSame('contenu_edite', \Masilia\IbexaIcons\Icon\GroupSlug::of('Contenu édité'));
    }
}
