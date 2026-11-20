<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Masilia\IbexaIcons\Icon\Sprite;
use Masilia\IbexaIcons\Tests\SpriteFactory;
use PHPUnit\Framework\TestCase;

final class SpriteTest extends TestCase
{
    public function testItReadsEverySymbolEvenWhenTheTagIsSpreadOverLines(): void
    {
        self::assertSame(['calendar', 'calendar-check', 'folder', 'home', 'text-paragraph', 'user-add'], SpriteFactory::create()->ids());
    }

    public function testItKnowsAnIconFromANameThatIsNotOne(): void
    {
        $sprite = SpriteFactory::create();

        self::assertTrue($sprite->has('home'));
        self::assertFalse($sprite->has('house'));
    }

    public function testASearchFindsWholeNamesBeforeParts(): void
    {
        self::assertSame(['calendar', 'calendar-check'], SpriteFactory::create()->search('calendar'));
        self::assertSame(['user-add'], SpriteFactory::create()->search('add a user'));
        self::assertSame([], SpriteFactory::create()->search('   '));
    }

    public function testTheClosestIconsHelpWithATypo(): void
    {
        self::assertContains('calendar', SpriteFactory::create()->closest('calender'));
    }

    public function testAThumbnailIsThePathOfTheSpriteAndTheName(): void
    {
        self::assertSame('/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg#home', Sprite::thumbnail('home'));
        self::assertSame('home', Sprite::iconOf(Sprite::thumbnail('home')));
        self::assertNull(Sprite::iconOf('/assets/images/poll.svg'));
        self::assertNull(Sprite::iconOf('/x.svg#'));
    }

    public function testWithoutTheSpriteThereAreNoIconsInsteadOfAnError(): void
    {
        $sprite = new Sprite('/nowhere', new \Symfony\Component\Cache\Adapter\ArrayAdapter());

        self::assertSame([], $sprite->ids());
    }
}
