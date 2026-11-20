<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests\Lib;

use Masilia\Contracts\IbexaIcons\IconSource;
use PHPUnit\Framework\TestCase;

final class IconSourceTest extends TestCase
{
    public function testAPersonIsNeverOverriddenByTheAssistantAnImportOrTheDefaults(): void
    {
        self::assertFalse(IconSource::Ai->canReplace(IconSource::Admin));
        self::assertFalse(IconSource::Import->canReplace(IconSource::Admin));
        self::assertFalse(IconSource::Default->canReplace(IconSource::Admin));
        self::assertTrue(IconSource::Admin->canReplace(IconSource::Admin));
    }

    public function testTheAssistantReplacesAnImportAndTheDefaultsButNotTheReverse(): void
    {
        self::assertTrue(IconSource::Ai->canReplace(IconSource::Import));
        self::assertTrue(IconSource::Import->canReplace(IconSource::Default));
        self::assertFalse(IconSource::Default->canReplace(IconSource::Ai));
        self::assertFalse(IconSource::Default->canReplace(IconSource::Import));
    }
}
