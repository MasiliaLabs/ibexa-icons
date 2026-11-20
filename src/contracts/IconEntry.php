<?php

declare(strict_types=1);

namespace Masilia\Contracts\IbexaIcons;

/** The icon of a content type or of a group: the name of a symbol of the Ibexa sprite, and who chose it. */
final readonly class IconEntry
{
    public function __construct(
        public IconKind $kind,
        public string $identifier,
        public string $icon,
        public IconSource $source,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
