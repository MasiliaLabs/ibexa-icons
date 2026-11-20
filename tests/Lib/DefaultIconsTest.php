<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\IbexaIcons\Icon\DefaultIcons;
use PHPUnit\Framework\TestCase;

final class DefaultIconsTest extends TestCase
{
    public function testEveryDefaultIsAnIconName(): void
    {
        foreach ([...DefaultIcons::TYPES, ...DefaultIcons::GROUPS] as $icon) {
            self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $icon);
        }
    }

    public function testItKnowsTheSocleOfTheBlocksAndNothingElse(): void
    {
        self::assertSame('home', DefaultIcons::for(IconKind::Type, 'home_page'));
        self::assertSame('folder', DefaultIcons::for(IconKind::Group, 'Blocks'));
        self::assertNull(DefaultIcons::for(IconKind::Type, 'article'));
    }
}
