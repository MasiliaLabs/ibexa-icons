<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Tests;

use Masilia\Contracts\IbexaIcons\IconEntry;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\Contracts\IbexaIcons\InvalidIcon;
use Masilia\IbexaIcons\Icon\Sprite;

/** The registry without a database, with the same rules as the real one. */
final class InMemoryRegistry implements IconRegistry
{
    /** @var array<string, IconEntry> */
    private array $entries = [];

    public function __construct(private readonly ?Sprite $sprite = null)
    {
    }

    public function find(IconKind $kind, string $identifier): ?IconEntry
    {
        return $this->entries[$kind->value . ':' . $identifier] ?? null;
    }

    public function all(): array
    {
        return array_values($this->entries);
    }

    public function set(IconKind $kind, string $identifier, string $icon, IconSource $source): bool
    {
        if ($this->sprite !== null && !$this->sprite->has($icon)) {
            throw InvalidIcon::unknown($icon, $this->sprite->closest($icon));
        }
        $existing = $this->find($kind, $identifier);
        if ($existing !== null && !$source->canReplace($existing->source)) {
            return false;
        }
        $this->entries[$kind->value . ':' . $identifier] = new IconEntry($kind, $identifier, $icon, $source, new \DateTimeImmutable());

        return true;
    }

    public function remove(IconKind $kind, string $identifier): void
    {
        unset($this->entries[$kind->value . ':' . $identifier]);
    }
}
