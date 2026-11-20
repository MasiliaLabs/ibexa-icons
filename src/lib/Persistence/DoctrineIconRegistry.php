<?php

declare(strict_types=1);

namespace Masilia\IbexaIcons\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Masilia\Contracts\IbexaIcons\IconEntry;
use Masilia\Contracts\IbexaIcons\IconKind;
use Masilia\Contracts\IbexaIcons\IconRegistry;
use Masilia\Contracts\IbexaIcons\IconSource;
use Masilia\Contracts\IbexaIcons\InvalidIcon;
use Masilia\IbexaIcons\Icon\Sprite;
use Masilia\IbexaIcons\Persistence\Entity\IconRecord;
use Psr\Cache\CacheItemPoolInterface;

/** The registry in the database. All the icons are read once and kept in the cache until one is written. */
final class DoctrineIconRegistry implements IconRegistry
{
    private const CACHE_KEY = 'masilia_icons_registry';

    /** @var array<string, IconEntry>|null */
    private ?array $entries = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
        private readonly Sprite $sprite,
    ) {
    }

    public function find(IconKind $kind, string $identifier): ?IconEntry
    {
        return $this->entries()[self::key($kind, $identifier)] ?? null;
    }

    public function all(): array
    {
        return array_values($this->entries());
    }

    public function set(IconKind $kind, string $identifier, string $icon, IconSource $source): bool
    {
        if (!$this->sprite->has($icon)) {
            throw InvalidIcon::unknown($icon, $this->sprite->closest($icon));
        }
        $existing = $this->find($kind, $identifier);
        if ($existing !== null && !$source->canReplace($existing->source)) {
            return false;
        }
        $now = new \DateTimeImmutable();
        $record = $this->entityManager->find(IconRecord::class, ['kind' => $kind->value, 'identifier' => $identifier]);
        if ($record === null) {
            $this->entityManager->persist(new IconRecord($kind->value, $identifier, $icon, $source->value, $now));
        } else {
            $record->icon = $icon;
            $record->source = $source->value;
            $record->updatedAt = $now;
        }
        $this->entityManager->flush();
        $this->forget();

        return true;
    }

    public function remove(IconKind $kind, string $identifier): void
    {
        $record = $this->entityManager->find(IconRecord::class, ['kind' => $kind->value, 'identifier' => $identifier]);
        if ($record !== null) {
            $this->entityManager->remove($record);
            $this->entityManager->flush();
        }
        $this->forget();
    }

    private static function key(IconKind $kind, string $identifier): string
    {
        return $kind->value . ':' . $identifier;
    }

    private function forget(): void
    {
        $this->entries = null;
        $this->cache->deleteItem(self::CACHE_KEY);
    }

    /** @return array<string, IconEntry> */
    private function entries(): array
    {
        if ($this->entries !== null) {
            return $this->entries;
        }
        $item = $this->cache->getItem(self::CACHE_KEY);
        if (!$item->isHit()) {
            $rows = [];
            foreach ($this->entityManager->getRepository(IconRecord::class)->findAll() as $record) {
                $rows[] = [$record->kind, $record->identifier, $record->icon, $record->source, $record->updatedAt->format(\DATE_ATOM)];
            }
            $this->cache->save($item->set($rows));
        }
        $entries = [];
        foreach ($item->get() as [$kind, $identifier, $icon, $source, $updatedAt]) {
            $entry = new IconEntry(IconKind::from($kind), $identifier, $icon, IconSource::from($source), new \DateTimeImmutable($updatedAt));
            $entries[self::key($entry->kind, $entry->identifier)] = $entry;
        }

        return $this->entries = $entries;
    }
}
