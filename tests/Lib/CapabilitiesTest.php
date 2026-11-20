<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Masilia\Contracts\IbexaAi\Os\Risk;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\IbexaIcons\Capability\IconsListCapability;
use Masilia\IbexaIcons\Capability\IconsSearchCapability;
use Masilia\IbexaIcons\Capability\IconsSetCapability;
use Masilia\IbexaIcons\Icon\ContentModelScanner;
use Masilia\IbexaIcons\Tests\InMemoryRegistry;
use Masilia\IbexaIcons\Tests\SpriteFactory;
use PHPUnit\Framework\TestCase;

final class CapabilitiesTest extends TestCase
{
    /** @return list<array{kind: IconKind, identifier: string, name: string, group: string, icon: ?string, source: string}> */
    private static function items(): array
    {
        return [
            ['kind' => IconKind::Group, 'identifier' => 'Blocks', 'name' => 'Blocks', 'group' => '', 'icon' => null, 'source' => 'none'],
            ['kind' => IconKind::Type, 'identifier' => 'event', 'name' => 'Event', 'group' => 'Content', 'icon' => null, 'source' => 'none'],
            ['kind' => IconKind::Type, 'identifier' => 'page', 'name' => 'Page', 'group' => 'Content', 'icon' => 'folder', 'source' => 'admin'],
        ];
    }

    private function scanner(): ContentModelScanner
    {
        $scanner = $this->createMock(ContentModelScanner::class);
        $scanner->method('scan')->willReturn(self::items());

        return $scanner;
    }

    public function testListingCanKeepOnlyWhatHasNoIcon(): void
    {
        $result = (new IconsListCapability($this->scanner()))->execute(['missing_only' => true]);

        self::assertSame(2, $result->data['total']);
        self::assertSame(['Blocks', 'event'], array_column($result->data['items'], 'identifier'));
        self::assertSame(['group', 'type'], array_column($result->data['items'], 'kind'));
    }

    public function testListingCanKeepOneKind(): void
    {
        $result = (new IconsListCapability($this->scanner()))->execute(['kind' => 'type']);

        self::assertSame(2, $result->data['total']);
    }

    public function testSearchGivesIconNamesAndRefusesAnEmptyQuery(): void
    {
        $capability = new IconsSearchCapability(SpriteFactory::create());

        self::assertSame(['calendar', 'calendar-check'], $capability->execute(['query' => 'calendar'])->data['icons']);
        self::assertNotNull($capability->validate(['query' => '  ']));
        self::assertSame(Risk::READ, $capability->risk());
    }

    public function testSettingNeedsApprovalAndChecksTheTypeAndTheIcon(): void
    {
        $capability = new IconsSetCapability(new InMemoryRegistry(), $this->scanner(), SpriteFactory::create());

        self::assertSame(Risk::WRITE, $capability->risk());
        self::assertNull($capability->validate(['icons' => [['kind' => 'type', 'identifier' => 'event', 'icon' => 'calendar']]]));
        self::assertStringContainsString('no type "nope"', (string) $capability->validate(['icons' => [['kind' => 'type', 'identifier' => 'nope', 'icon' => 'calendar']]]));
        self::assertStringContainsString('Closest: calendar', (string) $capability->validate(['icons' => [['kind' => 'type', 'identifier' => 'event', 'icon' => 'calender']]]));
        self::assertStringContainsString('"type" or "group"', (string) $capability->validate(['icons' => [['kind' => 'page', 'identifier' => 'event', 'icon' => 'calendar']]]));
    }

    public function testThePreviewShowsEachChoiceWithItsReason(): void
    {
        $capability = new IconsSetCapability(new InMemoryRegistry(), $this->scanner(), SpriteFactory::create());
        $text = $capability->preview(['icons' => [['kind' => 'type', 'identifier' => 'event', 'icon' => 'calendar', 'reason' => 'it has a date']]])->toApprovalText(Risk::WRITE);

        self::assertStringContainsString('type event: calendar (it has a date)', $text);
    }

    public function testTheAssistantSetsIconsButKeepsWhatAPersonChose(): void
    {
        $sprite = SpriteFactory::create();
        $registry = new InMemoryRegistry($sprite);
        $registry->set(IconKind::Type, 'page', 'folder', IconSource::Admin);
        $capability = new IconsSetCapability($registry, $this->scanner(), $sprite);

        $result = $capability->execute(['icons' => [
            ['kind' => 'type', 'identifier' => 'event', 'icon' => 'calendar'],
            ['kind' => 'type', 'identifier' => 'page', 'icon' => 'home'],
        ]]);

        self::assertTrue($result->success);
        self::assertSame(['type event: calendar'], $result->data['set']);
        self::assertCount(1, $result->data['skipped']);
        self::assertSame('calendar', $registry->find(IconKind::Type, 'event')?->icon);
        self::assertSame(IconSource::Ai, $registry->find(IconKind::Type, 'event')?->source);
        self::assertSame('folder', $registry->find(IconKind::Type, 'page')?->icon);
    }
}
